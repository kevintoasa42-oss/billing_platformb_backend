<?php

namespace App\Context\V3\Modules\Core\Establishment\Domain\Repository;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\EmissionPoint;

interface EmissionPointRepositoryInterface
{
    public function all(): array;

    public function find(string $id): ?EmissionPoint;

    public function byEstablishment(string $establishmentId): array;

    public function create(EmissionPoint $emissionPoint): EmissionPoint;

    public function update(string $id, EmissionPoint $emissionPoint): ?EmissionPoint;
}
