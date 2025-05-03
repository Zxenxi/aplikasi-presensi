<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // <-- PASTIKAN INI ADA & TIDAK DI-COMMENT
use Illuminate\Http\Request; // <-- Tambahkan ini jika ingin pakai $request->user()
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
// Controller Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;

// ... (sisa kode routes dimulai di sini) ...
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di sini Anda bisa mendaftarkan web routes untuk aplikasi Anda. Route ini
| dimuat oleh RouteServiceProvider dan semuanya akan
| ditugaskan ke grup middleware "web". Buat sesuatu yang hebat!
|
*/

// --------------------------------------------------------------------------
// Rute Publik & Redirect Awal
// --------------------------------------------------------------------------

// Rute halaman utama ('/')
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
Route::middleware(['auth'])->group(function () {

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

    // --- Grup Rute Area Admin (Hanya Super Admin & Petugas Piket) ---
    Route::prefix('admin') // Semua URL diawali /admin
        ->name('admin.') // Semua nama route diawali admin.
        ->middleware('role:Super Admin,Petugas Piket') // Hanya role Admin/Piket
        ->group(function () {

            // Dashboard Admin
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            // Manajemen Presensi Manual (Admin & Piket bisa lihat index, CRUD hanya Super Admin via Controller)
            Route::resource('/attendances', AdminAttendanceController::class)->except(['show']);

            // Laporan (Admin & Piket bisa lihat & export)
            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
            Route::get('/reports/export-pdf', [AdminReportController::class, 'exportPdf'])->name('reports.export.pdf'); // Sesuaikan nama route PDF

            // Daftar Kelas (Admin & Piket bisa lihat)
            Route::get('/classes', [AdminKelasController::class, 'index'])->name('classes.index');

            // --- Grup Rute Khusus Super Admin ---
            // Hanya Super Admin yang bisa mengakses route di dalam grup ini
            Route::middleware('role:Super Admin')->group(function () {
                // User Management
                Route::resource('/users', AdminUserController::class);

                // Class Management (CRUD Actions)
                Route::post('/classes', [AdminKelasController::class, 'store'])->name('classes.store');
                Route::put('/classes/{kela}', [AdminKelasController::class, 'update'])->name('classes.update');
                Route::delete('/classes/{kela}', [AdminKelasController::class, 'destroy'])->name('classes.destroy');

                // Application Settings
                Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
                Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
            });
            // --- Akhir Grup Khusus Super Admin ---

        });
    // --- Akhir Grup Rute Area Admin ---

});
// --- Akhir Grup Rute Utama Otentikasi ---
// --------------------------------------------------------------------------
// Rute Otentikasi Bawaan Laravel (dari Breeze/Jetstream)
// --------------------------------------------------------------------------
// Pastikan file ini di-require untuk menangani login, logout, dll.
require __DIR__.'/auth.php';