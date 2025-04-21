<?php

// --------------------------------------------------------------------------
// Import Semua Controller di Awal
// --------------------------------------------------------------------------
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController; // Untuk presensi Siswa/Guru
// Controller Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController; // Untuk CRUD presensi oleh Admin

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
Route::get('/', function () {
    // Jika user belum login, arahkan ke halaman login
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    // Jika sudah login, arahkan berdasarkan role
    $user = auth()->user();
    if ($user->isSuperAdmin() || $user->isPetugasPiket()) {
        return redirect()->route('admin.dashboard'); // Ke dashboard admin
    }
    if ($user->isGuru() || $user->isSiswa()) {
        return redirect()->route('attendance.history'); // Ke riwayat presensi
    }
    // Fallback ke dashboard default jika role tidak cocok
    return redirect()->route('dashboard');
})->name('home');


// --------------------------------------------------------------------------
// Grup Rute Utama yang Membutuhkan Otentikasi (Login)
// --------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // --- Dashboard Bawaan Laravel (Fallback) ---
    // Diakses jika user login tapi role tidak cocok dengan admin/guru/siswa
    // atau sebagai tujuan redirect sementara
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isPetugasPiket()) { return redirect()->route('admin.dashboard'); }
        if ($user->isGuru() || $user->isSiswa()) { return redirect()->route('attendance.history'); }
        // Jika tidak cocok, tampilkan view dashboard default dari Breeze/Jetstream
        return view('dashboard');
    })->name('dashboard');

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