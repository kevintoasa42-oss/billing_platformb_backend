<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierProfileRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Domain\Services\SettlementPolicy;
use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;
use DomainException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * PostgreSQL adapter for the canonical ThirdParty + Carrier experience.
 *
 * The carrier aggregate is addressed by its ThirdParty UUID. Legacy numeric
 * identifiers are deliberately absent from this port. SQL mirrors the
 * reference project so the frontend response shape is preserved exactly.
 */
final class CarrierProfileRepository implements CarrierProfileRepositoryInterface
{
    /** @return list<array<string,mixed>> */
    public function list(string $tenantId, array $filters = []): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $filters): array {
            $status = strtolower(trim((string) ($filters['status'] ?? 'active')));
            $status = in_array($status, ['active', 'inactive', 'all'], true) ? $status : 'active';
            $where = match ($status) {
                'inactive' => 'c.is_active = false',
                'all' => 'TRUE',
                default => 'c.is_active = true',
            };
            $values = [$tenantId, $tenantId];
            $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
            if ($search !== '') {
                $where .= " AND (t.name ILIKE ? OR t.identification ILIKE ? OR COALESCE(c.trade_name, '') ILIKE ? OR EXISTS (SELECT 1 FROM core.carrier_affiliations f JOIN core.carrier_vehicle_assignments a ON a.tenant_id=f.tenant_id AND a.affiliation_id=f.id JOIN core.vehicles v ON v.tenant_id=a.tenant_id AND v.id=a.vehicle_id WHERE f.tenant_id=c.tenant_id AND f.third_party_id=c.third_party_id AND upper(regexp_replace(trim(v.plate),'\\s+','','g')) ILIKE ?))";
                $term = '%'.$search.'%';
                $plateTerm = '%'.CanonicalIdentification::normalize($search).'%';
                $values = [...$values, $term, $term, $term, $plateTerm];
            }
            $limit = max(1, min(100, (int) ($filters['per_page'] ?? 100)));
            $rows = $this->all(<<<SQL
                SELECT
                    c.id AS carrier_company_id,
                    c.tenant_id,
                    t.id AS third_party_id,
                    t.name,
                    t.identification,
                    COALESCE(t.identification_type, CASE WHEN length(t.identification)=13 THEN '04' ELSE '05' END) AS identification_type,
                    t.person_type,
                    t.address,
                    t.phone,
                    t.email,
                    c.legal_name,
                    c.trade_name,
                    c.is_active,
                    COALESCE((SELECT s.status FROM core.carrier_signatures s WHERE s.tenant_id=c.tenant_id AND s.carrier_company_id=c.id), 'missing') AS signature_status,
                    COALESCE((SELECT p.verification_status FROM core.carrier_payment_accounts p WHERE p.tenant_id=c.tenant_id AND p.carrier_company_id=c.id AND p.is_primary AND p.is_active), 'missing') AS payment_account_status,
                    EXISTS(SELECT 1 FROM core.transport_operations op JOIN fiscal.documents original ON original.tenant_id=op.tenant_id AND original.operation_id=op.id AND original.document_type='invoice' JOIN fiscal.credit_notes cn ON cn.tenant_id=original.tenant_id AND cn.original_document_id=original.id JOIN fiscal.documents note ON note.tenant_id=cn.tenant_id AND note.id=cn.id WHERE op.tenant_id=c.tenant_id AND op.carrier_company_id=c.id AND cn.settlement_review_status='pending' AND note.fiscal_status IN ('simulated','imported','authorized')) AS review_pending,
                    COALESCE((SELECT SUM(d.total) FROM core.transport_operations o JOIN fiscal.documents d ON d.tenant_id=o.tenant_id AND d.operation_id=o.id WHERE o.tenant_id=c.tenant_id AND o.carrier_company_id=c.id AND d.document_type='invoice' AND d.fiscal_status IN ('simulated','imported','authorized')),0)::text AS customer_gross,
                    COALESCE((SELECT SUM(d.total) FROM core.transport_operations o JOIN fiscal.documents original ON original.tenant_id=o.tenant_id AND original.operation_id=o.id AND original.document_type='invoice' JOIN fiscal.credit_notes cn ON cn.tenant_id=original.tenant_id AND cn.original_document_id=original.id JOIN fiscal.documents d ON d.tenant_id=cn.tenant_id AND d.id=cn.id WHERE o.tenant_id=c.tenant_id AND o.carrier_company_id=c.id AND cn.settlement_impact IN ('reduce_customer_operation','requires_regularization') AND d.fiscal_status IN ('simulated','imported','authorized')),0)::text AS customer_credits,
                    COALESCE((SELECT SUM(r.total) FROM fiscal.received_documents r WHERE r.tenant_id=c.tenant_id AND r.issuer_id=c.third_party_id AND r.document_type='invoice' AND r.affects_transport AND r.fiscal_status IN ('received','imported','authorized')),0)::text AS partner_invoices,
                    COALESCE((SELECT SUM(r.total) FROM fiscal.received_documents r WHERE r.tenant_id=c.tenant_id AND r.issuer_id=c.third_party_id AND r.document_type='credit_note' AND r.affects_transport AND r.fiscal_status IN ('received','imported','authorized')),0)::text AS partner_credits,
                    COALESCE((SELECT SUM(a.amount) FROM fiscal.settlement_allocations a WHERE a.tenant_id=c.tenant_id AND a.carrier_company_id=c.id AND a.status='active'),0)::text AS allocated
                FROM core.carrier_companies c
                JOIN core.third_parties t ON t.tenant_id=c.tenant_id AND t.id=c.third_party_id
                WHERE c.tenant_id=? AND t.tenant_id=? AND {$where}
                ORDER BY COALESCE(c.trade_name,c.legal_name,t.name), c.id
                LIMIT ?
                SQL, [...$values, $limit]);

            return array_map(fn (array $row): array => $this->mapSummary($row), $rows);
        });
    }

    /** @return array<string,mixed>|null */
    public function findByThirdPartyId(string $tenantId, string $thirdPartyId): ?array
    {
        $thirdPartyId = $this->requireUuid($thirdPartyId);

        return $this->withTenant($tenantId, function () use ($tenantId, $thirdPartyId): ?array {
            $carrier = $this->carrier($tenantId, $thirdPartyId);

            return $carrier === null ? null : $this->detail($carrier);
        });
    }

    /** @return array<string,mixed> */
    public function onboard(string $tenantId, array $input, string $idempotencyKey, ?string $actorId = null): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $input, $idempotencyKey, $actorId): array {
            $hash = hash('sha256', $this->canonicalJson($input));
            $existingRequest = $this->first('SELECT request_hash,response::text AS response FROM core.carrier_onboarding_requests WHERE tenant_id=? AND idempotency_key=? FOR UPDATE', [$tenantId, $idempotencyKey]);
            if ($existingRequest !== null) {
                if ((string) $existingRequest['request_hash'] !== $hash) {
                    throw new DomainException('La Idempotency-Key ya fue usada con otro contenido.');
                }
                $response = json_decode((string) $existingRequest['response'], true);
                if (! is_array($response)) {
                    throw new RuntimeException('La respuesta idempotente almacenada no es válida.');
                }

                return $response;
            }

            $identity = CanonicalIdentification::from(
                (string) ($input['identification_type'] ?? ''),
                (string) ($input['identification'] ?? ''),
            );
            if ($identity === null) {
                throw new DomainException('La identificación fiscal es obligatoria.');
            }
            // Serialize onboarding attempts for the same tenant/type/value.
            // The database uniqueness index remains the final guard, while
            // this transaction lock lets concurrent idempotency keys reuse
            // the same identity instead of racing into a conflict.
            $this->statement('SELECT pg_advisory_xact_lock(hashtextextended(?, 0))', [$tenantId.'|'.$identity->type.'|'.$identity->value]);
            $name = trim((string) ($input['name'] ?? $input['legal_name'] ?? ''));
            $legalName = trim((string) ($input['legal_name'] ?? $name));
            if ($name === '' || $legalName === '') {
                throw new DomainException('El nombre y la razón social son obligatorios.');
            }
            $personType = $this->normalizePersonType($input['person_type'] ?? null);
            if ($identity->type === '05' && $personType === 'juridical') {
                throw new DomainException('Una cédula sólo puede asociarse a una persona natural.');
            }
            if ($identity->type === '05') {
                $personType = 'natural';
            }

            $thirdParty = $this->first('SELECT * FROM core.third_parties WHERE tenant_id=? AND identification_type=? AND upper(regexp_replace(trim(identification),\'\\s+\',\'\',\'g\'))=? FOR UPDATE', [$tenantId, $identity->type, $identity->value]);
            $reusedThirdParty = $thirdParty !== null;
            if ($thirdParty === null) {
                $thirdParty = $this->first('INSERT INTO core.third_parties(tenant_id,name,identification,must_invoice,identification_type,person_type,address,phone,email,is_active) VALUES (?,?,?,?,?,?,?,?,?,true) RETURNING *', [$tenantId, $name, $identity->value, 'false', $identity->type, $personType, $input['address'] ?? null, $input['phone'] ?? null, $input['email'] ?? null]);
            } else {
                $existingPersonType = trim((string) ($thirdParty['person_type'] ?? ''));
                if ($personType !== null && $existingPersonType !== '' && $existingPersonType !== $personType) {
                    throw new DomainException('El tipo de persona no coincide con la identidad transportista existente.');
                }
                $this->statement('UPDATE core.third_parties SET name=COALESCE(NULLIF(name,\'\'),?),identification_type=COALESCE(identification_type,?),person_type=COALESCE(?,person_type),address=COALESCE(address,?),phone=COALESCE(phone,?),email=COALESCE(email,?) WHERE tenant_id=? AND id=?', [$name, $identity->type, $personType, $input['address'] ?? null, $input['phone'] ?? null, $input['email'] ?? null, $tenantId, $thirdParty['id']]);
                $thirdParty = $this->first('SELECT * FROM core.third_parties WHERE tenant_id=? AND id=? FOR UPDATE', [$tenantId, $thirdParty['id']]);
            }
            if ($thirdParty === null) {
                throw new RuntimeException('No se pudo crear o recuperar la identidad ThirdParty.');
            }

            $this->statement("INSERT INTO core.third_party_roles(tenant_id,third_party_id,role) VALUES (?,?,'carrier') ON CONFLICT DO NOTHING", [$tenantId, $thirdParty['id']]);
            $carrier = $this->first('INSERT INTO core.carrier_companies(tenant_id,third_party_id,legal_name,trade_name,is_active) VALUES (?,?,?,?,true) ON CONFLICT(tenant_id,third_party_id) DO UPDATE SET legal_name=EXCLUDED.legal_name,trade_name=EXCLUDED.trade_name,is_active=true,updated_at=now() RETURNING *', [$tenantId, $thirdParty['id'], $legalName, trim((string) ($input['trade_name'] ?? $legalName))]);
            if ($carrier === null) {
                throw new RuntimeException('No se pudo crear la ficha Carrier.');
            }

            $activityId = trim((string) ($input['activity_id'] ?? ''));
            if ($activityId !== '') {
                $this->ensureActivity($activityId);
                $from = $this->dateValue($input['activity_valid_from'] ?? null);
                $establishmentCode = $this->sriCode($input['establishment_code'] ?? '001', 'El establecimiento SRI');
                $this->statement('INSERT INTO core.carrier_activities(tenant_id,carrier_company_id,activity_id,validity,is_primary) SELECT ?,?,?,daterange(?::date,NULL,\'[)\'),true WHERE NOT EXISTS (SELECT 1 FROM core.carrier_activities WHERE tenant_id=? AND carrier_company_id=? AND activity_id=? AND validity @> CURRENT_DATE)', [$tenantId, $carrier['id'], $activityId, $from, $tenantId, $carrier['id'], $activityId]);
                $this->statement('INSERT INTO core.third_party_activities(tenant_id,third_party_id,establishment_code,activity_id,validity) VALUES (?,?,?,?,daterange(?::date,NULL,\'[)\')) ON CONFLICT DO NOTHING', [$tenantId, $thirdParty['id'], $establishmentCode, $activityId, $from]);
            }

            $defaultIssuer = $this->first('SELECT mode FROM core.carrier_issuer_settings WHERE tenant_id=?', [$tenantId]);
            $mode = strtolower(trim((string) ($input['issuer_mode'] ?? $defaultIssuer['mode'] ?? 'operator')));
            $issuer = $this->configureIssuer($tenantId, (string) $carrier['id'], $mode, $input);
            if (array_key_exists('payment_account', $input) && is_array($input['payment_account']) && $input['payment_account'] !== []) {
                $this->upsertPayment($tenantId, $carrier, $input['payment_account'], $actorId);
            }
            $this->statement('INSERT INTO core.carrier_signatures(tenant_id,carrier_company_id,status) VALUES (?,? ,\'missing\') ON CONFLICT(tenant_id,carrier_company_id) DO NOTHING', [$tenantId, $carrier['id']]);
            $plates = array_key_exists('plates', $input) || array_key_exists('plate', $input)
                ? $this->syncPlates($tenantId, (string) $thirdParty['id'], $this->requestedPlates($input))
                : $this->plates($tenantId, (string) $thirdParty['id']);
            $this->auditEvent($tenantId, $actorId, 'third_party.carrier_onboarded', 'carrier_company', (string) $carrier['id'], null, [], ['third_party_id' => (string) $thirdParty['id'], 'person_type' => $thirdParty['person_type'] ?? $personType, 'issuer_mode' => $mode, 'plates' => array_column($plates, 'plate')]);

            $result = $this->detail($this->carrier($tenantId, (string) $thirdParty['id']) ?? throw new RuntimeException('No se pudo leer la ficha Carrier creada.'));
            $result['onboarding'] = ['reused_third_party' => $reusedThirdParty, 'issuer' => $issuer];
            $this->statement('INSERT INTO core.carrier_onboarding_requests(tenant_id,idempotency_key,request_hash,third_party_id,response) VALUES (?,?,?,?,?::jsonb)', [$tenantId, $idempotencyKey, $hash, $thirdParty['id'], json_encode($result, JSON_THROW_ON_ERROR)]);

            return $result;
        });
    }

    /** @return array<string,mixed> */
    public function update(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $thirdPartyId, $input, $actorId): array {
            $thirdPartyId = $this->requireUuid($thirdPartyId);
            $carrier = $this->carrier($tenantId, $thirdPartyId, true);
            if ($carrier === null) {
                throw new DomainException('No se encontró el socio transportista.');
            }
            $before = ['name' => $carrier['name'], 'legal_name' => $carrier['legal_name'], 'trade_name' => $carrier['trade_name'], 'is_active' => $this->bool($carrier['is_active'])];
            $name = trim((string) ($input['name'] ?? $carrier['name']));
            $legalName = trim((string) ($input['legal_name'] ?? $carrier['legal_name']));
            if ($name === '' || $legalName === '') {
                throw new DomainException('El nombre y la razón social son obligatorios.');
            }
            $active = array_key_exists('is_active', $input) ? (bool) $input['is_active'] : $this->bool($carrier['is_active']);
            $this->statement('UPDATE core.third_parties SET name=?,address=?,phone=?,email=?,is_active=?::boolean WHERE tenant_id=? AND id=?', [$name, array_key_exists('address', $input) ? $input['address'] : $carrier['address'], array_key_exists('phone', $input) ? $input['phone'] : $carrier['phone'], array_key_exists('email', $input) ? $input['email'] : $carrier['email'], $active ? 'true' : 'false', $tenantId, $thirdPartyId]);
            $this->statement('UPDATE core.carrier_companies SET legal_name=?,trade_name=?,is_active=?::boolean,updated_at=now() WHERE tenant_id=? AND id=?', [$legalName, trim((string) ($input['trade_name'] ?? $carrier['trade_name'] ?? $legalName)), $active ? 'true' : 'false', $tenantId, $carrier['carrier_company_id']]);
            if (array_key_exists('activity_id', $input) && trim((string) $input['activity_id']) !== '') {
                $activityId = trim((string) $input['activity_id']);
                $this->ensureActivity($activityId);
                $from = $this->dateValue($input['activity_valid_from'] ?? null);
                $establishmentCode = $this->sriCode($input['establishment_code'] ?? '001', 'El establecimiento SRI');
                $this->statement('INSERT INTO core.carrier_activities(tenant_id,carrier_company_id,activity_id,validity,is_primary) SELECT ?,?,?,daterange(?::date,NULL,\'[)\'),true WHERE NOT EXISTS (SELECT 1 FROM core.carrier_activities WHERE tenant_id=? AND carrier_company_id=? AND activity_id=? AND validity @> CURRENT_DATE)', [$tenantId, $carrier['carrier_company_id'], $activityId, $from, $tenantId, $carrier['carrier_company_id'], $activityId]);
                $this->statement('INSERT INTO core.third_party_activities(tenant_id,third_party_id,establishment_code,activity_id,validity) VALUES (?,?,?,?,daterange(?::date,NULL,\'[)\')) ON CONFLICT DO NOTHING', [$tenantId, $thirdPartyId, $establishmentCode, $activityId, $from]);
            }
            if (array_key_exists('plates', $input) || array_key_exists('plate', $input) || ! $active) {
                $this->syncPlates($tenantId, $thirdPartyId, $active ? $this->requestedPlates($input) : []);
            }
            $updated = $this->carrier($tenantId, $thirdPartyId);
            if ($updated === null) {
                throw new RuntimeException('No se pudo leer la ficha Carrier actualizada.');
            }
            $this->auditEvent($tenantId, $actorId, 'third_party.carrier_updated', 'carrier_company', (string) $carrier['carrier_company_id'], null, $before, ['name' => $updated['name'], 'legal_name' => $updated['legal_name'], 'trade_name' => $updated['trade_name'], 'is_active' => $this->bool($updated['is_active'])]);

            return $this->detail($updated);
        });
    }

    /** @return array<string,mixed> */
    public function updatePaymentAccount(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $thirdPartyId, $input, $actorId): array {
            $carrier = $this->carrier($tenantId, $this->requireUuid($thirdPartyId));
            if ($carrier === null) {
                throw new DomainException('No se encontró el socio transportista.');
            }
            $before = $this->payment($tenantId, (string) $carrier['carrier_company_id']);
            $after = $this->upsertPayment($tenantId, $carrier, $input, $actorId);
            $this->auditEvent($tenantId, $actorId, 'third_party.payment_account_updated', 'carrier_payment_account', (string) ($after['id'] ?? ''), null, $this->safePayment($before), $this->safePayment($after));

            return $this->detail($carrier);
        });
    }

    /** @return array<string,mixed> */
    public function updateSignature(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $thirdPartyId, $input, $actorId): array {
            $carrier = $this->carrier($tenantId, $this->requireUuid($thirdPartyId));
            if ($carrier === null) {
                throw new DomainException('No se encontró el socio transportista.');
            }
            foreach (['password', 'private_key', 'certificate', 'p12', 'pfx', 'contents', 'secret'] as $forbidden) {
                if (array_key_exists($forbidden, $input)) {
                    throw new DomainException('La clave privada y el contenido del certificado se gestionan en la custodia segura.');
                }
            }
            $status = strtolower(trim((string) ($input['status'] ?? 'missing')));
            if (! in_array($status, ['missing', 'active', 'expired', 'revoked', 'invalid'], true)) {
                throw new DomainException('Estado de firma inválido.');
            }
            $before = $this->signature($tenantId, (string) $carrier['carrier_company_id']);
            $this->statement('INSERT INTO core.carrier_signatures(tenant_id,carrier_company_id,status,certificate_fingerprint,certificate_serial,certificate_subject,valid_from,valid_until,key_reference,key_version,updated_by,replaced_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,now()) ON CONFLICT(tenant_id,carrier_company_id) DO UPDATE SET status=EXCLUDED.status,certificate_fingerprint=EXCLUDED.certificate_fingerprint,certificate_serial=EXCLUDED.certificate_serial,certificate_subject=EXCLUDED.certificate_subject,valid_from=EXCLUDED.valid_from,valid_until=EXCLUDED.valid_until,key_reference=EXCLUDED.key_reference,key_version=EXCLUDED.key_version,updated_by=EXCLUDED.updated_by,replaced_at=now(),updated_at=now()', [$tenantId, $carrier['carrier_company_id'], $status, $input['certificate_fingerprint'] ?? null, $input['certificate_serial'] ?? null, $input['certificate_subject'] ?? null, $input['valid_from'] ?? null, $input['valid_until'] ?? null, $input['key_reference'] ?? null, $input['key_version'] ?? null, $this->uuidOrNull($actorId)]);
            $after = $this->signature($tenantId, (string) $carrier['carrier_company_id']);
            $this->auditEvent($tenantId, $actorId, 'third_party.signature_updated', 'carrier_signature', (string) $carrier['carrier_company_id'], null, $this->safeSignature($before), $this->safeSignature($after));

            return $this->detail($carrier);
        });
    }

    /** @return array<string,mixed> */
    public function recordDocument(string $tenantId, string $thirdPartyId, array $input, ?string $actorId = null): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $thirdPartyId, $input, $actorId): array {
            $carrier = $this->carrier($tenantId, $this->requireUuid($thirdPartyId));
            if ($carrier === null) {
                throw new DomainException('No se encontró el socio transportista.');
            }
            $type = strtolower(trim((string) ($input['document_type'] ?? 'invoice')));
            if (! in_array($type, ['invoice', 'credit_note', 'debit_note', 'other'], true)) {
                throw new DomainException('Tipo de documento del socio inválido.');
            }
            $reference = trim((string) ($input['reference'] ?? ''));
            $total = $this->money((string) ($input['total'] ?? ''));
            if ($reference === '' || strlen($reference) > 120 || $this->compareMoney($total, '0.00') <= 0) {
                throw new DomainException('Referencia e importe son obligatorios.');
            }
            $xmlBase64 = preg_replace('/\s+/', '', (string) ($input['xml_base64'] ?? ''));
            $xml = base64_decode((string) $xmlBase64, true);
            if (! is_string($xml) || $xml === '' || strlen($xml) > 10_000_000) {
                throw new DomainException('El XML original no es válido o supera el límite permitido.');
            }
            $duplicate = $this->first('SELECT * FROM fiscal.received_documents WHERE tenant_id=? AND issuer_id=? AND reference=?', [$tenantId, $carrier['third_party_id'], $reference]);
            if ($duplicate !== null) {
                return ['document' => $this->mapReceivedDocument($duplicate), 'duplicate' => true];
            }
            $issueDate = $this->dateValue($input['issue_date'] ?? null);
            $operation = null;
            if (($input['operation_id'] ?? null) !== null && (string) $input['operation_id'] !== '') {
                $operation = $this->first('SELECT * FROM core.transport_operations WHERE tenant_id=? AND id=? FOR UPDATE', [$tenantId, $this->requireUuid((string) $input['operation_id'])]);
                if ($operation === null || ($operation['carrier_company_id'] !== null && (string) $operation['carrier_company_id'] !== (string) $carrier['carrier_company_id'])) {
                    throw new DomainException('La operación no pertenece al socio transportista.');
                }
            }
            if ($operation === null) {
                $issuerSnapshot = $this->issuerSnapshot($tenantId, (string) $carrier['carrier_company_id']);
                $operation = $this->first('INSERT INTO core.transport_operations(tenant_id,carrier_company_id,service_date,status,reference,issuer_snapshot) VALUES (?,?,?,\'open\',?,?::jsonb) RETURNING *', [$tenantId, $carrier['carrier_company_id'], $issueDate, 'received:'.$reference, json_encode($issuerSnapshot, JSON_THROW_ON_ERROR)]);
            }
            if ($operation === null) {
                throw new RuntimeException('No se pudo crear la operación del documento recibido.');
            }
            $originalId = null;
            if (in_array($type, ['credit_note', 'debit_note'], true)) {
                $originalId = $this->requireUuid((string) ($input['original_document_id'] ?? ''));
                $original = $this->first('SELECT * FROM fiscal.received_documents WHERE tenant_id=? AND id=? AND issuer_id=? AND document_type=\'invoice\' FOR UPDATE', [$tenantId, $originalId, $carrier['third_party_id']]);
                if ($original === null) {
                    throw new DomainException('La nota debe referenciar una factura recibida del mismo socio.');
                }
                if ($type === 'credit_note') {
                    $remaining = (string) ($this->first("SELECT GREATEST(r.total-COALESCE((SELECT SUM(n.total) FROM fiscal.received_documents n WHERE n.tenant_id=r.tenant_id AND n.original_received_document_id=r.id AND n.document_type='credit_note' AND n.affects_transport AND n.fiscal_status IN ('received','imported','authorized')),0),0)::text AS remaining FROM fiscal.received_documents r WHERE r.tenant_id=? AND r.id=?", [$tenantId, $originalId])['remaining'] ?? '0.00');
                    if ($this->compareMoney($total, $remaining) > 0) {
                        throw new DomainException('La nota de crédito supera el saldo de la factura original.');
                    }
                }
                if (strlen(trim((string) ($input['reason'] ?? ''))) < 8) {
                    throw new DomainException('La nota requiere un motivo de al menos 8 caracteres.');
                }
            }
            $status = strtolower(trim((string) ($input['fiscal_status'] ?? 'received')));
            if (! in_array($status, ['received', 'authorized', 'imported', 'draft'], true)) {
                throw new DomainException('Estado fiscal del documento recibido inválido.');
            }
            $affects = array_key_exists('affects_transport', $input) ? (bool) $input['affects_transport'] : $type !== 'other';
            $issuer = ['name' => $carrier['name'], 'identification' => $carrier['identification'], 'identification_type' => $carrier['identification_type']];
            $company = $this->first('SELECT name,ruc,legal_name FROM core.companies WHERE tenant_id=? LIMIT 1', [$tenantId]) ?? ['name' => 'Empresa', 'ruc' => null];
            $recipient = ['name' => $company['legal_name'] ?? $company['name'], 'identification' => $company['ruc']];
            $document = $this->first('INSERT INTO fiscal.received_documents(tenant_id,issuer_id,issuer_snapshot,recipient_snapshot,reference,operation_id,total,original_xml,xml_sha256,document_type,fiscal_status,access_key,authorization_number,issued_at,original_received_document_id,reason,affects_transport) VALUES (?,?,?::jsonb,?::jsonb,?,?,?,decode(?,\'base64\'),?,?,?,?,?,?::timestamptz,?,?,?::boolean) RETURNING *', [$tenantId, $carrier['third_party_id'], json_encode($issuer, JSON_THROW_ON_ERROR), json_encode($recipient, JSON_THROW_ON_ERROR), $reference, $operation['id'], $total, $xmlBase64, hash('sha256', $xml), $type, $status, $input['access_key'] ?? null, $input['authorization_number'] ?? null, $issueDate.' 12:00:00+00', $originalId, $input['reason'] ?? null, $affects ? 'true' : 'false']);
            if ($document === null) {
                throw new RuntimeException('No se pudo registrar el documento recibido.');
            }
            $this->auditEvent($tenantId, $actorId, 'third_party.document_received', 'received_document', (string) $document['id'], (string) $operation['id'], [], ['document_type' => $type, 'total' => $total, 'fiscal_status' => $status, 'xml_sha256' => hash('sha256', $xml)]);

            return ['document' => $this->mapReceivedDocument($document), 'duplicate' => false];
        });
    }

    /** @return array<string,mixed> */
    public function cancelDocument(string $tenantId, string $thirdPartyId, string $documentId, string $reason, ?string $actorId = null): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $thirdPartyId, $documentId, $reason, $actorId): array {
            $carrier = $this->carrier($tenantId, $this->requireUuid($thirdPartyId));
            $reason = trim($reason);
            if ($carrier === null || strlen($reason) < 8 || strlen($reason) > 500) {
                throw new DomainException('El socio o el motivo de cancelación no son válidos.');
            }
            $documentId = $this->requireUuid($documentId);
            $document = $this->first('SELECT * FROM fiscal.received_documents WHERE tenant_id=? AND id=? AND issuer_id=? FOR UPDATE', [$tenantId, $documentId, $carrier['third_party_id']]);
            if ($document === null) {
                throw new DomainException('No se encontró el documento recibido.');
            }
            if ((string) $document['fiscal_status'] === 'cancelled') {
                return ['document' => $this->mapReceivedDocument($document), 'cancelled' => true, 'idempotent' => true];
            }
            $activeAllocation = $this->first("SELECT id FROM fiscal.settlement_allocations WHERE tenant_id=? AND received_document_id=? AND status='active' FOR UPDATE", [$tenantId, $documentId]);
            if ($activeAllocation !== null) {
                throw new DomainException('El documento tiene conciliaciones activas; reviértelas primero.');
            }
            $this->statement("UPDATE fiscal.received_documents SET fiscal_status='cancelled',affects_transport=false WHERE tenant_id=? AND id=?", [$tenantId, $documentId]);
            $updated = $this->first('SELECT * FROM fiscal.received_documents WHERE tenant_id=? AND id=?', [$tenantId, $documentId]) ?? $document;
            $this->auditEvent($tenantId, $actorId, 'third_party.document_cancelled', 'received_document', $documentId, (string) $document['operation_id'], ['fiscal_status' => $document['fiscal_status']], ['fiscal_status' => 'cancelled', 'reason' => $reason]);

            return ['document' => $this->mapReceivedDocument($updated), 'cancelled' => true, 'idempotent' => false, 'reason' => $reason];
        });
    }

    /** @return array<string,mixed> */
    public function allocateDocument(string $tenantId, string $thirdPartyId, array $input, string $idempotencyKey, ?string $actorId = null): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $thirdPartyId, $input, $idempotencyKey, $actorId): array {
            $carrier = $this->carrier($tenantId, $this->requireUuid($thirdPartyId));
            if ($carrier === null || ! preg_match('/^[A-Za-z0-9_-]{8,100}$/D', $idempotencyKey)) {
                throw new DomainException('El socio o la clave de idempotencia no son válidos.');
            }
            $existing = $this->first('SELECT * FROM fiscal.settlement_allocations WHERE tenant_id=? AND idempotency_key=? FOR UPDATE', [$tenantId, $idempotencyKey]);
            if ($existing !== null) {
                if ((string) $existing['carrier_company_id'] !== (string) $carrier['carrier_company_id']) {
                    throw new DomainException('La clave de idempotencia ya pertenece a otro socio.');
                }

                return ['allocation' => $this->mapAllocation($existing), 'metrics' => $this->operationMetrics((string) $existing['operation_id'])];
            }
            $receivedId = $this->requireUuid((string) ($input['received_document_id'] ?? ''));
            $operationId = $this->requireUuid((string) ($input['operation_id'] ?? ''));
            $received = $this->first("SELECT * FROM fiscal.received_documents WHERE tenant_id=? AND id=? AND issuer_id=? AND document_type='invoice' AND affects_transport AND fiscal_status IN ('received','imported','authorized') FOR UPDATE", [$tenantId, $receivedId, $carrier['third_party_id']]);
            $operation = $this->first('SELECT * FROM core.transport_operations WHERE tenant_id=? AND id=? FOR UPDATE', [$tenantId, $operationId]);
            if ($received === null || $operation === null || ($operation['carrier_company_id'] !== null && (string) $operation['carrier_company_id'] !== (string) $carrier['carrier_company_id']) || (string) $received['operation_id'] !== (string) $operation['id']) {
                throw new DomainException('El documento recibido o la operación no pertenecen al socio.');
            }
            $amount = $this->money((string) ($input['amount'] ?? ''));
            $remaining = (string) ($this->first("SELECT GREATEST(r.total-COALESCE((SELECT SUM(a.amount) FROM fiscal.settlement_allocations a WHERE a.tenant_id=r.tenant_id AND a.received_document_id=r.id AND a.status='active'),0),0)::text AS remaining FROM fiscal.received_documents r WHERE r.tenant_id=? AND r.id=?", [$tenantId, $receivedId])['remaining'] ?? '0.00');
            if ($this->compareMoney($amount, $remaining) > 0) {
                throw new DomainException('La asignación supera el saldo disponible del documento.');
            }
            $metrics = $this->operationMetrics($operationId);
            if ($metrics['blocked'] || $this->compareMoney($amount, $metrics['pending']) > 0) {
                throw new DomainException('La operación está bloqueada o el importe supera el saldo pendiente.');
            }
            $customerDocument = $this->first("SELECT * FROM fiscal.documents WHERE tenant_id=? AND operation_id=? AND document_type='invoice' AND fiscal_status IN ('simulated','imported','authorized') ORDER BY issued_at DESC,id DESC LIMIT 1", [$tenantId, $operationId]);
            if ($customerDocument === null) {
                throw new DomainException('La operación no tiene una factura de cliente relacionada.');
            }
            $allocation = $this->first('INSERT INTO fiscal.settlement_allocations(tenant_id,operation_id,customer_document_id,received_document_id,carrier_company_id,amount,idempotency_key,created_by) VALUES (?,?,?,?,?,?,?,?) RETURNING *', [$tenantId, $operationId, $customerDocument['id'], $receivedId, $carrier['carrier_company_id'], $amount, $idempotencyKey, $this->uuidOrNull($actorId)]);
            if ($allocation === null) {
                throw new RuntimeException('No se pudo crear la conciliación.');
            }
            $after = $this->operationMetrics($operationId);
            $this->statement('UPDATE core.transport_operations SET carrier_company_id=?,status=?,updated_at=now() WHERE tenant_id=? AND id=?', [$carrier['carrier_company_id'], $after['status'], $tenantId, $operationId]);
            $this->auditEvent($tenantId, $actorId, 'third_party.settlement_allocated', 'settlement_allocation', (string) $allocation['id'], $operationId, [], ['amount' => $amount, 'status' => $after['status']]);

            return ['allocation' => $this->mapAllocation($allocation), 'metrics' => $after];
        });
    }

    /** @return array<string,mixed> */
    public function reverseAllocation(string $tenantId, string $thirdPartyId, string $allocationId, string $reason, ?string $actorId = null): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $thirdPartyId, $allocationId, $reason, $actorId): array {
            $carrier = $this->carrier($tenantId, $this->requireUuid($thirdPartyId));
            $reason = trim($reason);
            if ($carrier === null || strlen($reason) < 8 || strlen($reason) > 500) {
                throw new DomainException('El socio o el motivo de reversa no son válidos.');
            }
            $allocationId = $this->requireUuid($allocationId);
            $allocation = $this->first('SELECT * FROM fiscal.settlement_allocations WHERE tenant_id=? AND id=? AND carrier_company_id=? FOR UPDATE', [$tenantId, $allocationId, $carrier['carrier_company_id']]);
            if ($allocation === null) {
                throw new DomainException('No se encontró la conciliación.');
            }
            if ((string) $allocation['status'] === 'reversed') {
                return ['allocation' => $this->mapAllocation($allocation), 'reversed' => true, 'idempotent' => true, 'reason' => $reason, 'metrics' => $this->operationMetrics((string) $allocation['operation_id'])];
            }
            $before = $this->operationMetrics((string) $allocation['operation_id']);
            $this->statement("UPDATE fiscal.settlement_allocations SET status='reversed',reversed_at=now() WHERE tenant_id=? AND id=?", [$tenantId, $allocationId]);
            $after = $this->operationMetrics((string) $allocation['operation_id']);
            $this->statement('UPDATE core.transport_operations SET status=?,updated_at=now() WHERE tenant_id=? AND id=?', [$after['status'], $tenantId, $allocation['operation_id']]);
            $updated = $this->first('SELECT * FROM fiscal.settlement_allocations WHERE tenant_id=? AND id=?', [$tenantId, $allocationId]) ?? $allocation;
            $this->auditEvent($tenantId, $actorId, 'third_party.settlement_reversed', 'settlement_allocation', $allocationId, (string) $allocation['operation_id'], ['status' => 'active', 'metrics' => $before], ['status' => 'reversed', 'metrics' => $after, 'reason' => $reason]);

            return ['allocation' => $this->mapAllocation($updated), 'reversed' => true, 'idempotent' => false, 'reason' => $reason, 'metrics' => $after];
        });
    }

    /** @return array<string,mixed> */
    public function clearSettlementReview(string $tenantId, string $thirdPartyId, string $operationId, string $reason, ?string $actorId = null): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $thirdPartyId, $operationId, $reason, $actorId): array {
            $carrier = $this->carrier($tenantId, $this->requireUuid($thirdPartyId));
            $operationId = $this->requireUuid($operationId);
            $reason = trim($reason);
            if ($carrier === null || strlen($reason) < 8 || strlen($reason) > 500) {
                throw new DomainException('El socio o el motivo de regularización no son válidos.');
            }
            $operation = $this->first('SELECT * FROM core.transport_operations WHERE tenant_id=? AND id=? AND carrier_company_id=? FOR UPDATE', [$tenantId, $operationId, $carrier['carrier_company_id']]);
            if ($operation === null) {
                throw new DomainException('La operación no pertenece al socio.');
            }
            $pending = $this->all("SELECT cn.id FROM fiscal.credit_notes cn JOIN fiscal.documents d ON d.tenant_id=cn.tenant_id AND d.id=cn.id JOIN fiscal.documents original ON original.tenant_id=cn.tenant_id AND original.id=cn.original_document_id WHERE cn.tenant_id=? AND original.operation_id=? AND cn.settlement_review_status='pending' AND d.fiscal_status IN ('simulated','imported','authorized') FOR UPDATE", [$tenantId, $operationId]);
            if ($pending === []) {
                throw new DomainException('La operación no tiene una nota de crédito pendiente.');
            }
            $this->statement("UPDATE fiscal.credit_notes SET settlement_review_status='cleared' WHERE tenant_id=? AND id IN (SELECT cn.id FROM fiscal.credit_notes cn JOIN fiscal.documents original ON original.tenant_id=cn.tenant_id AND original.id=cn.original_document_id WHERE cn.tenant_id=? AND original.operation_id=? AND cn.settlement_review_status='pending')", [$tenantId, $tenantId, $operationId]);
            $metrics = $this->operationMetrics($operationId);
            $this->statement('UPDATE core.transport_operations SET status=?,updated_at=now() WHERE tenant_id=? AND id=?', [$metrics['status'], $tenantId, $operationId]);
            $this->auditEvent($tenantId, $actorId, 'third_party.settlement_review_cleared', 'transport_operation', $operationId, $operationId, ['pending_credit_notes' => count($pending)], ['cleared_credit_notes' => count($pending), 'reason' => $reason, 'metrics' => $metrics]);

            return ['operation_id' => $operationId, 'cleared_credit_notes' => count($pending), 'metrics' => $metrics, 'reason' => $reason];
        });
    }

    /** @return list<array<string,mixed>> */
    public function auditTrail(string $tenantId, string $thirdPartyId): array
    {
        return $this->withTenant($tenantId, function () use ($tenantId, $thirdPartyId): array {
            $carrier = $this->carrier($tenantId, $this->requireUuid($thirdPartyId));
            if ($carrier === null) {
                return [];
            }
            $rows = $this->all('SELECT a.* FROM platform.audit_events a WHERE a.tenant_id=? AND (a.entity_id=? OR a.operation_id IN (SELECT id FROM core.transport_operations WHERE tenant_id=? AND carrier_company_id=?)) ORDER BY a.occurred_at DESC,a.id DESC LIMIT 200', [$tenantId, $carrier['carrier_company_id'], $tenantId, $carrier['carrier_company_id']]);

            return array_map(fn (array $row): array => $this->mapAudit($row), $rows);
        });
    }

    /** @return array<string,mixed>|null */
    private function carrier(string $tenantId, string $thirdPartyId, bool $forUpdate = false): ?array
    {
        $sql = 'SELECT c.id AS carrier_company_id,c.tenant_id,c.legal_name,c.trade_name,c.is_active,t.id AS third_party_id,t.name,t.identification,COALESCE(t.identification_type,CASE WHEN length(t.identification)=13 THEN \'04\' ELSE \'05\' END) AS identification_type,t.person_type,t.address,t.phone,t.email FROM core.carrier_companies c JOIN core.third_parties t ON t.tenant_id=c.tenant_id AND t.id=c.third_party_id JOIN core.third_party_roles r ON r.tenant_id=t.tenant_id AND r.third_party_id=t.id AND r.role=\'carrier\' WHERE c.tenant_id=? AND t.tenant_id=? AND t.id=? LIMIT 1';
        if ($forUpdate) {
            $sql = str_replace(' LIMIT 1', ' LIMIT 1 FOR UPDATE', $sql);
        }

        return $this->first($sql, [$tenantId, $tenantId, $thirdPartyId]);
    }

    /** @return array<string,mixed> */
    private function detail(array $carrier): array
    {
        $summary = $this->mapSummary($this->summaryRow((string) $carrier['tenant_id'], (string) $carrier['carrier_company_id']) ?? $carrier);
        $roles = array_values(array_unique(array_map(
            static fn (array $row): string => (string) $row['role'],
            $this->all('SELECT role FROM core.third_party_roles WHERE tenant_id=? AND third_party_id=? ORDER BY role', [$carrier['tenant_id'], $carrier['third_party_id']]),
        )));
        $activities = $this->all('SELECT a.activity_id AS code,e.name,a.validity,a.is_primary FROM core.carrier_activities a JOIN core.economic_activities e ON e.id=a.activity_id WHERE a.tenant_id=? AND a.carrier_company_id=? ORDER BY a.is_primary DESC,a.activity_id', [$carrier['tenant_id'], $carrier['carrier_company_id']]);
        $establishments = $this->all('SELECT id,sri_code,name,address,phone,email,city_id,is_active FROM core.carrier_establishments WHERE tenant_id=? AND carrier_company_id=? ORDER BY sri_code', [$carrier['tenant_id'], $carrier['carrier_company_id']]);
        foreach ($establishments as &$establishment) {
            $establishment['id'] = (string) $establishment['id'];
            $establishment['is_active'] = $this->bool($establishment['is_active']);
            $points = $this->all('SELECT id,sri_code,name,next_sequential,is_active FROM core.carrier_emission_points WHERE tenant_id=? AND establishment_id=? ORDER BY sri_code', [$carrier['tenant_id'], $establishment['id']]);
            $establishment['emission_points'] = array_map(fn (array $point): array => [...$point, 'id' => (string) $point['id'], 'next_sequential' => (int) $point['next_sequential'], 'is_active' => $this->bool($point['is_active'])], $points);
        }
        unset($establishment);
        $received = array_map(fn (array $row): array => $this->mapReceivedDocument($row), $this->all('SELECT * FROM fiscal.received_documents WHERE tenant_id=? AND issuer_id=? ORDER BY issued_at DESC,id DESC LIMIT 200', [$carrier['tenant_id'], $carrier['third_party_id']]));
        $customerDocuments = array_map(fn (array $row): array => $this->mapCustomerDocument($row), $this->all("SELECT d.id,d.document_type,d.issued_at,d.total,d.fiscal_status,o.id AS operation_id FROM fiscal.documents d JOIN core.transport_operations o ON o.tenant_id=d.tenant_id AND o.id=d.operation_id WHERE d.tenant_id=? AND o.carrier_company_id=? AND d.document_type='invoice' ORDER BY d.issued_at DESC,d.id DESC LIMIT 200", [$carrier['tenant_id'], $carrier['carrier_company_id']]));
        $operations = [];
        foreach ($this->all('SELECT o.id,o.service_date,o.status,o.customer_id,o.vehicle_id,o.plate_snapshot,o.issuer_snapshot,t.name AS customer_name,t.identification AS customer_identification,v.plate AS vehicle_plate FROM core.transport_operations o LEFT JOIN core.third_parties t ON t.tenant_id=o.tenant_id AND t.id=o.customer_id LEFT JOIN core.vehicles v ON v.tenant_id=o.tenant_id AND v.id=o.vehicle_id WHERE o.tenant_id=? AND o.carrier_company_id=? ORDER BY o.service_date DESC,o.id DESC LIMIT 200', [$carrier['tenant_id'], $carrier['carrier_company_id']]) as $operation) {
            $metrics = $this->operationMetrics((string) $operation['id']);
            $operations[] = ['id' => (string) $operation['id'], 'service_date' => $operation['service_date'], 'status' => $metrics['status'], 'customer' => $operation['customer_id'] === null ? null : ['id' => (string) $operation['customer_id'], 'name' => $operation['customer_name'], 'identification' => $operation['customer_identification']], 'vehicle' => $operation['vehicle_id'] === null ? null : ['id' => (string) $operation['vehicle_id'], 'plate' => $operation['plate_snapshot'] ?? $operation['vehicle_plate']], 'issuer_snapshot' => $this->decodeJson($operation['issuer_snapshot'] ?? null), 'metrics' => $metrics];
        }
        $allocations = array_map(fn (array $row): array => $this->mapAllocation($row), $this->all('SELECT * FROM fiscal.settlement_allocations WHERE tenant_id=? AND carrier_company_id=? ORDER BY created_at DESC,id DESC LIMIT 200', [$carrier['tenant_id'], $carrier['carrier_company_id']]));
        $issuer = $this->first('SELECT mode,partner_ruc,partner_establishment_code,partner_emission_point_code,next_sequential,key_reference,key_version,updated_at FROM core.carrier_issuer_profiles WHERE tenant_id=? AND carrier_company_id=?', [$carrier['tenant_id'], $carrier['carrier_company_id']]) ?? ['mode' => 'operator'];
        $signature = $this->signature((string) $carrier['tenant_id'], (string) $carrier['carrier_company_id']) ?? ['status' => 'missing'];
        $payment = $this->payment((string) $carrier['tenant_id'], (string) $carrier['carrier_company_id']);

        return [
            ...$summary,
            'profile' => ['id' => (string) $carrier['third_party_id'], 'name' => $carrier['name'], 'legal_name' => $carrier['legal_name'], 'trade_name' => $carrier['trade_name'], 'person_type' => $carrier['person_type'] ?? null, 'identification_type' => $carrier['identification_type'], 'identification_number' => $carrier['identification'], 'address' => $carrier['address'], 'phone' => $carrier['phone'], 'email' => $carrier['email'], 'roles' => $roles !== [] ? $roles : ['carrier']],
            'activities' => $activities,
            'establishments' => $establishments,
            'issuer' => $issuer,
            'signature' => $signature,
            'payment_account' => $payment,
            'documents' => ['received' => $received, 'credit_notes' => array_values(array_filter($received, static fn (array $item): bool => $item['document_type'] === 'credit_note')), 'other' => array_values(array_filter($received, static fn (array $item): bool => ! in_array($item['document_type'], ['invoice', 'credit_note'], true))), 'customer_invoices' => $customerDocuments],
            'operations' => $operations,
            'pending_operations' => array_values(array_filter($operations, static fn (array $operation): bool => in_array($operation['status'], ['open', 'partially_settled', 'blocked'], true))),
            'allocations' => $allocations,
            'audit' => $this->auditRows((string) $carrier['tenant_id'], (string) $carrier['carrier_company_id']),
        ];
    }

    /** @return array<string,mixed>|null */
    private function summaryRow(string $tenantId, string $carrierCompanyId): ?array
    {
        return $this->first("SELECT c.id AS carrier_company_id,c.tenant_id,t.id AS third_party_id,t.name,t.identification,COALESCE(t.identification_type,CASE WHEN length(t.identification)=13 THEN '04' ELSE '05' END) AS identification_type,t.person_type,t.address,t.phone,t.email,c.legal_name,c.trade_name,c.is_active,COALESCE((SELECT s.status FROM core.carrier_signatures s WHERE s.tenant_id=c.tenant_id AND s.carrier_company_id=c.id),'missing') AS signature_status,COALESCE((SELECT p.verification_status FROM core.carrier_payment_accounts p WHERE p.tenant_id=c.tenant_id AND p.carrier_company_id=c.id AND p.is_primary AND p.is_active),'missing') AS payment_account_status,EXISTS(SELECT 1 FROM core.transport_operations o JOIN fiscal.documents original ON original.tenant_id=o.tenant_id AND original.operation_id=o.id AND original.document_type='invoice' JOIN fiscal.credit_notes cn ON cn.tenant_id=original.tenant_id AND cn.original_document_id=original.id JOIN fiscal.documents note ON note.tenant_id=cn.tenant_id AND note.id=cn.id WHERE o.tenant_id=c.tenant_id AND o.carrier_company_id=c.id AND cn.settlement_review_status='pending' AND note.fiscal_status IN ('simulated','imported','authorized')) AS review_pending,COALESCE((SELECT SUM(d.total) FROM core.transport_operations o JOIN fiscal.documents d ON d.tenant_id=o.tenant_id AND d.operation_id=o.id WHERE o.tenant_id=c.tenant_id AND o.carrier_company_id=c.id AND d.document_type='invoice' AND d.fiscal_status IN ('simulated','imported','authorized')),0)::text AS customer_gross,COALESCE((SELECT SUM(d.total) FROM core.transport_operations o JOIN fiscal.documents original ON original.tenant_id=o.tenant_id AND original.operation_id=o.id AND original.document_type='invoice' JOIN fiscal.credit_notes cn ON cn.tenant_id=original.tenant_id AND cn.original_document_id=original.id JOIN fiscal.documents d ON d.tenant_id=cn.tenant_id AND d.id=cn.id WHERE o.tenant_id=c.tenant_id AND o.carrier_company_id=c.id AND cn.settlement_impact IN ('reduce_customer_operation','requires_regularization') AND d.fiscal_status IN ('simulated','imported','authorized')),0)::text AS customer_credits,COALESCE((SELECT SUM(r.total) FROM fiscal.received_documents r WHERE r.tenant_id=c.tenant_id AND r.issuer_id=c.third_party_id AND r.document_type='invoice' AND r.affects_transport AND r.fiscal_status IN ('received','imported','authorized')),0)::text AS partner_invoices,COALESCE((SELECT SUM(r.total) FROM fiscal.received_documents r WHERE r.tenant_id=c.tenant_id AND r.issuer_id=c.third_party_id AND r.document_type='credit_note' AND r.affects_transport AND r.fiscal_status IN ('received','imported','authorized')),0)::text AS partner_credits,COALESCE((SELECT SUM(a.amount) FROM fiscal.settlement_allocations a WHERE a.tenant_id=c.tenant_id AND a.carrier_company_id=c.id AND a.status='active'),0)::text AS allocated FROM core.carrier_companies c JOIN core.third_parties t ON t.tenant_id=c.tenant_id AND t.id=c.third_party_id WHERE c.tenant_id=? AND c.id=?", [$tenantId, $carrierCompanyId]);
    }

    /** @return array<string,mixed> */
    private function mapSummary(array $row): array
    {
        $metrics = SettlementPolicy::evaluate((string) ($row['customer_gross'] ?? '0.00'), (string) ($row['customer_credits'] ?? '0.00'), (string) ($row['partner_invoices'] ?? '0.00'), (string) ($row['partner_credits'] ?? '0.00'), (string) ($row['allocated'] ?? '0.00'), $this->bool($row['review_pending'] ?? false));

        return ['id' => (string) ($row['third_party_id'] ?? ''), 'third_party_id' => (string) ($row['third_party_id'] ?? ''), 'carrier_company_id' => (string) ($row['carrier_company_id'] ?? ''), 'name' => (string) ($row['name'] ?? ''), 'legal_name' => (string) ($row['legal_name'] ?? $row['name'] ?? ''), 'trade_name' => $row['trade_name'] ?? null, 'person_type' => $row['person_type'] ?? null, 'identification_type' => (string) ($row['identification_type'] ?? '05'), 'identification_number' => (string) ($row['identification'] ?? ''), 'address' => $row['address'] ?? null, 'phone' => $row['phone'] ?? null, 'email' => $row['email'] ?? null, 'is_active' => $this->bool($row['is_active'] ?? true), 'signature_status' => (string) ($row['signature_status'] ?? 'missing'), 'payment_account_status' => (string) ($row['payment_account_status'] ?? 'missing'), 'plates' => $this->plates((string) ($row['tenant_id'] ?? ''), (string) ($row['third_party_id'] ?? '')), 'metrics' => $metrics, 'accounting_status' => $metrics['status'], 'audit_ready' => true];
    }

    /** @return list<array<string,mixed>> */
    private function plates(string $tenantId, string $thirdPartyId): array
    {
        return array_map(fn (array $row): array => ['id' => (string) $row['id'], 'plate' => (string) $row['plate'], 'is_active' => $this->bool($row['is_active']), 'validity' => (string) $row['validity']], $this->all("SELECT DISTINCT ON (v.id) v.id,v.plate,a.validity::text AS validity,(a.validity @> CURRENT_DATE) AS is_active FROM core.carrier_affiliations f JOIN core.carrier_vehicle_assignments a ON a.tenant_id=f.tenant_id AND a.affiliation_id=f.id JOIN core.vehicles v ON v.tenant_id=a.tenant_id AND v.id=a.vehicle_id WHERE f.tenant_id=? AND f.third_party_id=? ORDER BY v.id,is_active DESC,lower(a.validity) DESC NULLS LAST,a.id DESC", [$tenantId, $thirdPartyId]));
    }

    /** @return array<string,mixed>|null */
    private function signature(string $tenantId, string $carrierCompanyId): ?array
    {
        $row = $this->first('SELECT status,certificate_fingerprint,certificate_serial,certificate_subject,valid_from,valid_until,key_reference,key_version,updated_at,replaced_at FROM core.carrier_signatures WHERE tenant_id=? AND carrier_company_id=?', [$tenantId, $carrierCompanyId]);

        return $row === null ? null : [...$row, 'key_version' => $row['key_version'] === null ? null : (int) $row['key_version']];
    }

    /** @return array<string,mixed>|null */
    private function payment(string $tenantId, string $carrierCompanyId): ?array
    {
        $row = $this->first('SELECT id,account_holder_name,account_holder_identification,financial_institution,account_type,account_number_last4,currency,verification_status,is_primary,is_active,updated_at FROM core.carrier_payment_accounts WHERE tenant_id=? AND carrier_company_id=? ORDER BY is_primary DESC,is_active DESC,updated_at DESC,id DESC LIMIT 1', [$tenantId, $carrierCompanyId]);
        if ($row === null) {
            return null;
        }

        return [...$row, 'id' => (string) $row['id'], 'masked_account' => '•••• '.(string) $row['account_number_last4'], 'is_primary' => $this->bool($row['is_primary']), 'is_active' => $this->bool($row['is_active'])];
    }

    /** @return array<string,mixed> */
    private function upsertPayment(string $tenantId, array $carrier, array $input, ?string $actorId): array
    {
        $existing = $this->payment($tenantId, (string) $carrier['carrier_company_id']);
        $holderName = trim((string) ($input['account_holder_name'] ?? $existing['account_holder_name'] ?? $carrier['legal_name']));
        $holderIdentification = CanonicalIdentification::normalize((string) ($input['account_holder_identification'] ?? $existing['account_holder_identification'] ?? $carrier['identification']));
        $institution = trim((string) ($input['financial_institution'] ?? $existing['financial_institution'] ?? ''));
        $type = strtolower(trim((string) ($input['account_type'] ?? $existing['account_type'] ?? '')));
        $status = strtolower(trim((string) ($input['verification_status'] ?? $existing['verification_status'] ?? 'pending')));
        $number = $input['account_number'] ?? null;
        $number = $number === null || trim((string) $number) === '' ? null : (preg_replace('/[\s-]+/', '', trim((string) $number)) ?: null);
        if ($holderName === '' || $institution === '' || ! in_array($type, ['savings', 'checking', 'other'], true) || ! in_array($status, ['pending', 'verified', 'blocked'], true)) {
            throw new DomainException('La información de pago no es válida.');
        }
        if ($existing === null && ($number === null || preg_match('/^[0-9]{4,30}$/D', $number) !== 1)) {
            throw new DomainException('El número de cuenta es obligatorio y debe contener entre 4 y 30 dígitos.');
        }
        if ($number !== null && preg_match('/^[0-9]{4,30}$/D', $number) !== 1) {
            throw new DomainException('El número de cuenta debe contener entre 4 y 30 dígitos.');
        }
        $active = array_key_exists('is_active', $input) ? (bool) $input['is_active'] : (bool) ($existing['is_active'] ?? true);
        $encrypted = $number === null ? null : Crypt::encryptString($number);
        $last4 = $number === null ? (string) ($existing['account_number_last4'] ?? '') : substr($number, -4);
        $actor = $this->uuidOrNull($actorId);
        if ($existing === null) {
            $this->statement('INSERT INTO core.carrier_payment_accounts(tenant_id,carrier_company_id,account_holder_name,account_holder_identification,financial_institution,account_type,account_number_ciphertext,account_number_last4,currency,verification_status,is_primary,is_active,created_by,updated_by) VALUES (?,?,?,?,?,?,?,?,?,? ,true,?,?,?)', [$tenantId, $carrier['carrier_company_id'], $holderName, $holderIdentification, $institution, $type, $encrypted, $last4, 'USD', $status, $active ? 'true' : 'false', $actor, $actor]);
        } elseif ($encrypted !== null) {
            $this->statement('UPDATE core.carrier_payment_accounts SET account_holder_name=?,account_holder_identification=?,financial_institution=?,account_type=?,account_number_ciphertext=?,account_number_last4=?,verification_status=?,is_primary=true,is_active=?,updated_by=?,updated_at=now() WHERE tenant_id=? AND id=?', [$holderName, $holderIdentification, $institution, $type, $encrypted, $last4, $status, $active ? 'true' : 'false', $actor, $tenantId, $existing['id']]);
        } else {
            $this->statement('UPDATE core.carrier_payment_accounts SET account_holder_name=?,account_holder_identification=?,financial_institution=?,account_type=?,verification_status=?,is_primary=true,is_active=?,updated_by=?,updated_at=now() WHERE tenant_id=? AND id=?', [$holderName, $holderIdentification, $institution, $type, $status, $active ? 'true' : 'false', $actor, $tenantId, $existing['id']]);
        }

        return $this->payment($tenantId, (string) $carrier['carrier_company_id']) ?? throw new RuntimeException('No se pudo leer la cuenta de pago actualizada.');
    }

    /** @return array<string,mixed> */
    private function configureIssuer(string $tenantId, string $carrierCompanyId, string $mode, array $input): array
    {
        if (! in_array($mode, ['operator', 'partner'], true)) {
            throw new DomainException('El modo de emisor no es válido.');
        }
        if ($mode === 'operator') {
            $establishment = $this->first('SELECT e.id,e.sri_code FROM core.establishments e JOIN core.companies c ON c.tenant_id=e.tenant_id AND c.id=e.company_id WHERE e.tenant_id=? AND e.sri_code=? AND e.is_active LIMIT 1', [$tenantId, $this->sriCode($input['operator_establishment_code'] ?? $input['establishment_code'] ?? '001', 'El establecimiento de la operadora')]);
            $point = $establishment === null ? null : $this->first('SELECT id,sri_code FROM core.emission_points WHERE tenant_id=? AND establishment_id=? AND sri_code=? AND is_active LIMIT 1', [$tenantId, $establishment['id'], $this->sriCode($input['operator_emission_point_code'] ?? $input['emission_point_code'] ?? '001', 'El punto de la operadora')]);
            if ($establishment === null || $point === null) {
                throw new DomainException('El emisor de la operadora no está configurado para ese establecimiento y punto.');
            }
            $data = ['mode' => 'operator', 'operator_company_id' => $this->first('SELECT id FROM core.companies WHERE tenant_id=? LIMIT 1', [$tenantId])['id'] ?? null, 'operator_establishment_id' => $establishment['id'], 'operator_emission_point_id' => $point['id']];
            $this->statement('INSERT INTO core.carrier_issuer_profiles(tenant_id,carrier_company_id,mode,operator_company_id,operator_establishment_id,operator_emission_point_id) VALUES (?,?,?,?,?,?) ON CONFLICT(tenant_id,carrier_company_id) DO UPDATE SET mode=EXCLUDED.mode,operator_company_id=EXCLUDED.operator_company_id,operator_establishment_id=EXCLUDED.operator_establishment_id,operator_emission_point_id=EXCLUDED.operator_emission_point_id,partner_ruc=NULL,partner_establishment_code=NULL,partner_emission_point_code=NULL,next_sequential=NULL,key_reference=NULL,updated_at=now()', [$tenantId, $carrierCompanyId, 'operator', $data['operator_company_id'], $data['operator_establishment_id'], $data['operator_emission_point_id']]);

            return $data;
        }
        $ruc = CanonicalIdentification::normalize((string) ($input['partner_ruc'] ?? data_get($input, 'issuer.ruc', '')));
        $establishmentCode = trim((string) ($input['partner_establishment_code'] ?? data_get($input, 'issuer.establishment_code', '')));
        $pointCode = trim((string) ($input['partner_emission_point_code'] ?? data_get($input, 'issuer.emission_point_code', '')));
        $sequence = (int) ($input['partner_next_sequential'] ?? data_get($input, 'issuer.next_sequential', 0));
        $keyReference = trim((string) ($input['partner_key_reference'] ?? data_get($input, 'issuer.key_reference', '')));
        if ($ruc === '' || ! preg_match('/^[0-9]{13}$/D', $ruc) || ! preg_match('/^[0-9]{3}$/D', $establishmentCode) || ! preg_match('/^[0-9]{3}$/D', $pointCode) || $sequence < 1 || $sequence > 999999999 || $keyReference === '') {
            throw new DomainException('El emisor propio requiere RUC, establecimiento, punto, secuencia y referencia de firma.');
        }
        $data = ['mode' => 'partner', 'partner_ruc' => $ruc, 'partner_establishment_code' => $establishmentCode, 'partner_emission_point_code' => $pointCode, 'next_sequential' => $sequence, 'key_reference' => $keyReference];
        $this->statement('INSERT INTO core.carrier_issuer_profiles(tenant_id,carrier_company_id,mode,partner_ruc,partner_establishment_code,partner_emission_point_code,next_sequential,key_reference) VALUES (?,?,? ,?,?,?,?,?) ON CONFLICT(tenant_id,carrier_company_id) DO UPDATE SET mode=EXCLUDED.mode,operator_company_id=NULL,operator_establishment_id=NULL,operator_emission_point_id=NULL,partner_ruc=EXCLUDED.partner_ruc,partner_establishment_code=EXCLUDED.partner_establishment_code,partner_emission_point_code=EXCLUDED.partner_emission_point_code,next_sequential=EXCLUDED.next_sequential,key_reference=EXCLUDED.key_reference,updated_at=now()', [$tenantId, $carrierCompanyId, 'partner', $ruc, $establishmentCode, $pointCode, $sequence, $keyReference]);
        $establishment = $this->first('INSERT INTO core.carrier_establishments(tenant_id,carrier_company_id,sri_code,name,is_active) VALUES (?,?,?,\'Establecimiento propio\',true) ON CONFLICT(tenant_id,carrier_company_id,sri_code) DO UPDATE SET name=EXCLUDED.name,is_active=true,updated_at=now() RETURNING id', [$tenantId, $carrierCompanyId, $establishmentCode]);
        if ($establishment === null) {
            throw new RuntimeException('No se pudo crear el establecimiento propio del socio.');
        }
        $point = $this->first('INSERT INTO core.carrier_emission_points(tenant_id,establishment_id,sri_code,name,next_sequential,is_active) VALUES (?,?,?,\'Punto de emisión propio\',?,true) ON CONFLICT(tenant_id,establishment_id,sri_code) DO UPDATE SET next_sequential=EXCLUDED.next_sequential,is_active=true RETURNING id', [$tenantId, $establishment['id'], $pointCode, $sequence]);
        if ($point === null) {
            throw new RuntimeException('No se pudo crear el punto de emisión propio del socio.');
        }
        $data['carrier_establishment_id'] = (string) $establishment['id'];
        $data['carrier_emission_point_id'] = (string) $point['id'];

        return $data;
    }

    /**
     * Return only non-secret issuer data. This is copied to an operation once
     * and is never rebuilt from mutable carrier settings when the operation is
     * read later.
     *
     * @return array<string,mixed>
     */
    private function issuerSnapshot(string $tenantId, string $carrierCompanyId): array
    {
        $profile = $this->first('SELECT mode,operator_company_id,operator_establishment_id,operator_emission_point_id,partner_ruc,partner_establishment_code,partner_emission_point_code,next_sequential,key_reference,key_version FROM core.carrier_issuer_profiles WHERE tenant_id=? AND carrier_company_id=?', [$tenantId, $carrierCompanyId]);
        if ($profile === null) {
            return ['mode' => 'operator', 'provider' => 'operator', 'configured' => false];
        }

        if ((string) $profile['mode'] === 'partner') {
            return [
                'mode' => 'partner',
                'provider' => 'partner',
                'ruc' => $profile['partner_ruc'],
                'establishment_code' => $profile['partner_establishment_code'],
                'emission_point_code' => $profile['partner_emission_point_code'],
                'sequential' => $profile['next_sequential'] === null ? null : (int) $profile['next_sequential'],
                'key_reference' => $profile['key_reference'],
                'key_version' => $profile['key_version'] === null ? null : (int) $profile['key_version'],
            ];
        }

        $operator = $this->first('SELECT c.ruc,c.name AS company_name,e.sri_code AS establishment_code,p.sri_code AS emission_point_code FROM core.companies c JOIN core.establishments e ON e.tenant_id=c.tenant_id AND e.company_id=c.id JOIN core.emission_points p ON p.tenant_id=e.tenant_id AND p.establishment_id=e.id WHERE c.tenant_id=? AND c.id=? AND e.id=? AND p.id=?', [$tenantId, $profile['operator_company_id'], $profile['operator_establishment_id'], $profile['operator_emission_point_id']]);

        return [
            'mode' => 'operator',
            'provider' => 'operator',
            'company_id' => $profile['operator_company_id'],
            'company_name' => $operator['company_name'] ?? null,
            'ruc' => $operator['ruc'] ?? null,
            'establishment_id' => $profile['operator_establishment_id'],
            'establishment_code' => $operator['establishment_code'] ?? null,
            'emission_point_id' => $profile['operator_emission_point_id'],
            'emission_point_code' => $operator['emission_point_code'] ?? null,
        ];
    }

    /** @param list<string> $plates @return list<array<string,mixed>> */
    private function syncPlates(string $tenantId, string $thirdPartyId, array $plates): array
    {
        $affiliation = $this->first('SELECT * FROM core.carrier_affiliations WHERE tenant_id=? AND third_party_id=? AND validity @> CURRENT_DATE ORDER BY lower(validity) DESC,id DESC LIMIT 1 FOR UPDATE', [$tenantId, $thirdPartyId]);
        if ($affiliation === null && $plates !== []) {
            $affiliation = $this->first("INSERT INTO core.carrier_affiliations(tenant_id,third_party_id,validity) VALUES (?,?,daterange(CURRENT_DATE,NULL,'[)')) RETURNING *", [$tenantId, $thirdPartyId]);
        }
        if ($affiliation === null) {
            return [];
        }
        $current = $this->all('SELECT a.id AS assignment_id,v.id AS vehicle_id,v.plate,a.validity,a.validity @> CURRENT_DATE AS is_active FROM core.carrier_vehicle_assignments a JOIN core.vehicles v ON v.tenant_id=a.tenant_id AND v.id=a.vehicle_id WHERE a.tenant_id=? AND a.affiliation_id=? FOR UPDATE', [$tenantId, $affiliation['id']]);
        $desired = array_fill_keys($plates, true);
        foreach ($current as $row) {
            if ($this->bool($row['is_active']) && ! isset($desired[$this->normalizePlate($row['plate'])])) {
                $this->statement("UPDATE core.carrier_vehicle_assignments SET validity=daterange(lower(validity),CURRENT_DATE,'[)') WHERE tenant_id=? AND id=? AND validity @> CURRENT_DATE", [$tenantId, $row['assignment_id']]);
            }
        }
        foreach ($plates as $plate) {
            $vehicle = $this->first("SELECT * FROM core.vehicles WHERE tenant_id=? AND upper(regexp_replace(trim(plate),'\\s+','','g'))=? FOR UPDATE", [$tenantId, $plate]);
            if ($vehicle === null) {
                $vehicle = $this->first('INSERT INTO core.vehicles(tenant_id,plate) VALUES (?,?) RETURNING *', [$tenantId, $plate]);
            }
            if ($vehicle === null) {
                throw new RuntimeException('No se pudo crear el vehículo.');
            }
            $conflict = $this->first('SELECT f.third_party_id FROM core.carrier_vehicle_assignments a JOIN core.carrier_affiliations f ON f.tenant_id=a.tenant_id AND f.id=a.affiliation_id WHERE a.tenant_id=? AND a.vehicle_id=? AND a.validity @> CURRENT_DATE FOR UPDATE', [$tenantId, $vehicle['id']]);
            if ($conflict !== null && (string) $conflict['third_party_id'] !== $thirdPartyId) {
                throw new DomainException('La placa '.$plate.' ya está asignada a otro socio transportista.');
            }
            if ($conflict === null) {
                $this->statement("INSERT INTO core.carrier_vehicle_assignments(tenant_id,affiliation_id,vehicle_id,validity) VALUES (?,?,?,daterange(CURRENT_DATE,NULL,'[)'))", [$tenantId, $affiliation['id'], $vehicle['id']]);
            }
        }

        return $this->plates($tenantId, $thirdPartyId);
    }

    /** @return array<string,mixed> */
    private function mapReceivedDocument(array $row): array
    {
        $operation = $this->first('SELECT id FROM core.transport_operations WHERE tenant_id=? AND id=?', [$row['tenant_id'], $row['operation_id']]);
        $original = $row['original_received_document_id'] === null ? null : $this->first('SELECT id FROM fiscal.received_documents WHERE tenant_id=? AND id=?', [$row['tenant_id'], $row['original_received_document_id']]);

        return ['id' => (string) $row['id'], 'document_type' => (string) $row['document_type'], 'reference' => (string) $row['reference'], 'issue_date' => substr((string) $row['issued_at'], 0, 10), 'total' => (string) $row['total'], 'fiscal_status' => (string) $row['fiscal_status'], 'access_key' => $row['access_key'] ?? null, 'authorization_number' => $row['authorization_number'] ?? null, 'operation_id' => (string) ($operation['id'] ?? $row['operation_id']), 'original_document_id' => (string) ($original['id'] ?? ''), 'reason' => $row['reason'] ?? null, 'affects_transport' => $this->bool($row['affects_transport'] ?? false), 'xml_sha256' => (string) $row['xml_sha256'], 'has_original_xml' => true];
    }

    /** @return array<string,mixed> */
    private function mapCustomerDocument(array $row): array
    {
        return ['id' => (string) $row['id'], 'document_type' => (string) $row['document_type'], 'issue_date' => substr((string) $row['issued_at'], 0, 10), 'total' => (string) $row['total'], 'fiscal_status' => (string) $row['fiscal_status'], 'operation_id' => (string) $row['operation_id']];
    }

    /** @return array<string,mixed> */
    private function mapAllocation(array $row): array
    {
        $received = $this->first('SELECT id,reference FROM fiscal.received_documents WHERE tenant_id=? AND id=?', [$row['tenant_id'], $row['received_document_id']]);

        return ['id' => (string) $row['id'], 'operation_id' => (string) $row['operation_id'], 'received_document_id' => (string) $row['received_document_id'], 'received_reference' => $received['reference'] ?? null, 'customer_document_id' => $row['customer_document_id'] === null ? null : (string) $row['customer_document_id'], 'amount' => (string) $row['amount'], 'status' => (string) $row['status'], 'created_at' => $row['created_at'] ?? null];
    }

    /** @return list<array<string,mixed>> */
    private function auditRows(string $tenantId, string $carrierCompanyId): array
    {
        return array_map(fn (array $row): array => $this->mapAudit($row), $this->all('SELECT a.* FROM platform.audit_events a WHERE a.tenant_id=? AND (a.entity_id=? OR a.operation_id IN (SELECT id FROM core.transport_operations WHERE tenant_id=? AND carrier_company_id=?)) ORDER BY a.occurred_at DESC,a.id DESC LIMIT 200', [$tenantId, $carrierCompanyId, $tenantId, $carrierCompanyId]));
    }

    /** @return array<string,mixed> */
    private function mapAudit(array $row): array
    {
        $decode = static function (mixed $value): array {
            if (is_array($value)) {
                return $value;
            }
            $decoded = json_decode((string) $value, true);

            return is_array($decoded) ? $decoded : [];
        };

        return ['id' => (string) $row['id'], 'action' => (string) $row['action'], 'entity_type' => (string) $row['entity_type'], 'entity_id' => $row['entity_id'] ?? null, 'operation_id' => $row['operation_id'] ?? null, 'actor_id' => $row['actor_id'] ?? null, 'before' => $decode($row['before_state'] ?? []), 'after' => $decode($row['after_state'] ?? []), 'metadata' => $decode($row['metadata'] ?? []), 'occurred_at' => $row['occurred_at'] ?? null];
    }

    private function auditEvent(string $tenantId, ?string $actorId, string $action, string $entityType, ?string $entityId, ?string $operationId, array $before, array $after): void
    {
        $requestId = null;
        $idempotencyKey = null;
        if (function_exists('request') && app()->bound('request')) {
            $request = request();
            $requestId = $request->header('X-Request-Id');
            $idempotencyKey = $request->header('Idempotency-Key');
        }
        $this->statement('INSERT INTO platform.audit_events(tenant_id,actor_id,action,entity_type,entity_id,operation_id,request_id,before_state,after_state,metadata) VALUES (?,?,?,?,?,?,?,?::jsonb,?::jsonb,?::jsonb)', [$tenantId, $this->uuidOrNull($actorId), $action, $entityType, $entityId, $operationId, $requestId, json_encode($before, JSON_THROW_ON_ERROR), json_encode($after, JSON_THROW_ON_ERROR), json_encode(['source' => 'third-party-canonical', 'idempotency_key' => $idempotencyKey], JSON_THROW_ON_ERROR)]);
    }

    /** @return array<string,mixed> */
    private function operationMetrics(string $operationId, bool $includeReview = true): array
    {
        $row = $this->first("SELECT COALESCE((SELECT SUM(total) FROM fiscal.documents WHERE operation_id=? AND document_type='invoice' AND fiscal_status IN ('simulated','imported','authorized')),0)::text AS customer_gross,COALESCE((SELECT SUM(d.total) FROM fiscal.credit_notes cn JOIN fiscal.documents d ON d.tenant_id=cn.tenant_id AND d.id=cn.id JOIN fiscal.documents original ON original.tenant_id=cn.tenant_id AND original.id=cn.original_document_id WHERE original.operation_id=? AND cn.settlement_impact IN ('reduce_customer_operation','requires_regularization') AND d.fiscal_status IN ('simulated','imported','authorized')),0)::text AS customer_credits,COALESCE((SELECT SUM(total) FROM fiscal.received_documents WHERE operation_id=? AND document_type='invoice' AND affects_transport AND fiscal_status IN ('received','imported','authorized')),0)::text AS partner_invoices,COALESCE((SELECT SUM(total) FROM fiscal.received_documents WHERE operation_id=? AND document_type='credit_note' AND affects_transport AND fiscal_status IN ('received','imported','authorized')),0)::text AS partner_credits,COALESCE((SELECT SUM(amount) FROM fiscal.settlement_allocations WHERE operation_id=? AND status='active'),0)::text AS allocated", [$operationId, $operationId, $operationId, $operationId, $operationId]) ?? [];
        $reviewPending = false;
        if ($includeReview) {
            $reviewPending = (bool) ($this->first("SELECT EXISTS(SELECT 1 FROM fiscal.credit_notes cn JOIN fiscal.documents d ON d.tenant_id=cn.tenant_id AND d.id=cn.id JOIN fiscal.documents original ON original.tenant_id=cn.tenant_id AND original.id=cn.original_document_id WHERE cn.tenant_id=current_setting('app.tenant_id', true)::uuid AND original.operation_id=? AND cn.settlement_review_status='pending' AND d.fiscal_status IN ('simulated','imported','authorized')) AS review_pending", [$operationId])['review_pending'] ?? false);
        }

        return SettlementPolicy::evaluate((string) ($row['customer_gross'] ?? '0.00'), (string) ($row['customer_credits'] ?? '0.00'), (string) ($row['partner_invoices'] ?? '0.00'), (string) ($row['partner_credits'] ?? '0.00'), (string) ($row['allocated'] ?? '0.00'), $reviewPending);
    }

    private function normalizePersonType(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }
        $personType = strtolower(trim((string) $value));
        if (! in_array($personType, ['natural', 'juridical'], true)) {
            throw new DomainException('El tipo de persona no es válido.');
        }

        return $personType;
    }

    private function ensureActivity(string $activityId): void
    {
        if ($this->first('SELECT id FROM core.economic_activities WHERE id=?', [$activityId]) === null) {
            throw new DomainException('La actividad económica no existe en el catálogo consolidado.');
        }
    }

    private function dateValue(mixed $value): string
    {
        $date = trim((string) ($value ?? date('Y-m-d')));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date) !== 1) {
            throw new DomainException('La fecha no es válida.');
        }

        return $date;
    }

    private function sriCode(mixed $value, string $label): string
    {
        $code = str_pad(trim((string) $value), 3, '0', STR_PAD_LEFT);
        if (preg_match('/^[0-9]{3}$/D', $code) !== 1) {
            throw new DomainException($label.' debe tener tres dígitos.');
        }

        return $code;
    }

    /** @return list<string> */
    private function requestedPlates(array $input): array
    {
        $raw = array_key_exists('plates', $input) ? $input['plates'] : (array_key_exists('plate', $input) ? [$input['plate']] : []);
        if ($raw === null) {
            return [];
        }
        if (! is_array($raw)) {
            throw new DomainException('La lista de placas no es válida.');
        }

        $result = [];
        foreach ($raw as $value) {
            if (trim((string) $value) !== '') {
                $result[] = $this->normalizeCarrierPlate($value);
            }
        }

        return array_values(array_unique($result));
    }

    private function normalizeCarrierPlate(mixed $value): string
    {
        $plate = strtoupper(preg_replace('/\s+/', '', trim((string) $value)) ?: '');
        if ($plate === '' || preg_match('/^[A-Z0-9-]{3,12}$/D', $plate) !== 1) {
            throw new DomainException('La placa no es válida.');
        }

        return $plate;
    }

    private function normalizePlate(string $plate): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($plate)) ?: '');
    }

    private function money(string $value): string
    {
        $value = trim($value);
        if (preg_match('/^(?:0|[1-9][0-9]*)(?:\.[0-9]{1,2})?$/D', $value) !== 1) {
            throw new DomainException('Importe contable inválido.');
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '0');

        return (int) $whole.'.'.str_pad($fraction, 2, '0');
    }

    private function compareMoney(string $left, string $right): int
    {
        $cents = static function (string $value): int {
            [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '0');

            return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
        };

        return $cents($this->money($left)) <=> $cents($this->money($right));
    }

    private function requireUuid(string $value): string
    {
        $value = strtolower(trim($value));
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/D', $value) !== 1) {
            throw new DomainException('Identificador UUID inválido.');
        }

        return $value;
    }

    private function bool(mixed $value): bool
    {
        return $value === true || in_array(strtolower((string) $value), ['1', 't', 'true', 'yes'], true);
    }

    /** @return array<string,mixed>|null */
    private function decodeJson(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }

    /** @return array<string,mixed> */
    private function safePayment(?array $row): array
    {
        return $row === null ? [] : array_intersect_key($row, array_flip(['id', 'account_holder_name', 'account_holder_identification', 'financial_institution', 'account_type', 'account_number_last4', 'currency', 'verification_status', 'is_primary', 'is_active']));
    }

    /** @return array<string,mixed> */
    private function safeSignature(?array $row): array
    {
        return $row === null ? [] : array_intersect_key($row, array_flip(['status', 'certificate_fingerprint', 'certificate_serial', 'certificate_subject', 'valid_from', 'valid_until', 'key_reference', 'key_version']));
    }

    private function canonicalJson(array $value): string
    {
        $normalize = function (mixed $item) use (&$normalize): mixed {
            if (! is_array($item)) {
                return $item;
            }
            if (array_is_list($item)) {
                return array_map($normalize, $item);
            }
            ksort($item);

            return array_map($normalize, $item);
        };

        return json_encode($normalize($value), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function uuidOrNull(?string $value): ?string
    {
        return is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/Di', $value) === 1 ? strtolower($value) : null;
    }

    /**
     * Run a callback inside a tenant-scoped transaction on master_v3.
     * The tenant context is set session-level so RLS policies apply to every
     * statement executed within the transaction.
     *
     * @template T
     * @param \Closure(): T $callback
     * @return T
     */
    private function withTenant(string $tenantId, \Closure $callback): mixed
    {
        $this->requireUuid($tenantId);

        return DB::connection('master_v3')->transaction(function () use ($tenantId, $callback): mixed {
            DB::connection('master_v3')->statement("SELECT set_config('app.tenant_id', ?, true)", [$tenantId]);

            return $callback();
        });
    }

    /** @return list<array<string,mixed>> */
    private function all(string $sql, array $bindings = []): array
    {
        $rows = DB::connection('master_v3')->select($sql, $bindings);
        $result = [];
        foreach ($rows as $row) {
            $result[] = (array) $row;
        }

        return $result;
    }

    /** @return array<string,mixed>|null */
    private function first(string $sql, array $bindings = []): ?array
    {
        $row = DB::connection('master_v3')->selectOne($sql, $bindings);

        return $row === null ? null : (array) $row;
    }

    private function statement(string $sql, array $bindings = []): void
    {
        DB::connection('master_v3')->statement($sql, $bindings);
    }
}
