<?php

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
            'tenant' => \App\Http\Middleware\SetTenantConnection::class,
        ]);
        $middleware->redirectTo(fn (Request $request) => $request->expectsJson() ? null : '/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })
    ->withProviders([
        \App\Providers\ContextServiceProvider::class,
        \App\Context\V1\BranchOffices\Infrastructure\Laravel\Providers\BranchOfficeServiceProvider::class,
        \App\Context\V1\Clients\Infrastructure\Laravel\Providers\ClientServiceProvider::class,
        \App\Context\V1\EmissionPoints\Infrastructure\Laravel\Providers\EmissionPointServiceProvider::class,
        \App\Context\V1\Partners\Infrastructure\Laravel\Providers\PartnerServiceProvider::class,
    ])
    ->create();
