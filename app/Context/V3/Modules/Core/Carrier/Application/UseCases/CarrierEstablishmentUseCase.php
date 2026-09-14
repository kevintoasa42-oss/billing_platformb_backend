<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\UseCases;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierEstablishmentModel as CarrierEstablishmentEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Application\DTOs\CarrierEstablishmentCreateDTO;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEstablishment;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierEstablishmentRepositoryInterface;

class CarrierEstablishmentUseCase
{
    public function __construct(
        private readonly CarrierEstablishmentRepositoryInterface $repository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $records = CarrierEstablishmentEloquentModel::query()
            ->with(['carrierCompany.thirdParty', 'carrierCompany.activities.economicActivity'])
            ->orderBy('name')
            ->get();

        return $records->map(fn ($r) => $this->enrich($r))->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        $record = CarrierEstablishmentEloquentModel::query()
            ->with(['carrierCompany.thirdParty', 'carrierCompany.activities.economicActivity'])
            ->find($id);

        return $record !== null ? $this->enrich($record) : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function create(CarrierEstablishmentCreateDTO $dto): array
    {
        $establishment = CarrierEstablishment::fromArray($dto->toArray());

        $created = $this->repository->create($establishment);

        // Reload with relations for enriched response
        $record = CarrierEstablishmentEloquentModel::query()
            ->with(['carrierCompany.thirdParty', 'carrierCompany.activities.economicActivity'])
            ->find($created->id);

        return $record !== null ? $this->enrich($record) : $created->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function enrich(CarrierEstablishmentEloquentModel $record): array
    {
        $carrierCompany = null;
        if ($record->carrierCompany !== null) {
            $cc = $record->carrierCompany;
            $carrierCompany = [
                'id' => $cc->id,
                'legal_name' => $cc->legal_name,
                'trade_name' => $cc->trade_name,
                'is_active' => $cc->is_active,
                'third_party_name' => $cc->thirdParty?->name,
                'third_party_identification' => $cc->thirdParty?->identification,
            ];
        }

        $activities = [];
        if ($record->carrierCompany !== null && $record->carrierCompany->relationLoaded('activities')) {
            foreach ($record->carrierCompany->activities as $activity) {
                $activities[] = [
                    'activity_id' => $activity->activity_id,
                    'name' => $activity->economicActivity?->name,
                    'is_primary' => $activity->is_primary,
                ];
            }
        }

        return [
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'carrier_company_id' => $record->carrier_company_id,
            'carrier_company' => $carrierCompany,
            'sri_code' => $record->sri_code,
            'name' => $record->name,
            'address' => $record->address,
            'phone' => $record->phone,
            'email' => $record->email,
            'city_id' => $record->city_id,
            'is_active' => $record->is_active,
            'activities' => $activities,
        ];
    }
}
