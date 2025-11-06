<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust proxies - IMPORTANT for HTTPS detection behind nginx/cloudflare
        $middleware->trustProxies(at: '*');

        // Thêm global middleware để sanitize input và security headers
        $middleware->append(\App\Http\Middleware\SanitizeInputMiddleware::class);
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
        $middleware->append(\App\Http\Middleware\DetectSuspiciousActivityMiddleware::class);
        $middleware->append(\App\Http\Middleware\ForceHttpsMiddleware::class);

        // Session security cho web routes
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\SessionSecurityMiddleware::class,
        ]);

        $middleware->alias([
            // Middleware tổng hợp cho role và permission - thay thế 4 middleware cũ
            'role.permission' => \App\Http\Middleware\RolePermissionMiddleware::class,

            // Giữ lại alias cũ để tương thích ngược (backward compatibility)
            'admin' => \App\Http\Middleware\RolePermissionMiddleware::class,
            'role' => \App\Http\Middleware\RolePermissionMiddleware::class,
            'permission' => \App\Http\Middleware\RolePermissionMiddleware::class,

            // Middleware bảo mật
            'sanitize' => \App\Http\Middleware\SanitizeInputMiddleware::class,
            'security.headers' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
            'login.attempts' => \App\Http\Middleware\LoginAttemptMiddleware::class,
            'token.expiration' => \App\Http\Middleware\CheckTokenExpirationMiddleware::class,
            'detect.suspicious' => \App\Http\Middleware\DetectSuspiciousActivityMiddleware::class,
            'session.security' => \App\Http\Middleware\SessionSecurityMiddleware::class,
            'force.https' => \App\Http\Middleware\ForceHttpsMiddleware::class,
            'cors.security' => \App\Http\Middleware\CorsSecurityMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
