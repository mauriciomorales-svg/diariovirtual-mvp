<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \Intervention\Image\Laravel\ServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->prependToGroup('web', [
            \App\Http\Middleware\EnsureJsonForGeminiAdminEndpoints::class,
        ]);

        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($e instanceof AuthenticationException) {
                return null;
            }

            // Siempre JSON en estos GET (evita página HTML 500 y SyntaxError en el panel)
            if ($request->is('admin/gemini/health', 'admin/gemini/stats')) {
                $msg = config('app.debug') ? $e->getMessage() : 'Error del servidor';

                return $request->is('admin/gemini/health')
                    ? response()->json([
                        'success' => false,
                        'healthy' => false,
                        'error' => $msg,
                        'timestamp' => now()->toIso8601String(),
                    ], 500)
                    : response()->json([
                        'success' => false,
                        'error' => $msg,
                    ], 500);
            }

            if (! $request->is('admin/gemini/*')) {
                return null;
            }

            if (! $request->expectsJson() && $request->header('X-Requested-With') !== 'XMLHttpRequest') {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => config('app.debug') ? $e->getMessage() : 'Error del servidor',
            ], 500);
        });
    })->create();
