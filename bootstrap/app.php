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
    // ->withMiddleware(function (Middleware $middleware) {
    //     $middleware->alias([
    //         'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    //     ]);
    // })

    ->withMiddleware(function (Middleware $middleware) { 
        //
        $middleware->alias(         [
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            // ... middleware bawaan lainnya ...
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    
            //------------------------------------
            'role' => \App\Http\Middleware\EnsureUserHasRole::class, // <-- Tambahkan baris ini
            //------------------------------------
    
            // ... middleware custom lainnya jika ada ...
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
    