<?php

namespace App\Context\V3\Modules\Core\Settings\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Settings\Domain\Models\AdditionalInfoPreset;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\AdditionalInfoPresetRepositoryInterface;
use App\Context\V3\Modules\Core\Settings\Infrastructure\Laravel\Eloquent\Models\AdditionalInfoPresetModel;
use App\Context\V3\Modules\Core\Settings\Infrastructure\Mappers\AdditionalInfoPresetMapper;
use Illuminate\Support\Str;

class AdditionalInfoPresetRepository implements AdditionalInfoPresetRepositoryInterface
{
    public function __construct(
        private readonly AdditionalInfoPresetMapper $mapper,
    ) {}

    public function all(bool $availableOnly = false): array
    {
        $query = AdditionalInfoPresetModel::query()
            ->orderBy('sort_order')
            ->orderBy('legacy_id');

        if ($availableOnly) {
            $query->where('is_active', true);
        }

        return $this->mapper->toDomainList($query->get());
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

        return $this->mapper->toDomain($record->refresh());
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

        return $this->mapper->toDomain($record);
    }

    public function delete(int $legacyId): bool
    {
        return (bool) AdditionalInfoPresetModel::query()
            ->where('legacy_id', $legacyId)
            ->update(['is_active' => false]);
    }
}
