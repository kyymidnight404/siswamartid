<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Konfigurasi trusted proxy (mendukung environment TRUSTED_PROXIES untuk Cloudflare/reverse proxy)
        $trustedProxies = env('TRUSTED_PROXIES', '*');
        if ($trustedProxies === '*' || empty($trustedProxies)) {
            $middleware->trustProxies(at: '*');
        } else {
            $proxies = array_map('trim', explode(',', (string) $trustedProxies));
            $middleware->trustProxies(at: $proxies);
        }

        // Daftarkan middleware role dan status aktif sebagai alias
        $middleware->alias([
            'admin'   => \App\Http\Middleware\EnsureAdmin::class,
            'penjual' => \App\Http\Middleware\EnsurePenjual::class,
            'active'  => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Redirect user yang sudah login bila mengakses route guest (spt /login)
        $middleware->redirectUsersTo(function (Request $request) {
            if (auth()->check()) {
                return auth()->user()->role === 'admin' 
                    ? route('admin.dashboard') 
                    : route('penjual.dashboard');
            }
            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (ValidationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data yang dikirim tidak valid.',
                    'errors' => $exception->errors(),
                ], 422);
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
