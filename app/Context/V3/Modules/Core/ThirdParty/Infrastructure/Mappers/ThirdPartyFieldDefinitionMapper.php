<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyFieldDefinition;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyFieldDefinitionModel;
use Illuminate\Support\Collection;

final class ThirdPartyFieldDefinitionMapper
{
    public function toDomain(ThirdPartyFieldDefinitionModel $model): ThirdPartyFieldDefinition
    {
        return new ThirdPartyFieldDefinition(
            id: (string) $model->getKey(),
            code: (string) $model->getAttribute('code'),
            label: (string) $model->getAttribute('label'),
            scope: (string) $model->getAttribute('scope'),
            dataType: (string) $model->getAttribute('data_type'),
            validation: (array) ($model->getAttribute('validation') ?? []),
            sortOrder: (int) $model->getAttribute('sort_order'),
            isRequired: (bool) $model->getAttribute('is_required'),
            isActive: (bool) $model->getAttribute('is_active'),
        );
    }

    /** @return list<ThirdPartyFieldDefinition> */
    public function toDomainList(Collection $models): array
    {
        return $models
            ->map(fn (ThirdPartyFieldDefinitionModel $model): ThirdPartyFieldDefinition => $this->toDomain($model))
            ->values()
            ->all();
    }
}
