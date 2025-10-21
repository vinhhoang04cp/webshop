<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            // Middleware tổng hợp cho role và permission - thay thế 4 middleware cũ
            'role.permission' => \App\Http\Middleware\RolePermissionMiddleware::class,

            // Giữ lại alias cũ để tương thích ngược (backward compatibility)
            'admin' => \App\Http\Middleware\RolePermissionMiddleware::class,
            'role' => \App\Http\Middleware\RolePermissionMiddleware::class,
            'permission' => \App\Http\Middleware\RolePermissionMiddleware::class,

            // Firebase Authentication Middleware
            'firebase.auth' => \App\Http\Middleware\FirebaseAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
