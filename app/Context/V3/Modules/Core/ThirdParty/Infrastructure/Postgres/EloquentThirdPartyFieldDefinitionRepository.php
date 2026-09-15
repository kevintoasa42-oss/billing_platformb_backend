<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyFieldDefinition;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldDefinitionRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyFieldDefinitionModel;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers\ThirdPartyFieldDefinitionMapper;

final readonly class EloquentThirdPartyFieldDefinitionRepository implements ThirdPartyFieldDefinitionRepositoryInterface
{
    public function __construct(
        private ThirdPartyFieldDefinitionMapper $mapper,
    ) {}

    public function all(?string $scope, bool $activeOnly): array
    {
        $query = ThirdPartyFieldDefinitionModel::query()
            ->orderBy('sort_order')
            ->orderBy('label');

        if ($scope !== null && $scope !== '' && $scope !== 'both') {
            $query->whereIn('scope', [$scope, 'both']);
        }

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $this->mapper->toDomainList($query->get());
    }

    public function create(array $attributes): ThirdPartyFieldDefinition
    {
        $model = ThirdPartyFieldDefinitionModel::query()->create($attributes);

        return $this->mapper->toDomain($model->refresh());
    }

    public function update(string $id, array $attributes): ?ThirdPartyFieldDefinition
    {
        $model = ThirdPartyFieldDefinitionModel::query()->find($id);

        if ($model === null) {
            return null;
        }

        $model->fill($attributes);
        $model->save();

        return $this->mapper->toDomain($model->refresh());
    }

    public function deactivate(string $id): ?ThirdPartyFieldDefinition
    {
        return $this->update($id, ['is_active' => false]);
    }
}
