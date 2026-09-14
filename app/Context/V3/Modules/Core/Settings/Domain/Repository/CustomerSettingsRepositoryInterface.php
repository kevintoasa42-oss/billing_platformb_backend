<?php

namespace App\Context\V3\Modules\Core\Settings\Domain\Repository;

use App\Context\V3\Modules\Core\Settings\Domain\Models\CustomerSettings;

interface CustomerSettingsRepositoryInterface
{
    public function get(): CustomerSettings;

    public function save(CustomerSettings $settings): CustomerSettings;
}
