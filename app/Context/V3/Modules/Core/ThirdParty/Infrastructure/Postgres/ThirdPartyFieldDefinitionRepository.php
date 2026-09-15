<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyFieldDefinition;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldDefinitionRepositoryInterface;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

final class ThirdPartyFieldDefinitionRepository implements ThirdPartyFieldDefinitionRepositoryInterface
{
    private const DEFINITIONS_TABLE = 'core.third_party_field_definitions';

    /** @var list<string> */
    private const SCOPES = ['customer', 'carrier', 'both'];

    /** @var list<string> */
    private const DATA_TYPES = ['text', 'number', 'date', 'boolean', 'select'];

    /** @return array<int, ThirdPartyFieldDefinition> */
    public function all(?string $scope, bool $activeOnly): array
    {
        $query = DB::connection('master_v3')->table(self::DEFINITIONS_TABLE)
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

        return array_map(fn (object $row): ThirdPartyFieldDefinition => $this->toDefinition($row), $query->get()->all());
    }

    public function create(array $attributes): ThirdPartyFieldDefinition
    {
        $data = $this->normalizeDefinition($attributes, true);
        $id = (string) Str::uuid();

        try {
            DB::connection('master_v3')->table(self::DEFINITIONS_TABLE)->insert([
                'tenant_id' => $attributes['tenant_id'],
                'id' => $id,
                ...$data,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $error) {
            if (str_contains(strtolower($error->getMessage()), 'third_party_field_definitions')) {
                throw new DomainException('Ya existe un campo de tercero con ese código.', 0, $error);
            }
            throw $error;
        }

        $definition = $this->definition((string) $id);
        if ($definition === null) {
            throw new DomainException('No se pudo guardar la definición del campo de tercero.');
        }
        $this->audit($attributes['tenant_id'], $attributes['actor_id'] ?? null, 'third_party.field_definition.created', 'third_party_field_definition', $id, [], $this->mapDefinition($definition));

        return $definition;
    }

    public function update(string $id, array $attributes): ?ThirdPartyFieldDefinition
    {
        $id = $this->requireUuid($id);
        $before = $this->definition($id);
        if ($before === null) {
            return null;
        }

        $data = $this->normalizeDefinition(array_merge($this->mapDefinition($before), $attributes), false);
        try {
            DB::connection('master_v3')->table(self::DEFINITIONS_TABLE)
                ->where('id', $id)
                ->update([...$data, 'updated_at' => now()]);
        } catch (\Throwable $error) {
            if (str_contains(strtolower($error->getMessage()), 'third_party_field_definitions')) {
                throw new DomainException('Ya existe un campo de tercero con ese código.', 0, $error);
            }
            throw $error;
        }

        return $this->definition($id);
    }

    public function deactivate(string $id): ?ThirdPartyFieldDefinition
    {
        $id = $this->requireUuid($id);
        $before = $this->definition($id);
        if ($before === null) {
            return null;
        }

        DB::connection('master_v3')->table(self::DEFINITIONS_TABLE)
            ->where('id', $id)
            ->update(['is_active' => false, 'updated_at' => now()]);

        return $this->definition($id);
    }

    private function definition(string $id): ?object
    {
        return DB::connection('master_v3')->table(self::DEFINITIONS_TABLE)
            ->where('id', $id)
            ->first();
    }

    private function toDefinition(object $row): ThirdPartyFieldDefinition
    {
        $validation = json_decode((string) ($row->validation ?? '{}'), true);

        return new ThirdPartyFieldDefinition(
            (string) $row->id,
            (string) $row->code,
            (string) $row->label,
            (string) $row->scope,
            (string) $row->data_type,
            is_array($validation) ? $validation : [],
            (int) $row->sort_order,
            (bool) $row->is_required,
            (bool) $row->is_active,
        );
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

    private function requireUuid(string $value): string
    {
        $value = strtolower(trim($value));
        if (Str::isUuid($value) !== true) {
            throw new DomainException('El identificador de ThirdParty debe ser un UUID.');
        }

        return $value;
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
