<?php

namespace App\Context\V3\Modules\Core\Product\Domain\Repository;

use App\Context\V3\Modules\Core\Product\Domain\Models\ProductSettings;

interface ProductSettingsRepositoryInterface
{
    public function get(): ProductSettings;

    public function save(ProductSettings $settings): ProductSettings;
}
