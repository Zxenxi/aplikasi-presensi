<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JadwalPiket;
use Carbon\Carbon;

class CheckPicketAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Jika pengguna adalah Super Admin, berikan akses tanpa pengecekan jadwal.
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // --- LOGIKA BARU UNTUK PENGECEKAN JADWAL DAN JAM ---

        // 1. Dapatkan hari dan waktu saat ini sesuai timezone aplikasi (Asia/Jakarta)
        $now = Carbon::now();
        $hariIni = $now->dayOfWeekIso; // Senin = 1, Selasa = 2, ..., Minggu = 7
        $jamSekarang = $now->format('H:i:s');

        // 2. Cari jadwal piket untuk user ini pada hari ini
        $jadwalPiketHariIni = JadwalPiket::where('user_id', $user->id)
                                        ->where('hari_ke', $hariIni)
                                        ->first();

        // 3. Jika tidak ada jadwal sama sekali untuk hari ini, tolak akses.
        if (!$jadwalPiketHariIni) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki jadwal piket hari ini.');
        }

        // 4. Cek jam piket jika jam_mulai dan jam_selesai diisi
        $jamMulai = $jadwalPiketHariIni->jam_mulai;
        $jamSelesai = $jadwalPiketHariIni->jam_selesai;

        // Jika jam mulai dan selesai ada di jadwal, lakukan pengecekan waktu.
        if ($jamMulai && $jamSelesai) {
            // Tolak akses jika jam sekarang berada di luar rentang jam piket.
            if ($jamSekarang < $jamMulai || $jamSekarang > $jamSelesai) {
                return redirect()->route('dashboard')->with('error', 'Sekarang bukan jam piket Anda. Jadwal Anda adalah ' . Carbon::parse($jamMulai)->format('H:i') . ' - ' . Carbon::parse($jamSelesai)->format('H:i') . '.');
            }
        }
        // Jika jam mulai dan selesai tidak diatur (NULL), maka guru dianggap piket seharian penuh.
        // Dalam kasus ini, kita tidak perlu melakukan apa-apa dan langsung berikan akses.

        // 5. Jika semua pengecekan lolos, berikan akses ke halaman.
        return $next($request);
    }
}