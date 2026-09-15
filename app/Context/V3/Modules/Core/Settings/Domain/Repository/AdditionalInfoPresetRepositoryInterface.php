<?php

namespace App\Context\V3\Modules\Core\Settings\Domain\Repository;

use App\Context\V3\Modules\Core\Settings\Domain\Models\AdditionalInfoPreset;

interface AdditionalInfoPresetRepositoryInterface
{
    /**
     * @return AdditionalInfoPreset[]
     */
    public function all(bool $availableOnly = false): array;

    public function create(array $data): AdditionalInfoPreset;

    public function update(int $legacyId, array $data): ?AdditionalInfoPreset;

    public function delete(int $legacyId): bool;
}
