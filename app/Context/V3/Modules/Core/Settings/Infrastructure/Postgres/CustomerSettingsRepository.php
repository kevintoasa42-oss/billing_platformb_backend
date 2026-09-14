<?php

namespace App\Context\V3\Modules\Core\Settings\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Settings\Domain\Models\CustomerSettings;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\CustomerSettingsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CustomerSettingsRepository implements CustomerSettingsRepositoryInterface
{
    public function get(): CustomerSettings
    {
        $row = DB::connection('master_v3')
            ->table('core.tenant_settings')
            ->first();

        $settings = [];
        if ($row !== null && $row->customer_settings !== null) {
            $settings = is_array($row->customer_settings)
                ? $row->customer_settings
                : (json_decode((string) $row->customer_settings, true) ?: []);
        }

        return CustomerSettings::fromArray(['allow_multiple_plates' => $settings['allow_multiple_plates'] ?? false]);
    }

    public function save(CustomerSettings $settings): CustomerSettings
    {
        DB::connection('master_v3')->transaction(function () use ($settings): void {
            $row = DB::connection('master_v3')
                ->table('core.tenant_settings')
                ->first();

            $current = [];
            if ($row !== null && $row->customer_settings !== null) {
                $current = is_array($row->customer_settings)
                    ? $row->customer_settings
                    : (json_decode((string) $row->customer_settings, true) ?: []);
            }

            $merged = $current;
            if ($settings->allowMultiplePlates !== null) {
                $merged['allow_multiple_plates'] = $settings->allowMultiplePlates;
            }

            if ($row === null) {
                DB::connection('master_v3')->table('core.tenant_settings')->insert([
                    'customer_settings' => json_encode($merged, JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
            } else {
                DB::connection('master_v3')->table('core.tenant_settings')
                    ->where('tenant_id', $row->tenant_id)
                    ->update([
                        'customer_settings' => json_encode($merged, JSON_THROW_ON_ERROR),
                        'updated_at' => now(),
                    ]);
            }
        });

        return $this->get();
    }
}
