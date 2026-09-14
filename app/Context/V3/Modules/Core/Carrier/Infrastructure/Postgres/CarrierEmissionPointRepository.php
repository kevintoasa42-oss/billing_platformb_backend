<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierEmissionPointModel as CarrierEmissionPointEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEmissionPoint;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierEmissionPointRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers\CarrierEmissionPointMapper;

class CarrierEmissionPointRepository implements CarrierEmissionPointRepositoryInterface
{
    public function __construct(
        private readonly CarrierEmissionPointMapper $mapper,
    ) {}

    public function all(): array
    {
        $records = CarrierEmissionPointEloquentModel::query()
            ->with(['establishment.carrierCompany.thirdParty'])
            ->orderBy('name')
            ->get();

        return $this->mapper->toResponseArrayList($records);
    }

    public function find(string $id): ?array
    {
        $record = CarrierEmissionPointEloquentModel::query()
            ->with(['establishment.carrierCompany.thirdParty'])
            ->find($id);

        return $record !== null ? $this->mapper->toResponseArray($record) : null;
    }

    public function byEstablishment(string $establishmentId): array
    {
        $records = CarrierEmissionPointEloquentModel::query()
            ->with(['establishment.carrierCompany.thirdParty'])
            ->where('establishment_id', $establishmentId)
            ->orderBy('name')
            ->get();

        return $this->mapper->toResponseArrayList($records);
    }

    public function create(CarrierEmissionPoint $emissionPoint): array
    {
        $record = CarrierEmissionPointEloquentModel::query()->create(
            $this->mapper->toDatabaseArray($emissionPoint)
        );

        return $this->mapper->toResponseArray($record->load(['establishment.carrierCompany.thirdParty']));
    }
}
