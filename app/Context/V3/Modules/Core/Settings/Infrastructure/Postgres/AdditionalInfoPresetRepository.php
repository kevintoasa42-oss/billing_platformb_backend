<?php

namespace App\Context\V3\Modules\Core\Settings\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Settings\Domain\Models\AdditionalInfoPreset;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\AdditionalInfoPresetRepositoryInterface;
use App\Context\V3\Modules\Core\Settings\Infrastructure\Laravel\Eloquent\Models\AdditionalInfoPresetModel;
use Illuminate\Support\Str;

class AdditionalInfoPresetRepository implements AdditionalInfoPresetRepositoryInterface
{
    public function all(bool $availableOnly = false): array
    {
        $query = AdditionalInfoPresetModel::query()
            ->orderBy('sort_order')
            ->orderBy('legacy_id');

        if ($availableOnly) {
            $query->where('is_active', true);
        }

        return $query->get()
            ->map(fn (AdditionalInfoPresetModel $record): AdditionalInfoPreset => $this->toDomain($record))
            ->all();
    }

    public function create(array $data): AdditionalInfoPreset
    {
        $record = AdditionalInfoPresetModel::query()->create([
            'id' => Str::uuid()->toString(),
            'code' => trim((string) ($data['code'] ?? Str::upper(Str::random(8)))),
            'name' => trim((string) ($data['name'] ?? 'Dato adicional')),
            'default_value' => $data['default_value'] ?? null,
            'auto_apply' => (bool) ($data['auto_apply'] ?? false),
            'value_editable' => (bool) ($data['value_editable'] ?? true),
            'is_required' => (bool) ($data['is_required'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'access_rules' => $data['access_rules'] ?? [],
        ]);

        $record->refresh();

        return $this->toDomain($record);
    }

    public function update(int $legacyId, array $data): ?AdditionalInfoPreset
    {
        $record = AdditionalInfoPresetModel::query()->where('legacy_id', $legacyId)->first();

        if ($record === null) {
            return null;
        }

        $allowed = ['code', 'name', 'default_value', 'auto_apply', 'value_editable', 'is_required', 'is_active', 'sort_order'];
        $values = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $values[$key] = $data[$key];
            }
        }
        if (array_key_exists('access_rules', $data)) {
            $values['access_rules'] = $data['access_rules'];
        }

        if ($values !== []) {
            $record->update($values);
            $record->refresh();
        }

        return $this->toDomain($record);
    }

    public function delete(int $legacyId): bool
    {
        return (bool) AdditionalInfoPresetModel::query()
            ->where('legacy_id', $legacyId)
            ->update(['is_active' => false]);
    }

    private function toDomain(AdditionalInfoPresetModel $record): AdditionalInfoPreset
    {
        return new AdditionalInfoPreset(
            id: (int) $record->legacy_id,
            code: (string) $record->code,
            name: (string) $record->name,
            defaultValue: $record->default_value,
            autoApply: (bool) $record->auto_apply,
            valueEditable: (bool) $record->value_editable,
            isRequired: (bool) $record->is_required,
            isActive: (bool) $record->is_active,
            sortOrder: (int) $record->sort_order,
            accessRules: $record->access_rules ?? [],
        );
    }
}
