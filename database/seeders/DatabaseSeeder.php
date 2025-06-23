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
            SettingsSeeder::class,
            KelasSeeder::class,    // <-- Benar, dijalankan sebelum UserSeeder
            UserSeeder::class,
            AttendanceSeeder::class,
        ]);
    }
}