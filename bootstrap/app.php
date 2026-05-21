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
        $middleware->redirectGuestsTo(fn () => route('auth.login'));

        $middleware->alias([
            'auth.email'       => \App\Http\Middleware\EnsureEmailVerified::class,
            'mfa'              => \App\Http\Middleware\RequireMfa::class,
            'resolve.tenant'   => \App\Http\Middleware\ResolveTenant::class,
            'tenant.can'       => \App\Http\Middleware\CheckTenantPermission::class,
            'plan.feature'     => \App\Http\Middleware\CheckPlanFeature::class,
            'plan.limit'       => \App\Http\Middleware\CheckPlanLimit::class,
            'admin'            => \App\Http\Middleware\EnsureSuperAdmin::class,
            'auth.admin'       => \App\Http\Middleware\EnsureAdminAuthenticated::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'billing/stripe/webhook',
            'billing/razorpay/webhook',
            'billing/paypal/webhook',
        ]);
    })
    ->withProviders([
        \App\Providers\EventServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
