<?php

namespace App\Context\V3\Modules\Core\Vehicle\Domain\Repository;

use App\Context\V3\Modules\Core\Vehicle\Domain\Models\Vehicle;

interface VehicleRepositoryInterface
{
    public function all(): array;

    public function find(string $id): ?Vehicle;

    public function create(Vehicle $vehicle): Vehicle;

    public function update(string $id, Vehicle $vehicle): ?Vehicle;
}
