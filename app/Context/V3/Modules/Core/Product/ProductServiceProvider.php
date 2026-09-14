<?php

namespace App\Context\V3\Modules\Core\Product;

use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductRepositoryInterface;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductSettingsRepositoryInterface;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductTaxRepositoryInterface;
use App\Context\V3\Modules\Core\Product\Infrastructure\Postgres\ProductRepository;
use App\Context\V3\Modules\Core\Product\Infrastructure\Postgres\ProductSettingsRepository;
use App\Context\V3\Modules\Core\Product\Infrastructure\Postgres\ProductTaxRepository;
use Illuminate\Support\ServiceProvider;

final class ProductServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductTaxRepositoryInterface::class, ProductTaxRepository::class);
        $this->app->bind(ProductSettingsRepositoryInterface::class, ProductSettingsRepository::class);
    }
}
