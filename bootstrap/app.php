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
            'course.catalog' => \App\Http\Middleware\EnsureCourseCatalogAccess::class,
            'code_club.enabled' => \App\Http\Middleware\EnsureCodeClubEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('livewire/*') || $request->header('X-Livewire')) {
                return response()->json(['message' => 'Session expired'], 419);
            }

            return redirect('/login');
        });
    })->create();
