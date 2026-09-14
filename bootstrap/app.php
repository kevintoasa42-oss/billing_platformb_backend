<?php

use App\Context\V1\Modules\BranchOffices\Infrastructure\Laravel\Providers\BranchOfficeServiceProvider;
use App\Context\V1\Modules\Clients\Infrastructure\Laravel\Providers\ClientServiceProvider;
use App\Context\V1\Modules\EmissionPoints\Infrastructure\Laravel\Providers\EmissionPointServiceProvider;
use App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Laravel\Providers\SriVoucherTypeServiceProvider;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Middleware\AuthenticateV3SessionCookie;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Providers\AuthenticationServiceProvider;
use App\Context\V3\Modules\Core\Carrier\CarrierServiceProvider;
use App\Context\V3\Modules\Core\Company\CompanyServiceProvider;
use App\Context\V3\Modules\Core\EconomicActivity\EconomicActivityServiceProvider;
use App\Context\V3\Modules\Core\Establishment\EstablishmentServiceProvider;
use App\Context\V3\Modules\Core\Notification\NotificationServiceProvider;
use App\Context\V3\Modules\Core\SriIva\SriIvaServiceProvider;
use App\Context\V3\Modules\Core\Product\ProductServiceProvider;
use App\Context\V3\Modules\Core\Settings\SettingsServiceProvider;
use App\Context\V3\Modules\Core\Vehicle\VehicleServiceProvider;
use App\Context\V3\Shared\Mail\MailServiceProvider;
use App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Http\Middleware\CaptureTenantContext;
use App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Http\Middleware\SetTenantConnection;
use App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Providers\TenantServiceProvider;
use App\Providers\ContextServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => SetTenantConnection::class,
            'auth.v3.cookie' => AuthenticateV3SessionCookie::class,
            'tenant.v3.context' => CaptureTenantContext::class,
        ]);
        $middleware->redirectTo(fn (Request $request) => $request->expectsJson() ? null : '/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->withProviders([
        ContextServiceProvider::class,
        BranchOfficeServiceProvider::class,
        ClientServiceProvider::class,
        EmissionPointServiceProvider::class,
        SriVoucherTypeServiceProvider::class,
        TenantServiceProvider::class,
        AuthenticationServiceProvider::class,
        CarrierServiceProvider::class,
        CompanyServiceProvider::class,
        EstablishmentServiceProvider::class,
        VehicleServiceProvider::class,
        EconomicActivityServiceProvider::class,
        MailServiceProvider::class,
        NotificationServiceProvider::class,
        SriIvaServiceProvider::class,
        ProductServiceProvider::class,
        SettingsServiceProvider::class,
    ])
    ->create();
