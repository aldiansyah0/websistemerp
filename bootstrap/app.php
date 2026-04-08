<?php

use App\Services\EnsurePermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => EnsurePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $throwable): void {
            Log::channel('alerts')->error('Unhandled application exception', [
                'message' => $throwable->getMessage(),
                'exception' => get_class($throwable),
            ]);

            if (config('services.sentry.dsn') && app()->bound('sentry')) {
                app('sentry')->captureException($throwable);
            }
        });
    })->create();
