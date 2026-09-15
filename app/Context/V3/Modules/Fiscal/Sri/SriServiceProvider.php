<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri;

use App\Context\V3\Modules\Fiscal\Sri\Domain\Ports\DocumentKeyCustodianInterface;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Ports\SriSoapClientInterface;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Ports\XmlSigningServiceInterface;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Adapters\OpenBaoTransitSigningClient;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services\LegacyP12XmlSigner;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services\SriCredentialSigningService;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services\SriSoapClient;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services\SriXmlSigner;
use Illuminate\Support\ServiceProvider;

final class SriServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DocumentKeyCustodianInterface::class, OpenBaoTransitSigningClient::class);
        $this->app->bind(SriSoapClientInterface::class, SriSoapClient::class);

        // The XmlSigningServiceInterface is bound to SriXmlSigner (OpenBao path).
        // SriCredentialSigningService wraps both OpenBao and legacy P12.
        $this->app->bind(XmlSigningServiceInterface::class, SriXmlSigner::class);

        $this->app->extend(SriCredentialSigningService::class, function ($service, $app) {
            return $service;
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(resource_path('views/sri'), 'sri');
    }
}
