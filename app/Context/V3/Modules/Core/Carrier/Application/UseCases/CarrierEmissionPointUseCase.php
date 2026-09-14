<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\UseCases;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierEmissionPointModel as CarrierEmissionPointEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Application\DTOs\CarrierEmissionPointCreateDTO;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEmissionPoint;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierEmissionPointRepositoryInterface;

class CarrierEmissionPointUseCase
{
    public function __construct(
        private readonly CarrierEmissionPointRepositoryInterface $repository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $records = CarrierEmissionPointEloquentModel::query()
            ->with(['establishment.carrierCompany.thirdParty'])
            ->orderBy('name')
            ->get();

        return $records->map(fn ($r) => $this->enrich($r))->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        $record = CarrierEmissionPointEloquentModel::query()
            ->with(['establishment.carrierCompany.thirdParty'])
            ->find($id);

        return $record !== null ? $this->enrich($record) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function byEstablishment(string $establishmentId): array
    {
        $records = CarrierEmissionPointEloquentModel::query()
            ->with(['establishment.carrierCompany.thirdParty'])
            ->where('establishment_id', $establishmentId)
            ->orderBy('name')
            ->get();

        return $records->map(fn ($r) => $this->enrich($r))->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function create(CarrierEmissionPointCreateDTO $dto): array
    {
        $emissionPoint = CarrierEmissionPoint::fromArray($dto->toArray());

        $created = $this->repository->create($emissionPoint);

        $record = CarrierEmissionPointEloquentModel::query()
            ->with(['establishment.carrierCompany.thirdParty'])
            ->find($created->id);

        return $record !== null ? $this->enrich($record) : $created->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function enrich(CarrierEmissionPointEloquentModel $record): array
    {
        $establishment = null;
        if ($record->establishment !== null) {
            $est = $record->establishment;
            $establishment = [
                'id' => $est->id,
                'name' => $est->name,
                'sri_code' => $est->sri_code,
                'carrier_company_id' => $est->carrier_company_id,
                'carrier_company_name' => $est->carrierCompany?->legal_name,
            ];
        }

        return [
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'establishment_id' => $record->establishment_id,
            'establishment' => $establishment,
            'sri_code' => $record->sri_code,
            'name' => $record->name,
            'next_sequential' => $record->next_sequential,
            'is_active' => $record->is_active,
        ];
    }
}
