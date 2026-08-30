<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'checkstatus' => \App\Http\Middleware\CheckStatus::class,
            'admin'       => \App\Http\Middleware\Admin::class,
            'employee'    => \App\Http\Middleware\Employee::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'zoom/webhook',
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\TrackUserActivity::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('audit:prune')->dailyAt('02:00');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();