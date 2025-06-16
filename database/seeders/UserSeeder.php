<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $kelasIds = Kelas::pluck('id')->toArray(); // Ambil semua ID kelas

        // === Buat User Guru ===
        $this->command->info('Memulai Seeder Guru...');
        $guruUsers = [];
        for ($i = 0; $i < 15; $i++) { // Buat 15 guru
            $guruUsers[] = User::create([
                'name' => 'Guru ' . $faker->firstName() . ' ' . $faker->lastName(),
                'email' => "guru" . ($i + 1) . "@example.com", // Email sesuai format yang diminta
                'email_verified_at' => now(),
                'role' => 'Guru',
                'kelas_id' => null,
                'password' => Hash::make('password'),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ]);
        }
        $this->command->info('-> Seeder Guru selesai.');

        // === Assign Wali Kelas ===
        $this->command->info('Memulai Assign Wali Kelas...');
        $availableGuruIds = collect($guruUsers)->pluck('id')->shuffle();
        $allKelas = Kelas::all();
        foreach ($allKelas as $index => $kelas) {
            $waliKelasId = $availableGuruIds->get($index);
            if ($waliKelasId) {
                $kelas->wali_kelas_id = $waliKelasId;
                $kelas->save();
            }
        }
        $this->command->info('-> Assign Wali Kelas selesai.');

        // === Buat User Siswa ===
        $this->command->info('Memulai Seeder Siswa...');

        if (empty($kelasIds)) {
            $this->command->warn('PERINGATAN: Tidak ada data Kelas ditemukan. Seeder Siswa akan dilewati.');
        } else {
            $targetTotalSiswa = 50; // Target total siswa yang ingin dibuat
            $this->command->info("Target: Membuat {$targetTotalSiswa} siswa...");

            for ($s = 0; $s < $targetTotalSiswa; $s++) {
                $randomKelasId = $faker->randomElement($kelasIds);
                $uniqueSuffix = uniqid();
                User::create([
                    'name' => $faker->firstName() . ' ' . $faker->lastName(),
                    'email' => "siswa{$randomKelasId}_{$s}_{$uniqueSuffix}@example.com", // Format email siswa
                    'email_verified_at' => now(),
                    'role' => 'Siswa',
                    'kelas_id' => $randomKelasId,
                    'password' => Hash::make('password'),
                    'remember_token' => \Illuminate\Support\Str::random(10),
                ]);
            }
            $this->command->info("-> Seeder Siswa selesai. {$targetTotalSiswa} siswa telah dibuat.");
        }

        // === Buat User Admin ===
        $this->command->info('Memulai Seeder Admin...');
        User::create([
            'name' => 'Admin Presensi',
            'email' => 'admin@example.com', // Email admin
            'email_verified_at' => now(),
            'role' => 'Super Admin',
            'kelas_id' => null,
            'password' => Hash::make('password'),
            'remember_token' => \Illuminate\Support\Str::random(10),
        ]);
        $this->command->info('-> Seeder Admin selesai.');

        $this->command->info('Seeder User selesai secara keseluruhan.');
    }
}