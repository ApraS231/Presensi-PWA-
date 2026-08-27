<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);

        $middleware->alias([
            'active'          => \App\Http\Middleware\CheckActive::class,
            'enrolled'        => \App\Http\Middleware\CheckEnrolled::class,
            'role.karyawan'   => \App\Http\Middleware\RoleKaryawan::class,
            'role.admin'      => \App\Http\Middleware\RoleAdmin::class,
            'role.superadmin' => \App\Http\Middleware\RoleSuperadmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi Anda telah berakhir. Silakan muat ulang halaman atau login kembali.',
                ], 419);
            }

            return redirect()->route('login')->with('error', 'Sesi Anda telah kedaluwarsa. Silakan masuk kembali.');
        });
    })->create();
