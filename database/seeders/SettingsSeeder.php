<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Setting; // Pastikan Model Setting sudah dibuat

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama jika ada, untuk memastikan hanya ada 1 baris
        Setting::truncate(); 

        Setting::create([
            'school_latitude'        => -7.709829547744618, // Contoh Latitude Jakarta
            'school_longitude'       => 110.0077382299018, // Contoh Longitude Jakarta
            'allowed_radius_meters'  => 150,
            'attendance_start_time'  => '06:00:00',
            'attendance_end_time'    => '23:30:00',
            'late_threshold_time'    => '23:15:00',
        ]);
    }
}