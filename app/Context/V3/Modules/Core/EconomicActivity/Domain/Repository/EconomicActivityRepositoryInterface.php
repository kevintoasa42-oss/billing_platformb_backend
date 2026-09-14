<?php

namespace App\Context\V3\Modules\Core\EconomicActivity\Domain\Repository;

use App\Context\V3\Modules\Core\EconomicActivity\Domain\Models\EconomicActivity;

interface EconomicActivityRepositoryInterface
{
    public function all(): array;

    public function find(string $id): ?EconomicActivity;

    public function create(EconomicActivity $activity): EconomicActivity;

    public function update(string $id, EconomicActivity $activity): ?EconomicActivity;
}
