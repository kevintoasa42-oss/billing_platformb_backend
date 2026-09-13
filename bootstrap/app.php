<?php

use App\Context\V1\Modules\BranchOffices\Infrastructure\Laravel\Providers\BranchOfficeServiceProvider;
use App\Context\V1\Modules\Clients\Infrastructure\Laravel\Providers\ClientServiceProvider;
use App\Context\V1\Modules\EmissionPoints\Infrastructure\Laravel\Providers\EmissionPointServiceProvider;
use App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Laravel\Providers\SriVoucherTypeServiceProvider;
use App\Http\Middleware\SetTenantConnection;
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
    ])
    ->create();
