<?php

namespace App\Providers;

use App\Models\PicketSchedule;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Definisikan Gate 'manageTodayAttendanceAdmin'
        // Mengizinkan akses jika user adalah Super Admin ATAU terjadwal piket hari ini.
        Gate::define('manageTodayAttendanceAdmin', function (User $user) {
            if ($user->isSuperAdmin()) {
                return true; // Super Admin selalu diizinkan
            }

            // Cek apakah user terjadwal piket hari ini.
            return PicketSchedule::where('user_id', $user->id)
                                 ->whereDate('duty_date', Carbon::today())
                                 ->exists();
        });

        // Anda tidak perlu mengubah Gate atau Policy lain di sini.
    }
}