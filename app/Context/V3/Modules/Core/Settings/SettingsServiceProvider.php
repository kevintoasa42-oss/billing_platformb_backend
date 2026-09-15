<?php

namespace App\Context\V3\Modules\Core\Settings;

use App\Context\V3\Modules\Core\Settings\Domain\Repository\AdditionalInfoPresetRepositoryInterface;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\CustomerSettingsRepositoryInterface;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\PaymentMethodSettingsRepositoryInterface;
use App\Context\V3\Modules\Core\Settings\Infrastructure\Postgres\AdditionalInfoPresetRepository;
use App\Context\V3\Modules\Core\Settings\Infrastructure\Postgres\CustomerSettingsRepository;
use App\Context\V3\Modules\Core\Settings\Infrastructure\Postgres\PaymentMethodSettingsRepository;
use Illuminate\Support\ServiceProvider;

final class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomerSettingsRepositoryInterface::class, CustomerSettingsRepository::class);
        $this->app->bind(PaymentMethodSettingsRepositoryInterface::class, PaymentMethodSettingsRepository::class);
        $this->app->bind(AdditionalInfoPresetRepositoryInterface::class, AdditionalInfoPresetRepository::class);
    }
}
