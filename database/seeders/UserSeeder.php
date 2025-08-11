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
        $guruList = [
            'SETYAWAN ARI RESPATI, S.Pd',
            'KUKUH YOGA GANJAR SUTARWAN, S.Pd',
            'TRI WINARSI',
            'DWI KRISTIASIH, S.Pd',
            'PRABANTORO SAPUTRO, S.Pd',
            'DIKA SRI VANIARI',
            'ENDANG WAHYUNINGSIH, Apt. M. Kes.',
            'SULISTYOWATI, S.Pd',
            'MAYDA ADRIYANTI, S.Pd',
            'LISA PRASTIYANTI, S.E.',
            'DYAH SIWI R, S.E.',
            'CAHYANING RATRI, S.Pd',
            'WIDAYANTI, S.Pd',
            'Apt. AGUSTA ARI MURTI KRISTYANTI, M.Farm.',
            'FIRMANSAH AL FATONI',
            'Apt. FRANSISCA INDAH PRATIWI, M.Farm.',
            'DRA. SUHARTATI',
            'CHRISTIANA YUSI WULANDARI, S.Pd',
            'PUJI WALUYO, S.Kom.',
            'DRS. DIDIK HADI PRAYITNO',
            'CATARINA SEGIHARNI, S.Pd',
            'SRI WENING ARIANI, S.Pd',
            'WATINI',
            'ENI MURNIATI, S.E.',
            'MARIA SUCI DEWI LESTARI, SE., S.Pd',
            'HERIYANTO',
            'EKO PRASTOWO',
            'PURNOMO',
        ];
        $usedEmails = [];
        foreach ($guruList as $idx => $namaGuru) {
            $namaDepan = strtolower(preg_replace('/[^a-zA-Z]/', '', explode(' ', $namaGuru)[0]));
            $emailBase = $namaDepan;
            $email = $emailBase . '@gmail.com';
            $counter = 1;
            while (in_array($email, $usedEmails)) {
                $email = $emailBase . $counter . '@gmail.com';
                $counter++;
            }
            $usedEmails[] = $email;
            User::create([
                'name' => $namaGuru,
                'email' => $email,
                'email_verified_at' => now(),
                'role' => 'Guru',
                'kelas_id' => null, // Guru tidak punya kelas_id
                'password' => Hash::make('password'),
                'remember_token' => \Illuminate\Support\Str::random(10),
            ]);
        }
        $this->command->info('-> Seeder Guru selesai.');

        // === BLOK ASSIGN WALI KELAS DIHAPUS DARI SINI ===
        // Tidak ada lagi logika untuk assign wali kelas

        // === Buat User Siswa ===
        $this->command->info('Memulai Seeder Siswa...');

        $siswaList = [
            'YOLANDA APRILIA KACARIBU',
            'Nanda Alief Sahara Ramadhan',
            'LAURA BADRIANI',
            'MILANIA ZALIKA SILOVESKY',
            'Jelita Siahaan',
            'Gresia Kurniasari',
            'Mei Sumi Rahayu',
            'Rocinta Br Gultom',
        ];

        if (empty($kelasIds)) {
            $this->command->warn('PERINGATAN: Tidak ada data Kelas ditemukan. Seeder Siswa akan dilewati.');
        } else {
            foreach ($siswaList as $idx => $namaSiswa) {
                $randomKelasId = $faker->randomElement($kelasIds);
                $email = 'siswa' . ($idx + 1) . '@example.com';
                User::create([
                    'name' => $namaSiswa,
                    'email' => $email,
                    'email_verified_at' => now(),
                    'role' => 'Siswa',
                    'kelas_id' => $randomKelasId,
                    'password' => Hash::make('password'),
                    'remember_token' => \Illuminate\Support\Str::random(10),
                ]);
            }
            $this->command->info('-> Seeder Siswa selesai. ' . count($siswaList) . ' siswa telah dibuat.');
        }

        // === Buat User Admin ===
        $this->command->info('Memulai Seeder Admin...');
        User::create([
            'name' => 'Admin Presensi',
            'email' => 'admin@example.com',
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