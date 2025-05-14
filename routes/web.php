<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController; // User Attendance
// Controller Admin
use App\Http\Controllers\Admin\PicketScheduleController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController; // Admin Attendance

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// ... (kode awal, redirect home, route dashboard default, route profile) ...
// Rute halaman utama ('/')
Route::get('/', function () {
    // Gunakan Auth::check()
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    /** @var \App\Models\User|null $user */ // <-- Tambahkan hint di sini (tambahkan |null karena user bisa null jika check gagal, meskipun kita sudah cek)
    $user = Auth::user(); // <-- Pastikan menggunakan Auth::user() atau auth()->user()

    // Cek null untuk user (pengamanan tambahan)
    if (!$user) {
        // Jika user null meskipun Auth::check() true (jarang terjadi), redirect ke login
        Auth::logout(); // Logout paksa jika state aneh
        return redirect()->route('login');
    }

    // Panggil method role pada $user
    if ($user->isSuperAdmin() || $user->isPetugasPiket()) { // IDE seharusnya mengenali ini sekarang
        return redirect()->route('admin.dashboard');
    }
    if ($user->isGuru() || $user->isSiswa()) { // IDE seharusnya mengenali ini sekarang
        return redirect()->route('attendance.history');
    }
    // Fallback
    return redirect()->route('dashboard');
})->name('home');


// --------------------------------------------------------------------------
// Grup Rute Utama yang Membutuhkan Otentikasi (Login)
// --------------------------------------------------------------------------

    // Dashboard Default Bawaan Laravel (Fallback)
    Route::get('/dashboard', function () {
        /** @var \App\Models\User|null $user */ // <-- Tambahkan hint di sini
        $user = Auth::user(); // <-- Pastikan menggunakan Auth::user() atau auth()->user()

        // Cek null untuk user
        if (!$user) {
            Auth::logout();
            return redirect()->route('login');
        }

        // Panggil method role pada $user
        if ($user->isSuperAdmin() || $user->isPetugasPiket()) { // IDE seharusnya mengenali ini
             return redirect()->route('admin.dashboard');
        }
        if ($user->isGuru() || $user->isSiswa()) { // IDE seharusnya mengenali ini
             return redirect()->route('attendance.history');
        }
        // Tampilkan view dashboard default jika tidak cocok role di atas
        return view('dashboard');
    })->name('dashboard');

    // ... (sisa route di dalam grup auth) ...

    // --- Rute Profil Pengguna ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- Rute Presensi (Hanya Guru & Siswa) ---
    Route::prefix('presensi') // Semua URL diawali /presensi
        ->name('attendance.') // Semua nama route diawali attendance.
        ->middleware('role:Guru,Siswa') // Hanya role Guru/Siswa
        ->group(function () {
            Route::get('/buat', [AttendanceController::class, 'create'])->name('create');
            Route::post('/', [AttendanceController::class, 'store'])->name('store'); // POST ke /presensi
            Route::get('/riwayat', [AttendanceController::class, 'index'])->name('history');
    });
// --- Rute Presensi (Hanya Guru & Siswa) ---
Route::prefix('presensi')
    ->name('attendance.')
    ->middleware('role:Guru,Siswa') // Middleware role statis
    ->group(function () {
        Route::get('/buat', [AttendanceController::class, 'create'])->name('create');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
        Route::get('/riwayat', [AttendanceController::class, 'index'])->name('history');
});

// --- Grup Rute Area Admin ---
// Middleware role statis di sini akan membatasi akses ke SELURUH KONTROLLER di dalam grup ini
// hanya untuk user dengan role Super Admin atau Petugas Piket statis.
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:Super Admin,Petugas Piket']) // <-- MIDDLEWARE AUTH & ROLE STATIS DI TINGKAT GRUP
    ->group(function () {

        // Dashboard Admin (Middleware grup berlaku)
        // Otorisasi tambahan BISA di controller jika ada nuansa lain
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // --- Route untuk Manajemen Presensi Manual (Admin) ---
        // Middleware grup 'role' berlaku di sini.
        // Kontrol akses spesifik (index, edit, update) dilakukan DI DALAM AdminAttendanceController
        // menggunakan Gate dinamis yang memeriksa jadwal piket hari ini.
        // create, store, destroy tetap hanya untuk Super Admin via cek di controller.
        Route::resource('/attendances', AdminAttendanceController::class)->except(['show']); // <-- Biarkan TANPA middleware 'can' atau 'role' tambahan di sini

        // Manajemen Jadwal Piket (Middleware grup berlaku. Otorisasi Super Admin di controller)
         Route::resource('/picket-schedules', PicketScheduleController::class)->except(['show']);

        // Laporan (Middleware grup berlaku. Otorisasi di controller berdasarkan Super Admin / Petugas Piket statis)
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/export-pdf', [AdminReportController::class, 'exportPdf'])->name('reports.export.pdf');

        // Daftar Kelas (Middleware grup berlaku. Otorisasi di controller berdasarkan Super Admin / Petugas Piket statis)
        Route::get('/classes', [AdminKelasController::class, 'index'])->name('classes.index');

        // --- Grup Rute Khusus Super Admin ---
        // Middleware ini akan membatasi lebih lanjut DI DALAM grup /admin
        // hanya untuk user dengan role Super Admin
        Route::middleware('role:Super Admin')->group(function () {
             // User Management (Middleware grup berlaku. Otorisasi Super Admin di controller)
             Route::resource('/users', AdminUserController::class);

             // Class Management (CRUD Actions) (Middleware grup berlaku. Otorisasi Super Admin di controller)
             Route::post('/classes', [AdminKelasController::class, 'store'])->name('classes.store');
             Route::put('/classes/{kela}', [AdminKelasController::class, 'update'])->name('classes.update');
             Route::delete('/classes/{kela}', [AdminKelasController::class, 'destroy'])->name('classes.destroy');

             // Application Settings (Middleware grup berlaku. Otorisasi Super Admin di controller)
             Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
             Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        });
        // --- Akhir Grup Khusus Super Admin ---

    });
// --- Akhir Grup Rute Area Admin ---

// --- Rute Otentikasi Bawaan Laravel ---
require __DIR__.'/auth.php';