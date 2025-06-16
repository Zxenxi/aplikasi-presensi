<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Kelas; // Pastikan ini di-import
use App\Models\User;  // Pastikan ini di-import

// Rute default Sanctum (jika Anda menggunakannya)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rute untuk mengambil siswa per kelas
// Kita akan letakkan ini di bawah prefix 'api' secara manual jika RouteServiceProvider tidak melakukannya
Route::middleware(['web', 'auth', 'role:Super Admin']) // GANTI SEMENTARA KE 'web' & 'auth' untuk tes dari Blade
    ->prefix('admin/kelas') // Hanya prefix 'admin/kelas' di sini
    ->name('api.admin.kelas.') // Beri nama agar konsisten
    ->group(function () {
        Route::get('/{kelas}/students', function (Kelas $kelas) {
            return $kelas->students()
                          ->where('role', 'Siswa')
                          ->where('is_active', true)
                          ->orderBy('name')
                          ->get(['id', 'name', 'email']);
        })->name('students'); // Nama rutenya akan jadi 'api.admin.kelas.students'
    });