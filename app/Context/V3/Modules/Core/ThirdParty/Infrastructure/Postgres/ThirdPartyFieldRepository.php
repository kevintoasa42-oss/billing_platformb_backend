<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldRepositoryInterface;
use DomainException;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class ThirdPartyFieldRepository implements ThirdPartyFieldRepositoryInterface
{
    private const DEFINITIONS_TABLE = 'core.third_party_field_definitions';

    private const VALUES_TABLE = 'core.third_party_field_values';

    private const THIRD_PARTIES_TABLE = 'core.third_parties';

    /** @var list<string> */
    private const SCOPES = ['customer', 'carrier', 'both'];

    /** @var list<string> */
    private const DATA_TYPES = ['text', 'number', 'date', 'boolean', 'select'];

    public function definitions(string $tenantId, ?string $scope = null, bool $activeOnly = true): array
    {
        $query = $this->db()->table(self::DEFINITIONS_TABLE)
            ->where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('label');

        if ($scope !== null && trim($scope) !== '') {
            $scope = strtolower(trim($scope));
            if (! in_array($scope, self::SCOPES, true)) {
                throw new DomainException('El alcance del campo de tercero no es válido.');
            }
            if ($scope !== 'both') {
                $query->whereIn('scope', [$scope, 'both']);
            }
        }

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return array_map(fn (object $row): array => $this->mapDefinition($row), $query->get()->all());
    }

    public function createDefinition(string $tenantId, array $input, ?string $actorId = null): array
    {
        $data = $this->normalizeDefinition($input, true);
        $id = (string) Str::uuid();

        try {
            $this->db()->table(self::DEFINITIONS_TABLE)->insert([
                'tenant_id' => $tenantId,
                'id' => $id,
                ...$data,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $error) {
            if (str_contains(strtolower($error->getMessage()), 'third_party_field_definitions')) {
                throw new DomainException('Ya existe un campo de tercero con ese código.', 0, $error);
            }
            throw $error;
        }

        $definition = $this->definition($tenantId, $id);
        if ($definition === null) {
            throw new DomainException('No se pudo guardar la definición del campo de tercero.');
        }
        $this->audit($tenantId, $actorId, 'third_party.field_definition.created', 'third_party_field_definition', $id, [], $definition);

        return $definition;
    }

    public function updateDefinition(string $tenantId, string $id, array $input, ?string $actorId = null): ?array
    {
        $id = $this->requireUuid($id);
        $before = $this->definition($tenantId, $id);
        if ($before === null) {
            return null;
        }

        $data = $this->normalizeDefinition(array_merge($before, $input), true);
        try {
            $this->db()->table(self::DEFINITIONS_TABLE)
                ->where('tenant_id', $tenantId)
                ->where('id', $id)
                ->update([...$data, 'updated_at' => now()]);
        } catch (Throwable $error) {
            if (str_contains(strtolower($error->getMessage()), 'third_party_field_definitions')) {
                throw new DomainException('Ya existe un campo de tercero con ese código.', 0, $error);
            }
            throw $error;
        }

        $after = $this->definition($tenantId, $id);
        if ($after !== null) {
            $this->audit($tenantId, $actorId, 'third_party.field_definition.updated', 'third_party_field_definition', $id, $before, $after);
        }

        return $after;
    }

    public function deactivateDefinition(string $tenantId, string $id, ?string $actorId = null): ?array
    {
        $id = $this->requireUuid($id);
        $before = $this->definition($tenantId, $id);
        if ($before === null) {
            return null;
        }

        $this->db()->table(self::DEFINITIONS_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->update(['is_active' => false, 'updated_at' => now()]);
        $after = $this->definition($tenantId, $id);
        if ($after !== null) {
            $this->audit($tenantId, $actorId, 'third_party.field_definition.deactivated', 'third_party_field_definition', $id, $before, $after);
        }

        return $after;
    }

    public function values(string $tenantId, string $thirdPartyId): array
    {
        $thirdPartyId = $this->requireUuid($thirdPartyId);

        return $this->valuesByThirdPartyIds($tenantId, [$thirdPartyId])[$thirdPartyId] ?? [];
    }

    public function valuesByThirdPartyIds(string $tenantId, array $thirdPartyIds): array
    {
        $ids = array_values(array_unique(array_map(fn (string $id): string => $this->requireUuid($id), $thirdPartyIds)));
        $result = array_fill_keys($ids, []);
        if ($ids === []) {
            return $result;
        }

        $rows = $this->db()->table(self::VALUES_TABLE.' as v')
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

    public function replaceValues(string $tenantId, string $thirdPartyId, array $values, ?string $actorId = null): array
    {
        $thirdPartyId = $this->requireUuid($thirdPartyId);
        $db = $this->db();

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

    /** @return array<string, mixed>|null */
    private function definition(string $tenantId, string $id): ?array
    {
        $row = $this->db()->table(self::DEFINITIONS_TABLE)
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();

        return $row === null ? null : $this->mapDefinition($row);
    }

    /** @return array<string, mixed> */
    private function mapDefinition(object $row): array
    {
        $validation = json_decode((string) ($row->validation ?? '{}'), true);

        return [
            'id' => (string) $row->id,
            'code' => (string) $row->code,
            'label' => (string) $row->label,
            'scope' => (string) $row->scope,
            'data_type' => (string) $row->data_type,
            'validation' => is_array($validation) ? $validation : [],
            'sort_order' => (int) $row->sort_order,
            'is_required' => (bool) $row->is_required,
            'is_active' => (bool) $row->is_active,
        ];
    }

    /** @return array<string, mixed> */
    private function normalizeDefinition(array $input, bool $requireIdentity): array
    {
        $code = strtolower(trim((string) ($input['code'] ?? '')));
        $label = trim((string) ($input['label'] ?? ''));
        $scope = strtolower(trim((string) ($input['scope'] ?? 'both')));
        $dataType = strtolower(trim((string) ($input['data_type'] ?? 'text')));
        $validation = is_array($input['validation'] ?? null) ? $input['validation'] : [];

        if (($requireIdentity && $code === '') || ($code !== '' && preg_match('/^[a-z][a-z0-9_]{1,80}$/D', $code) !== 1)) {
            throw new DomainException('El código del campo debe usar minúsculas, números y guiones bajos.');
        }
        if (($requireIdentity && $label === '') || ($label !== '' && mb_strlen($label) > 160)) {
            throw new DomainException('La etiqueta del campo es obligatoria y no puede superar 160 caracteres.');
        }
        if (! in_array($scope, self::SCOPES, true)) {
            throw new DomainException('El alcance del campo de tercero no es válido.');
        }
        if (! in_array($dataType, self::DATA_TYPES, true)) {
            throw new DomainException('El tipo de dato del campo de tercero no es válido.');
        }
        if ($dataType === 'select') {
            $options = $validation['options'] ?? null;
            if (! is_array($options) || $options === []) {
                throw new DomainException('Un campo de selección debe definir opciones.');
            }
            $validation['options'] = array_values(array_filter(array_map(static fn ($option): string => trim((string) $option), $options), static fn (string $option): bool => $option !== ''));
            if ($validation['options'] === []) {
                throw new DomainException('Un campo de selección debe definir opciones no vacías.');
            }
        }

        return [
            'code' => $code,
            'label' => $label,
            'scope' => $scope,
            'data_type' => $dataType,
            'validation' => json_encode($validation, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'sort_order' => max(0, min(100000, (int) ($input['sort_order'] ?? 0))),
            'is_required' => (bool) ($input['is_required'] ?? false),
            'is_active' => (bool) ($input['is_active'] ?? true),
        ];
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
        $this->db()->table('platform.audit_events')->insert([
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

    private function db(): Connection
    {
        return DB::connection('master_v3');
    }
}
