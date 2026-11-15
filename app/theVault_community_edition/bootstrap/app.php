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
        // Use the application's own subclasses (cookie/CSRF exceptions, trim exceptions).
        $middleware->web(replace: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class => \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class => \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \App\Http\Middleware\TrimStrings::class
        );

        // Proxies are intentionally not trusted by default (the old Fideloper TrustProxies was dropped).

        $middleware->alias([
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'verify_2fa' => \App\Http\Middleware\VerifyLogin::class,
            'is_vendor' => \App\Http\Middleware\IsVendor::class,
            'is_admin' => \App\Http\Middleware\IsAdmin::class,
            'is_banned' => \App\Http\Middleware\IsBanned::class,
            'admin_panel_access' => \App\Http\Middleware\HasAdminPanelAccess::class,
            'can_read_messages' => \App\Http\Middleware\CanReadMessages::class,
            'can_edit_products' => \App\Http\Middleware\CanEditProducts::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
