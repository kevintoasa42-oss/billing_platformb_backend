<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Repository;

use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierAffiliation;

interface CarrierAffiliationRepositoryInterface
{
    /**
     * @return array<int, CarrierAffiliation>
     */
    public function all(): array;

    public function find(string $id): ?CarrierAffiliation;

    public function create(CarrierAffiliation $affiliation): CarrierAffiliation;

    public function update(string $id, CarrierAffiliation $affiliation): ?CarrierAffiliation;

    public function findByThirdPartyId(string $thirdPartyId): ?CarrierAffiliation;

    /**
     * @param  array<int, string>  $thirdPartyIds
     * @return array<string, array<int, string>> Mapa de third_party_id => [plates]
     */
    public function platesByThirdPartyIds(array $thirdPartyIds): array;
}
