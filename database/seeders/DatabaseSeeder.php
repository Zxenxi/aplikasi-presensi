<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class, // Pengaturan umum dulu
            KelasSeeder::class,    // <<< PASTIKAN INI SEBELUM UserSeeder
            UserSeeder::class,      // Baru User (Guru, Siswa, Admin)
            AttendanceSeeder::class,
        ]);
        // $this->call([
        //     SettingsSeeder::class, // Pastikan ini sudah ada
        //     UserSeeder::class,    // Pastikan ini sudah ada & membuat cukup user
        //     KelasSeeder::class,   // Jika Anda punya seeder kelas
        //     AttendanceSeeder::class, // <-- Tambahkan ini
        //     // TodaysAttendanceSeeder::class,
        // ]);
    }
}