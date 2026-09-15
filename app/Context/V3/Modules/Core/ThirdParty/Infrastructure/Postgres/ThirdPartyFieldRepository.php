<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres;

use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class ThirdPartyFieldRepository implements \App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldRepositoryInterface
{
    private const DEFINITIONS_TABLE = 'core.third_party_field_definitions';

    private const VALUES_TABLE = 'core.third_party_field_values';

    private const THIRD_PARTIES_TABLE = 'core.third_parties';

    /** @var list<string> */
    private const SCOPES = ['customer', 'carrier', 'both'];

    /** @var list<string> */
    private const DATA_TYPES = ['text', 'number', 'date', 'boolean', 'select'];

    /** @return array<string, mixed> */
    public function values(string $tenantId, string $thirdPartyId): array
    {
        $thirdPartyId = $this->requireUuid($thirdPartyId);

        return $this->valuesByThirdPartyIds($tenantId, [$thirdPartyId])[$thirdPartyId] ?? [];
    }

    /** @param list<string> $thirdPartyIds @return array<string, array<string, mixed>> */
    public function valuesByThirdPartyIds(string $tenantId, array $thirdPartyIds): array
    {
        $ids = array_values(array_unique(array_map(fn (string $id): string => $this->requireUuid($id), $thirdPartyIds)));
        $result = array_fill_keys($ids, []);
        if ($ids === []) {
            return $result;
        }

        $rows = DB::connection('master_v3')->table(self::VALUES_TABLE.' as v')
            ->join(self::DEFINITIONS_TABLE.' as d', function ($join): void {
                $join->on('d.tenant_id', '=', 'v.tenant_id')
                    ->on('d.id', '=', 'v.definition_id');
            })
            ->where('v.tenant_id', $tenantId)
            ->whereIn('v.third_party_id', $ids)
            ->select(['v.third_party_id', 'd.code', 'v.value'])
            ->orderBy('d.sort_order')
            ->get();

        foreach ($rows as $row) {
            $decoded = json_decode((string) $row->value, true);
            $result[(string) $row->third_party_id][(string) $row->code] = json_last_error() === JSON_ERROR_NONE ? $decoded : $row->value;
        }

        return $result;
    }

    /** @param array<string, mixed> $values @return array<string, mixed> */
    public function replaceValues(string $tenantId, string $thirdPartyId, array $values, ?string $actorId = null): array
    {
        $thirdPartyId = $this->requireUuid($thirdPartyId);
        $db = DB::connection('master_v3');

        return $db->transaction(function () use ($db, $tenantId, $thirdPartyId, $values, $actorId): array {
            $party = $db->table(self::THIRD_PARTIES_TABLE.' as t')
                ->where('t.tenant_id', $tenantId)
                ->where('t.id', $thirdPartyId)
                ->where('t.is_active', true)
                ->whereExists(function ($query) use ($tenantId, $thirdPartyId): void {
                    $query->selectRaw('1')
                        ->from('core.third_party_roles as r')
                        ->whereColumn('r.tenant_id', 't.tenant_id')
                        ->whereColumn('r.third_party_id', 't.id')
                        ->whereIn('r.role', ['customer', 'carrier']);
                })
                ->first();
            if ($party === null) {
                throw new DomainException('El tercero no existe o no tiene un rol editable.');
            }

            $roles = $db->table('core.third_party_roles')
                ->where('tenant_id', $tenantId)
                ->where('third_party_id', $thirdPartyId)
                ->pluck('role')
                ->map(static fn ($role): string => (string) $role)
                ->all();
            $definitions = $db->table(self::DEFINITIONS_TABLE)
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->get()
                ->keyBy('code');
            $current = $this->values($tenantId, $thirdPartyId);

            foreach ($values as $code => $value) {
                if (! is_string($code) || ! isset($definitions[$code])) {
                    throw new DomainException('El campo de tercero no existe o está inactivo: '.(string) $code);
                }
                $definition = $definitions[$code];
                if (! $this->definitionApplies((string) $definition->scope, $roles)) {
                    throw new DomainException('El campo de tercero no aplica al rol actual: '.$code);
                }
                $normalized = $this->validateValue($definition, $value, $code);
                if ($normalized === null) {
                    unset($current[$code]);
                } else {
                    $current[$code] = $normalized;
                }
            }

            foreach ($definitions as $definition) {
                if (! $this->definitionApplies((string) $definition->scope, $roles) || ! (bool) $definition->is_required) {
                    continue;
                }
                $code = (string) $definition->code;
                if (! array_key_exists($code, $current) || $this->isBlank($current[$code])) {
                    throw new DomainException('El campo de tercero obligatorio no fue informado: '.(string) $definition->label);
                }
            }

            foreach ($values as $code => $value) {
                if (! array_key_exists($code, $current)) {
                    $definition = $definitions[$code] ?? null;
                    if ($definition !== null) {
                        $db->table(self::VALUES_TABLE)
                            ->where('tenant_id', $tenantId)
                            ->where('third_party_id', $thirdPartyId)
                            ->where('definition_id', $definition->id)
                            ->delete();
                    }
                    continue;
                }
                $definition = $definitions[$code];
                $json = json_encode($current[$code], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                $db->statement(
                    'INSERT INTO '.self::VALUES_TABLE.' (tenant_id,id,third_party_id,definition_id,value,updated_by,updated_at) VALUES (?,?,?,?,?::jsonb,?,now()) ON CONFLICT (tenant_id,third_party_id,definition_id) DO UPDATE SET value=EXCLUDED.value,updated_by=EXCLUDED.updated_by,updated_at=now()',
                    [$tenantId, (string) Str::uuid(), $thirdPartyId, $definition->id, $json, $this->uuidOrNull($actorId)],
                );
            }

            $after = $this->values($tenantId, $thirdPartyId);
            $this->audit($tenantId, $actorId, 'third_party.fields.updated', 'third_party', $thirdPartyId, [], $after);

            return $after;
        });
    }

    private function definitionApplies(string $scope, array $roles): bool
    {
        return $scope === 'both' || in_array($scope, $roles, true);
    }

    private function validateValue(object $definition, mixed $value, string $code): mixed
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            if ((bool) $definition->is_required) {
                throw new DomainException('El campo de tercero obligatorio no fue informado: '.$code);
            }

            return null;
        }

        $validation = json_decode((string) ($definition->validation ?? '{}'), true);
        $validation = is_array($validation) ? $validation : [];

        return match ((string) $definition->data_type) {
            'text' => $this->validateText($value, $validation, $code),
            'number' => $this->validateNumber($value, $validation, $code),
            'date' => $this->validateDate($value, $code),
            'boolean' => $this->validateBoolean($value, $code),
            'select' => $this->validateSelect($value, $validation, $code),
            default => throw new DomainException('El tipo de dato del campo no es válido: '.$code),
        };
    }

    private function validateText(mixed $value, array $validation, string $code): string
    {
        if (! is_scalar($value)) {
            throw new DomainException('El valor del campo debe ser texto: '.$code);
        }
        $value = trim((string) $value);
        $min = isset($validation['min_length']) ? max(0, (int) $validation['min_length']) : null;
        $max = isset($validation['max_length']) ? max(0, (int) $validation['max_length']) : null;
        if (($min !== null && mb_strlen($value) < $min) || ($max !== null && mb_strlen($value) > $max)) {
            throw new DomainException('El valor del campo no cumple su longitud: '.$code);
        }

        return $value;
    }

    private function validateNumber(mixed $value, array $validation, string $code): float|int
    {
        if (! is_numeric($value)) {
            throw new DomainException('El valor del campo debe ser numérico: '.$code);
        }
        $number = (float) $value;
        if (isset($validation['min']) && $number < (float) $validation['min']) {
            throw new DomainException('El valor del campo es menor al mínimo permitido: '.$code);
        }
        if (isset($validation['max']) && $number > (float) $validation['max']) {
            throw new DomainException('El valor del campo supera el máximo permitido: '.$code);
        }

        return fmod($number, 1.0) === 0.0 ? (int) $number : $number;
    }

    private function validateDate(mixed $value, string $code): string
    {
        $value = (string) $value;
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new DomainException('El valor del campo debe ser una fecha YYYY-MM-DD: '.$code);
        }

        return $value;
    }

    private function validateBoolean(mixed $value, string $code): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) && in_array($value, [0, 1], true)) {
            return $value === 1;
        }
        if (is_string($value) && in_array(strtolower(trim($value)), ['true', 'false', '0', '1'], true)) {
            return in_array(strtolower(trim($value)), ['true', '1'], true);
        }

        throw new DomainException('El valor del campo debe ser booleano: '.$code);
    }

    private function validateSelect(mixed $value, array $validation, string $code): string
    {
        $value = trim((string) $value);
        $options = array_map(static fn ($option): string => (string) $option, is_array($validation['options'] ?? null) ? $validation['options'] : []);
        if (! in_array($value, $options, true)) {
            throw new DomainException('El valor del campo no es una opción válida: '.$code);
        }

        return $value;
    }

    private function isBlank(mixed $value): bool
    {
        return $value === null || (is_string($value) && trim($value) === '');
    }

    private function requireUuid(string $value): string
    {
        $value = strtolower(trim($value));
        if (Str::isUuid($value) !== true) {
            throw new DomainException('El identificador de ThirdParty debe ser un UUID.');
        }

        return $value;
    }

    private function uuidOrNull(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value !== '' && Str::isUuid($value) ? $value : null;
    }

    private function audit(string $tenantId, ?string $actorId, string $action, string $entityType, string $entityId, array $before, array $after): void
    {
        DB::connection('master_v3')->table('platform.audit_events')->insert([
            'tenant_id' => $tenantId,
            'actor_id' => $this->uuidOrNull($actorId),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $this->uuidOrNull($entityId),
            'before_state' => json_encode($before, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'after_state' => json_encode($after, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'metadata' => json_encode(['source' => 'third-party-v3'], JSON_THROW_ON_ERROR),
        ]);
    }
}
