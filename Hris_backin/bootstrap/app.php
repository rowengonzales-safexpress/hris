<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\HandleInertiaRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            Route::middleware('web')
                ->group(base_path('routes/tracking.php'));
            Route::middleware('web')
                ->group(base_path('routes/frms.php'));
            Route::middleware('web')
                ->group(base_path('routes/sqdcm.php'));
            Route::middleware('web')
                ->group(base_path('routes/weeklytask.php'));

        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
           // \App\Http\Middleware\ShareUserMenus::class,
            \App\Http\Middleware\SessionTimeout::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'tracking' => \App\Http\Middleware\TrakingMiddleware::class,
            'check.app.user' => \App\Http\Middleware\CheckAppUserStatus::class,
            'frms' => \App\Http\Middleware\FrmsMiddleware::class,
            'sqdcm' => \App\Http\Middleware\SqdcmMiddleware::class,
            'weeklytask' => \App\Http\Middleware\WeeklyTaskMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function ($response) {
            $status = $response->getStatusCode();
            if (in_array($status, [401, 403, 404, 419, 429, 500, 503]) && request()->isMethod('GET')) {
                return \Inertia\Inertia::render('Error', ['status' => $status])
                    ->toResponse(request())
                    ->setStatusCode($status);
            }
            return $response;
        });
    })->create();
