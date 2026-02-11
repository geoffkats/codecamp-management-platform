<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Add session validation middleware to web group
        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserExists::class,
        ]);
        
        // Add performance optimization middleware
        $middleware->append(\App\Http\Middleware\OptimizeResponse::class);
        $middleware->append(\App\Http\Middleware\PerformanceHeaders::class);
        
        // Register middleware aliases
        $middleware->alias([
            'student.profile' => \App\Http\Middleware\EnsureUserHasStudentProfile::class,
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'require.daily.report' => \App\Http\Middleware\RequireDailyReport::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
