<?php

namespace App\Context\V3\Modules\Core\Settings\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Settings\Domain\Models\CustomerSettings;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\CustomerSettingsRepositoryInterface;
use App\Context\V3\Modules\Core\Settings\Infrastructure\Laravel\Eloquent\Models\TenantSettingsModel;

class CustomerSettingsRepository implements CustomerSettingsRepositoryInterface
{
    public function get(): CustomerSettings
    {
        $row = TenantSettingsModel::query()->first();

        $settings = $row?->customer_settings ?? [];

        return CustomerSettings::fromArray([
            'allow_multiple_plates' => $settings['allow_multiple_plates'] ?? false,
        ]);
    }

    public function save(CustomerSettings $settings): CustomerSettings
    {
        $row = TenantSettingsModel::query()->first();

        $current = $row?->customer_settings ?? [];

        $merged = $current;
        if ($settings->allowMultiplePlates !== null) {
            $merged['allow_multiple_plates'] = $settings->allowMultiplePlates;
        }

        if ($row === null) {
            TenantSettingsModel::query()->create([
                'customer_settings' => $merged,
            ]);
        } else {
            $row->update(['customer_settings' => $merged]);
        }

        return $this->get();
    }
}
