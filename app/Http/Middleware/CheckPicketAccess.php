<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPicketAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // app/Http/Kernel.php

    protected $routeMiddleware = [
        // ... middleware lain yang sudah ada ...
        // 'role' => \Spatie\Permission\Middlewares\RoleMiddleware::class, // (Contoh jika Anda pakai Spatie, atau middleware lain)
        'picket.access' => \App\Http\Middleware\CheckPicketAccess::class, 
    ];

    public function handle(Request $request, Closure $next): Response
    {
        
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Izinkan akses jika user adalah Super Admin ATAU petugas piket (sesuai logika baru kita).
        if ($user && ($user->isSuperAdmin() || $user->isPetugasPiket())) {
            return $next($request);
        }

        // Jika tidak, tolak akses.
        abort(403, 'ANDA TIDAK MEMILIKI AKSES.');
    }
}