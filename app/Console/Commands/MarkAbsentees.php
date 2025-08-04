<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class MarkAbsentees extends Command
{
    protected $signature = 'attendance:mark-absent';
    protected $description = 'Mark users who have not checked in as absent';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $today = Carbon::today();

        // Ambil semua user aktif (Siswa & Guru)
        $users = User::whereIn('role', ['Siswa', 'Guru'])->where('is_active', true)->get();

        foreach ($users as $user) {
            // Cek apakah user sudah punya record presensi hari ini
            $attendanceExists = Attendance::where('user_id', $user->id)
                ->whereDate('tanggal', $today)
                ->exists();

            // Jika belum ada, buat record baru dengan status Absen
            if (!$attendanceExists) {
                Attendance::create([
                    'user_id' => $user->id,
                    'tanggal' => $today->toDateString(),
                    'status' => 'Absen',
                    'jam_masuk' => null,
                    'keterangan' => 'Tidak ada keterangan'
                ]);
            }
        }

        $this->info('Successfully marked absentees for today.');
    }
}
