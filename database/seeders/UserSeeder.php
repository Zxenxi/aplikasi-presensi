<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $kelasIds = Kelas::pluck('id')->toArray(); // Ambil semua ID kelas

        // === Buat User Guru ===
        // ... (kode membuat guru tetap sama) ...
        $this->command->info('Memulai Seeder Guru...');
        $guruUsers = [];
        for ($i = 0; $i < 15; $i++) { // Buat 15 guru (sesuaikan jika perlu)
            $guruUsers[] = User::create([
                'name' => 'Guru ' . $faker->firstName() . ' ' . $faker->lastName(),
                'email' => "guru_{$i}_{$faker->unique()->word}@presensi.sch.id", // Email unik guru
                'email_verified_at' => now(),
                'role' => 'Guru',
                'kelas_id' => null,
                'password' => Hash::make('password'),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ]);
        }
         $this->command->info('-> Seeder Guru selesai.');

        // === Assign Wali Kelas ===
        // ... (kode assign wali kelas tetap sama) ...
         $this->command->info('Memulai Assign Wali Kelas...');
         $availableGuruIds = collect($guruUsers)->pluck('id')->shuffle();
         $allKelas = Kelas::all();
         foreach ($allKelas as $index => $kelas) {
             $waliKelasId = $availableGuruIds->get($index);
             if ($waliKelasId) { // Pastikan ada guru tersedia
                  $kelas->wali_kelas_id = $waliKelasId;
                  $kelas->save();
             }
         }
          $this->command->info('-> Assign Wali Kelas selesai.');


        // === Buat User Siswa (LOGIKA BARU) ===
        $this->command->info('Memulai Seeder Siswa...');

        if (empty($kelasIds)) {
            $this->command->warn('PERINGATAN: Tidak ada data Kelas ditemukan saat menjalankan UserSeeder.');
            $this->command->warn('-> Seeder Siswa akan dilewati. Pastikan KelasSeeder berjalan sebelum UserSeeder.');
        } else {
            // --- AWAL PERUBAHAN LOGIKA ---
            $targetTotalSiswa = 50; // Target total siswa yang ingin dibuat
            $this->command->info("Target: Membuat total {$targetTotalSiswa} siswa dan mendistribusikannya ke kelas yang ada...");

            for ($s = 0; $s < $targetTotalSiswa; $s++) {
                // Pilih kelas secara acak untuk siswa ini dari ID kelas yang ada
                $randomKelasId = $faker->randomElement($kelasIds);

                // Buat email unik untuk siswa
                $uniqueSuffix = uniqid(); // Tambahkan suffix unik sederhana
                User::create([
                    'name' => $faker->firstName() . ' ' . $faker->lastName(),
                    'email' => "siswa_{$randomKelasId}_{$s}_{$uniqueSuffix}@presensi.sch.id", // Format email alternatif
                    'email_verified_at' => now(),
                    'role' => 'Siswa',
                    'kelas_id' => $randomKelasId, // Assign siswa ke kelas acak
                    'password' => Hash::make('password'), // Gunakan password default
                    'remember_token' => \Illuminate\Support\Str::random(10),
                ]);

                // Beri jeda sedikit jika perlu untuk menghindari beban database berlebih (opsional)
                // if ($s % 100 == 0) { usleep(100000); } // Jeda 0.1 detik setiap 100 siswa
            }
            // --- AKHIR PERUBAHAN LOGIKA ---
            $this->command->info("-> Seeder Siswa selesai. Total {$targetTotalSiswa} siswa (kurang lebih) telah dibuat.");
        }

        // === Buat User Admin ===
        // ... (kode membuat admin tetap sama) ...
         $this->command->info('Memulai Seeder Admin...');
          User::create([
                 'name' => 'Admin Presensi',
                 'email' => 'admin@sekolah.sch.id', // Ganti dengan email admin
                 'email_verified_at' => now(),
                 'role' => 'Super Admin', // Tambahkan role Admin jika diperlukan
                 'kelas_id' => null,
                 'password' => Hash::make('password'), // Ganti password admin
                 'remember_token' => \Illuminate\Support\Str::random(10),
          ]);
          $this->command->info('-> Seeder Admin selesai.');


        $this->command->info('Seeder User selesai secara keseluruhan.');
    }
}