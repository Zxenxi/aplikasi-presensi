<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

use App\Http\Controllers\Admin\DashboardController; // Tambahkan use statement

Route::middleware(['auth'])->group(function () {
    // ... (rute profil dari Breeze) ...

    // Grup untuk Admin & Petugas Piket
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:Super Admin,Petugas Piket']) // Middleware role
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
            // Rute admin lain akan ditambahkan di sini (users, classes, settings, reports)
        });

    // Rute dashboard default Breeze (mungkin perlu penyesuaian redirect)
    Route::get('/dashboard', function () {
        $user = auth()->user();
         if ($user->isSuperAdmin() || $user->isPetugasPiket()) {
            return redirect()->route('admin.dashboard');
        }
        // Arahkan Guru/Siswa ke tempat lain nanti
        return view('dashboard'); // View default Breeze
    })->name('dashboard');

}); // Akhir middleware auth

// Rute home ('/') - Arahkan ke login atau dashboard sesuai status auth
Route::get('/', function () {
    if (!auth()->check()) { return redirect()->route('login'); }
    $user = auth()->user();
    if ($user->isSuperAdmin() || $user->isPetugasPiket()) { return redirect()->route('admin.dashboard'); }
    // Arahkan Guru/Siswa nanti
    return redirect()->route('dashboard'); // Ke dashboard Breeze sementara
})->name('home');


use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;

// ... (use statement lain dan rute auth) ...

Route::middleware(['auth'])->group(function () {

    // ... (rute profil, dll) ...

    // --- Grup Rute Admin & Petugas Piket ---
    Route::prefix('admin')->name('admin.')->middleware(['role:Super Admin,Petugas Piket'])->group(function () {

        // ... (rute dashboard) ...

        // --- User Management (Hanya Super Admin) ---
        Route::resource('/users', AdminUserController::class)->middleware('role:Super Admin');

        // --- Class Management ---
        // Index bisa dilihat Admin & Piket (middleware grup sudah cukup)
        Route::get('/classes', [AdminKelasController::class, 'index'])->name('classes.index');
        // CRUD hanya untuk Super Admin
        Route::post('/classes', [AdminKelasController::class, 'store'])->name('classes.store')->middleware('role:Super Admin');
        Route::put('/classes/{kela}', [AdminKelasController::class, 'update'])->name('classes.update')->middleware('role:Super Admin');
        Route::delete('/classes/{kela}', [AdminKelasController::class, 'destroy'])->name('classes.destroy')->middleware('role:Super Admin');

        // ... (rute reports, settings nanti) ...

    }); // Akhir grup admin

    // ... (rute guru/siswa nanti) ...

}); // Akhir middleware auth

use App\Http\Controllers\Admin\SettingController as AdminSettingController; // Tambahkan use statement

// Di dalam Route::prefix('admin')->name('admin.')->middleware(['role:Super Admin,Petugas Piket'])->group(...)

    // ... (rute dashboard, users, classes) ...

    // --- Pengaturan Aplikasi (Hanya Super Admin) ---
    Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit')->middleware('role:Super Admin');
    Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update')->middleware('role:Super Admin');

    // ... (rute reports nanti) ...

    use App\Http\Controllers\AttendanceController; // Tambahkan use statement

    Route::middleware(['auth'])->group(function () {
    
        // ... (rute profil, dashboard default, grup admin) ...
    
        // --- Rute Presensi (Guru & Siswa) ---
        Route::prefix('presensi')->name('attendance.')->middleware('role:Guru,Siswa')->group(function () {
            // Halaman form presensi
            Route::get('/buat', [AttendanceController::class, 'create'])->name('create');
            // Proses simpan presensi
            Route::post('/', [AttendanceController::class, 'store'])->name('store');
            // Halaman riwayat presensi
            Route::get('/riwayat', [AttendanceController::class, 'index'])->name('history');
        });
    
    }); // Akhir middleware auth


    use App\Http\Controllers\Admin\ReportController as AdminReportController; // Tambahkan use statement

// Di dalam Route::prefix('admin')->name('admin.')->middleware(['role:Super Admin,Petugas Piket'])->group(...)

    // ... (rute dashboard, users, classes, settings) ...

    // --- Laporan Presensi (Admin & Petugas Piket) ---
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
require __DIR__.'/auth.php'; // Rute otentikasi Breeze