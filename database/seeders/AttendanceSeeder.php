<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Setting;
use Carbon\Carbon;
use Faker\Factory as Faker;
use InvalidArgumentException; // Import Exception

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Hapus data attendance lama
        Attendance::truncate();

        $faker = Faker::create('id_ID');
        $users = User::whereIn('role', ['Guru', 'Siswa'])->get(); // Ambil Guru & Siswa saja
        $settings = Setting::first();

        if (!$settings) {
            $this->command->error('Settings not found. Please run SettingsSeeder first.');
            return;
        }
        if ($users->isEmpty()) {
            $this->command->warn('No Teachers or Students found to seed attendance for.');
            return;
        }

        // --- Konfigurasi Waktu & Status ---
        $today = Carbon::today();
        try {
            // Parsing waktu dari settings
            $attendanceStartTimeConfig = Carbon::parse($settings->attendance_start_time);
            $lateThresholdTimeConfig = Carbon::parse($settings->late_threshold_time);

            // Tentukan waktu akhir yang wajar untuk presensi hari ini (misal: +1 jam dari start)
            // Pastikan end time > start time, minimal 1 detik beda jika start = late threshold
            $reasonableEndTimeConfig = $attendanceStartTimeConfig->copy()->addHour();
            if ($reasonableEndTimeConfig <= $attendanceStartTimeConfig) {
                 $reasonableEndTimeConfig = $attendanceStartTimeConfig->copy()->addHour(); // Default +1 jam jika aneh
            }

            // Tentukan waktu akhir yang wajar untuk presensi hari lalu (misal: +3 jam dari start)
            $pastEndTimeConfig = $attendanceStartTimeConfig->copy()->addHours(3);
             if ($pastEndTimeConfig <= $attendanceStartTimeConfig) {
                 $pastEndTimeConfig = $attendanceStartTimeConfig->copy()->addHours(3); // Default +3 jam jika aneh
             }


        } catch (\Exception $e) {
            $this->command->error('Error parsing time settings: ' . $e->getMessage());
            $this->command->error('Please check attendance_start_time and late_threshold_time in the settings table.');
            return; // Stop seeder jika waktu tidak valid
        }


        // Status pools (sama seperti sebelumnya)
        $todayStatusPool = array_merge(/*...*/); // Isi seperti sebelumnya
        $pastStatusPool = array_merge(/*...*/);  // Isi seperti sebelumnya
         // -- ISI KEMBALI ARRAY STATUS POOL DARI KODE SEBELUMNYA --
         $todayStatusPool = array_merge(
             array_fill(0, 70, 'Hadir'),   // 70% Hadir
             array_fill(0, 15, 'Telat'),   // 15% Telat
             array_fill(0, 5, 'Izin'),    // 5% Izin
             array_fill(0, 5, 'Sakit'),   // 5% Sakit
             array_fill(0, 5, 'Absen')    // 5% Absen
         );
         $pastStatusPool = array_merge(
             array_fill(0, 65, 'Hadir'),
             array_fill(0, 15, 'Telat'),
             array_fill(0, 5, 'Izin'),
             array_fill(0, 5, 'Sakit'),
             array_fill(0, 10, 'Absen')
         );
         // ---------------------------------------------------------


        // --- Generate Data untuk Hari Ini ---
        $this->command->info("--- Generating attendance for Today ({$today->toDateString()}) ---");
        foreach ($users as $user) {
            // $this->command->info("Processing user: {$user->id} ({$user->role}) for today..."); // Uncomment untuk detail log

            $statusToday = $faker->randomElement($todayStatusPool);

            if ($statusToday !== 'Absen') {
                $jamMasuk = null; $latitude = null; $longitude = null; $isLocationValid = null; $selfiePath = null;

                if ($statusToday === 'Hadir' || $statusToday === 'Telat') {
                    // Tentukan waktu mulai dan selesai untuk hari ini
                    $startDateTime = $today->copy()->setTimeFrom($attendanceStartTimeConfig);
                    $endDateTime = $today->copy()->setTimeFrom($reasonableEndTimeConfig);

                    // Safety check jika end <= start (seharusnya tidak terjadi dengan logika di atas)
                    if ($endDateTime <= $startDateTime) {
                         $this->command->error("End time ({$endDateTime->toTimeString()}) is not after start time ({$startDateTime->toTimeString()}) for today. Skipping time generation for user {$user->id}.");
                          $jamMasuk = $startDateTime->format('H:i:s'); // Default ke start time jika error
                          $statusToday = $startDateTime->gt($lateThresholdTimeConfig) ? 'Telat' : 'Hadir';
                    } else {
                         try {
                              $jamMasukCarbon = Carbon::createFromTimestamp(
                                   $faker->dateTimeBetween($startDateTime, $endDateTime)->getTimestamp()
                              );
                              $jamMasuk = $jamMasukCarbon->format('H:i:s');
                              // Tentukan ulang status berdasarkan threshold
                              $statusToday = $jamMasukCarbon->gt($lateThresholdTimeConfig) ? 'Telat' : 'Hadir';
                         } catch (InvalidArgumentException $e) {
                              // Tangkap error spesifik dari Faker
                              $this->command->error("Faker dateTimeBetween error for user {$user->id} today: " . $e->getMessage());
                              $this->command->error("Start: {$startDateTime}, End: {$endDateTime}");
                              $jamMasuk = $startDateTime->format('H:i:s'); // Default ke start time
                              $statusToday = $startDateTime->gt($lateThresholdTimeConfig) ? 'Telat' : 'Hadir';
                         }
                    }


                    // Generate data lokasi dekat sekolah (sama seperti sebelumnya)
                    $latitude = $faker->latitude($settings->school_latitude - 0.001, $settings->school_latitude + 0.001);
                    $longitude = $faker->longitude($settings->school_longitude - 0.001, $settings->school_longitude + 0.001);
                    $isLocationValid = $faker->boolean(85);
                    $selfiePath = 'dummy/selfie.jpg';
                }

                Attendance::create([ /* ... data ... */
                    'user_id' => $user->id,
                    'tanggal' => $today->toDateString(),
                    'jam_masuk' => $jamMasuk,
                    'status' => $statusToday,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'selfie_path' => $selfiePath,
                    'is_location_valid' => $isLocationValid,
                    'created_at' => $today,
                    'updated_at' => $today,
                ]);
            }
        }


        // --- Generate Data untuk 7 Hari Terakhir ---
        $this->command->info("--- Generating attendance for Past 7 Days ---");
        $startDateForTrend = $today->copy()->subDays(7);

        for ($date = $startDateForTrend; $date->lt($today); $date->addDay()) {
            // $this->command->info("- Processing date: {$date->toDateString()}"); // Uncomment untuk detail log
            foreach ($users as $user) {
                 // $this->command->info("  Processing user: {$user->id} ({$user->role}) on {$date->toDateString()}"); // Uncomment untuk detail log

                $statusPast = $faker->randomElement($pastStatusPool);

                if ($statusPast !== 'Absen') {
                    $jamMasuk = null; $latitude = null; $longitude = null; $isLocationValid = null; $selfiePath = null;

                    if ($statusPast === 'Hadir' || $statusPast === 'Telat') {
                         // Tentukan waktu mulai dan selesai untuk hari lalu
                         $startDateTimePast = $date->copy()->setTimeFrom($attendanceStartTimeConfig);
                         $endDateTimePast = $date->copy()->setTimeFrom($pastEndTimeConfig); // Gunakan config yg sudah fix

                         // Safety check jika end <= start
                         if ($endDateTimePast <= $startDateTimePast) {
                              $this->command->error("End time ({$endDateTimePast->toTimeString()}) is not after start time ({$startDateTimePast->toTimeString()}) for date {$date->toDateString()}. Skipping time generation for user {$user->id}.");
                              $jamMasuk = $startDateTimePast->format('H:i:s');
                              $statusPast = $startDateTimePast->gt($lateThresholdTimeConfig) ? 'Telat' : 'Hadir';
                         } else {
                              try {
                                   $jamMasukCarbon = Carbon::createFromTimestamp(
                                        $faker->dateTimeBetween($startDateTimePast, $endDateTimePast)->getTimestamp()
                                   );
                                   $jamMasuk = $jamMasukCarbon->format('H:i:s');
                                   // Tentukan ulang status berdasarkan threshold
                                   $statusPast = $jamMasukCarbon->gt($lateThresholdTimeConfig) ? 'Telat' : 'Hadir';
                              } catch (InvalidArgumentException $e) {
                                   $this->command->error("Faker dateTimeBetween error for user {$user->id} on {$date->toDateString()}: " . $e->getMessage());
                                   $this->command->error("Start: {$startDateTimePast}, End: {$endDateTimePast}");
                                   $jamMasuk = $startDateTimePast->format('H:i:s'); // Default ke start time
                                   $statusPast = $startDateTimePast->gt($lateThresholdTimeConfig) ? 'Telat' : 'Hadir';
                              }
                         }

                        // Generate data lokasi (sama seperti sebelumnya)
                        $latitude = $faker->latitude($settings->school_latitude - 0.001, $settings->school_latitude + 0.001);
                        $longitude = $faker->longitude($settings->school_longitude - 0.001, $settings->school_longitude + 0.001);
                        $isLocationValid = $faker->boolean(85);
                        $selfiePath = 'dummy/selfie.jpg';
                    }

                    Attendance::create([ /* ... data ... */
                         'user_id' => $user->id,
                         'tanggal' => $date->toDateString(),
                         'jam_masuk' => $jamMasuk,
                         'status' => $statusPast,
                         'latitude' => $latitude,
                         'longitude' => $longitude,
                         'selfie_path' => $selfiePath,
                         'is_location_valid' => $isLocationValid,
                         'created_at' => $date,
                         'updated_at' => $date,
                    ]);
                }
            }
        }

        $this->command->info('Attendance seeding finished.');
    }
}