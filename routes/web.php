<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AttendanceController;

// Controller Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\JadwalPiketController as AdminJadwalPiketController;


// --------------------------------------------------------------------------
// Rute Publik & Redirect Awal
// --------------------------------------------------------------------------

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    if (!$user) {
        Auth::logout();
        return redirect()->route('login');
    }

    if ($user->isSuperAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user->isGuru() || $user->isSiswa()) {
        return redirect()->route('attendance.history');
    }

    return redirect()->route('dashboard');
})->name('home');


// --------------------------------------------------------------------------
// Grup Rute dengan Otentikasi
// --------------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // Dashboard Default
    Route::get('/dashboard', function () {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (!$user) {
            Auth::logout();
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isGuru()) {
            return $user->isPetugasPiket()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('attendance.create');
        }

        if ($user->isSiswa()) {
            return redirect()->route('attendance.create');
        }

        return view('dashboard');
    })->name('dashboard');


    // --- Profil ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // --- Presensi (Guru & Siswa) ---
    Route::prefix('presensi')
        ->name('attendance.')
        ->middleware('role:Guru,Siswa')
        ->group(function () {
            Route::get('/buat', [AttendanceController::class, 'create'])->name('create');
            Route::post('/', [AttendanceController::class, 'store'])->name('store');
            Route::get('/riwayat', [AttendanceController::class, 'index'])->name('history');
        });


    // --- Admin (Super Admin) ---
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:Super Admin')
        ->group(function () {

            // Dashboard
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            // Presensi (CRUD Manual)
            Route::resource('/attendances', AdminAttendanceController::class)->except(['show']);
            Route::get('/attendances/user/{user}', [AdminAttendanceController::class, 'userHistory'])->name('attendances.user_history');

            // Laporan
            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');
            Route::get('/reports/export-pdf', [AdminReportController::class, 'exportPdf'])->name('reports.export.pdf');

            // Kelas
            Route::get('/classes', [AdminKelasController::class, 'index'])->name('classes.index');
            Route::patch('/classes/{class}/deactivate-with-students', [AdminKelasController::class, 'deactivateWithStudents'])->name('classes.deactivateWithStudents');

            // --- Khusus Super Admin ---
            Route::middleware('role:Super Admin')->group(function () {
                // User Management
                Route::resource('/users', AdminUserController::class);
                Route::patch('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggleStatus');

                // Class Management
                Route::post('/classes', [AdminKelasController::class, 'store'])->name('classes.store');
                Route::put('/classes/{class}', [AdminKelasController::class, 'update'])->name('classes.update');
                Route::delete('/classes/{class}', [AdminKelasController::class, 'destroy'])->name('classes.destroy');
                Route::get('/classes/promote', [AdminKelasController::class, 'showPromotionForm'])->name('classes.promotionForm');
                Route::post('/classes/promote', [AdminKelasController::class, 'processPromotion'])->name('classes.processPromotion');
                Route::get('/classes/{class}/show', [AdminKelasController::class, 'show'])->name('classes.show');
                Route::post('/classes/{class}/bulk-update-students', [AdminKelasController::class, 'bulkUpdateStudents'])->name('classes.bulkUpdateStudents');

                // Settings
                Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
                Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

                // Jadwal Piket
                Route::resource('/picket-schedules', AdminJadwalPiketController::class)
                    ->except(['show'])
                    ->names('picket_schedules');
            });
        });
});

require __DIR__ . '/auth.php';