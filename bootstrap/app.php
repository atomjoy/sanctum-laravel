<?php

use App\Http\Middleware\Sanctum\SanctumAdmin;
use App\Http\Middleware\Sanctum\SanctumWeb;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sanctum SPA
        $middleware->statefulApi();
        // Import
        $middleware->alias([
            // Sanctum multi guards
            'sanctum_web' => SanctumWeb::class,
            'sanctum_admin' => SanctumAdmin::class,

            // Sanctum abilities
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
        // Sanctum API
        $middleware->api(prepend: [
            \App\Http\Middleware\Sanctum\ExpiredToken::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
