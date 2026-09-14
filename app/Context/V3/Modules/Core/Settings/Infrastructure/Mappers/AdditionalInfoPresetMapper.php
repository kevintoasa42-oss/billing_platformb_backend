<?php

namespace App\Context\V3\Modules\Core\Settings\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Settings\Domain\Models\AdditionalInfoPreset;
use App\Context\V3\Modules\Core\Settings\Infrastructure\Laravel\Eloquent\Models\AdditionalInfoPresetModel;

class AdditionalInfoPresetMapper
{
    public function toDomain(AdditionalInfoPresetModel $record): AdditionalInfoPreset
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

    /**
     * @param  iterable<int, AdditionalInfoPresetModel>  $records
     * @return array<int, AdditionalInfoPreset>
     */
    public function toDomainList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toDomain($record);
        }

        return $list;
    }
}
