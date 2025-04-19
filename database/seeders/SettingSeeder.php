<?php

// database/seeders/SettingSeeder.php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder {
    public function run() {
        Setting::create([
            'school_latitude' => -6.200000, // Ganti dengan Latitude Sekolah Anda
            'school_longitude' => 106.816666, // Ganti dengan Longitude Sekolah Anda
            'allowed_radius_meters' => 150, // Radius toleransi (meter)
            'attendance_start_time' => '07:00:00',
            'attendance_end_time' => '16:00:00',
            'late_threshold_time' => '07:15:00',
        ]);
    }
}