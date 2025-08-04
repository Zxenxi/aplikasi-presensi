<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
public function boot(): void
{
    Paginator::useTailwind();
    // ... (kode rate limiter jika ada)

    Route::middleware('api') // Middleware default untuk grup API
        ->prefix('api')     // INI YANG PENTING: Prefix 'api' ditambahkan di sini
        ->group(base_path('routes/api.php'));

    Route::middleware('web')
        ->group(base_path('routes/web.php'));
}
}