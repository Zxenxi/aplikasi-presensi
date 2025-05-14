<?php

namespace App\Providers;

use App\Models\PicketSchedule; // Pastikan model ini di-import
use Carbon\Carbon; // Pastikan Carbon di-import
use App\Models\User; // Pastikan User di-import
use Illuminate\Support\Facades\Gate; // Pastikan Gate di-import
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Jika Anda menggunakan Policy lain, daftarkan di sini
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies(); // Jika menggunakan Policy, biarkan baris ini

        // Gate 1: Memeriksa apakah user terjadwal piket HARI INI
        // Ini adalah kondisi dinamis dasar.
        Gate::define('isScheduledPiketToday', function (User $user) {
             // Cek apakah ada entri di tabel picket_schedules untuk user ini pada tanggal hari ini.
             return PicketSchedule::where('user_id', $user->id)
                                  ->whereDate('duty_date', Carbon::today())
                                  ->exists();
        });

        // Gate 2: Memeriksa apakah user memiliki izin untuk MELIHAT daftar presensi admin (Index)
        // Izin diberikan jika user adalah Super Admin, ATAU role statis 'Petugas Piket',
        // ATAU user terjadwal piket hari ini.
        Gate::define('viewAdminAttendanceList', function (User $user) {
            // Super Admin selalu bisa melihat
            if ($user->isSuperAdmin()) {
                return true;
            }

            // User dengan role statis 'Petugas Piket' juga bisa melihat (sesuai struktur Anda)
            if ($user->isPetugasPiket()) { // Memanggil method isPetugasPiket() dari model User Anda
                return true;
            }

            // User yang terjadwal piket hari ini juga bisa melihat
            return Gate::allows('isScheduledPiketToday', $user);
        });


        // Gate 3: Memeriksa apakah user memiliki izin untuk MENGELOLA presensi admin (Edit/Update)
        // Izin diberikan jika user adalah Super Admin, ATAU user terjadwal piket hari ini.
        // Role statis 'Petugas Piket' SAJA tidak cukup untuk Gate ini.
        Gate::define('manageTodayAttendanceAdmin', function (User $user) {
            // Super Admin selalu bisa mengelola
            if ($user->isSuperAdmin()) {
                return true;
            }

            // User yang terjadwal piket hari ini bisa mengelola
            return Gate::allows('isScheduledPiketToday', $user);
        });

        // Definisikan Gate lain jika ada aksi spesifik lain yang ingin diatur...
    }
}