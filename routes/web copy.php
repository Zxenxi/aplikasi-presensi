<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;
// Controller Admin
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use Illuminate\Support\Facades\Auth; // <-- PASTIKAN INI ADA & TIDAK DI-COMMENT
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use Illuminate\Http\Request; // <-- Tambahkan ini jika ingin pakai $request->user()
use App\Http\Controllers\Admin\JadwalPiketController as AdminJadwalPiketController; // <-- TAMBAHKAN INI
// ... (sisa kode routes dimulai di sini) ...


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
    if ($user->isSuperAdmin() ) { // IDE seharusnya mengenali ini sekarang
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
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        // Jika guru, cek apakah dia petugas piket hari ini
        if ($user->isGuru()) {
            if ($user->isPetugasPiket()) {
                // Guru yang piket hari ini bisa redirect ke dashboard admin
                return redirect()->route('admin.dashboard');
            } else {
                // Guru yang bukan petugas piket diarahkan ke halaman presensi
                return redirect()->route('attendance.create');
            }
        }
        if ($user->isSiswa()) {
            return redirect()->route('attendance.create');
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

    // --- Grup Rute Area Admin (Hanya Super Admin ---
    Route::prefix('admin') // Semua URL diawali /admin
        ->name('admin.') // Semua nama route diawali admin.
        ->middleware('role:Super Admin') // Hanya role Admin/Piket
        ->group(function () {

            // Dashboard Admin
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            // Manajemen Presensi Manual (Admin & Piket bisa lihat index, CRUD hanya Super Admin via Controller)
            Route::resource('/attendances', AdminAttendanceController::class)->except(['show']);
            Route::get('/attendances/user/{user}', [AdminAttendanceController::class, 'userHistory'])->name('attendances.user_history');

            // Laporan (Admin & Piket bisa lihat & export)
            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
            Route::get('/reports/export-pdf', [AdminReportController::class, 'exportPdf'])->name('reports.export.pdf'); 
            
            // Daftar Kelas (Admin & Piket bisa lihat)
            Route::get('/classes', [AdminKelasController::class, 'index'])->name('classes.index');

            // --- Grup Rute Khusus Super Admin ---
            // Hanya Super Admin yang bisa mengakses route di dalam grup ini
           Route::patch('/classes/{class}/deactivate-with-students', [\App\Http\Controllers\Admin\KelasController::class, 'deactivateWithStudents'])->name('classes.deactivateWithStudents');
            Route::middleware('role:Super Admin')->group(function () {
                // User Management
                Route::resource('/users', AdminUserController::class);

                // Class Management (CRUD Actions)
                Route::post('/classes', [AdminKelasController::class, 'store'])->name('classes.store');
                // Route::put('/classes/{kela}', [AdminKelasController::class, 'update'])->name('classes.update');
                // Route::delete('/classes/{kela}', [AdminKelasController::class, 'destroy'])->name('classes.destroy');
                Route::put('/classes/{class}', [AdminKelasController::class, 'update'])->name('classes.update');
                Route::delete('/classes/{class}', [AdminKelasController::class, 'destroy'])->name('classes.destroy');
                Route::get('/classes/promote', [AdminKelasController::class, 'showPromotionForm'])->name('classes.promotionForm');
                Route::post('/classes/promote', [AdminKelasController::class, 'processPromotion'])->name('classes.processPromotion');
                Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');
                Route::get('/classes/{class}/show', [AdminKelasController::class, 'show'])->name('classes.show'); 
                // Di dalam grup admin...
                Route::post('/classes/{class}/bulk-update-students', [AdminKelasController::class, 'bulkUpdateStudents'])->name('classes.bulkUpdateStudents');
                // Application Settings
                Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
                Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

                 // == RUTE BARU UNTUK MANAJEMEN JADWAL PIKET ==
                 Route::resource('/picket-schedules', AdminJadwalPiketController::class)
                 ->except(['show']) // Jika Anda tidak menggunakan halaman show individu
                 ->names('picket_schedules'); // Memberi nama route seperti admin.picket_schedules.index, .create, dll.
            // =============================================
            });
            // --- Akhir Grup Khusus Super Admin ---
        });
   
});
require __DIR__.'/auth.php';