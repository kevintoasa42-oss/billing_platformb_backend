<?php

namespace App\Context\V3\Modules\Core\Product\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Product\Domain\Models\ProductSettings;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductSettingsRepositoryInterface;
use App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models\TenantSettingsModel;
use Illuminate\Support\Facades\DB;

class ProductSettingsRepository implements ProductSettingsRepositoryInterface
{
    public function get(): ProductSettings
    {
        $record = TenantSettingsModel::query()->first();

        if ($record === null) {
            return ProductSettings::fromArray(null);
        }

        $settings = $record->product_settings;

        return ProductSettings::fromArray(is_array($settings) ? $settings : []);
    }

    public function save(ProductSettings $settings): ProductSettings
    {
        $data = $settings->toArray();

        DB::connection('master_v3')->transaction(function () use ($data): void {
            $existing = TenantSettingsModel::query()->first();

            if ($existing === null) {
                TenantSettingsModel::query()->create([
                    'product_settings' => $data,
                ]);
            } else {
                $existing->update(['product_settings' => $data]);
            }
        });

        return $settings;
    }
}
