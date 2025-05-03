<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kelas; // Pastikan Model Kelas sudah dibuat
use Faker\Factory as Faker;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID'); // Gunakan locale Indonesia

        $kelasData = [
            // Tingkat 10
            ['nama_kelas' => 'X IPA 1', 'tingkat' => 10, 'jurusan' => 'IPA'],
            ['nama_kelas' => 'X IPA 2', 'tingkat' => 10, 'jurusan' => 'IPA'],
            ['nama_kelas' => 'X IPS 1', 'tingkat' => 10, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'X IPS 2', 'tingkat' => 10, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'X Bahasa', 'tingkat' => 10, 'jurusan' => 'Bahasa'],
            // Tingkat 11
            ['nama_kelas' => 'XI IPA 1', 'tingkat' => 11, 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XI IPA 2', 'tingkat' => 11, 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XI IPS 1', 'tingkat' => 11, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'XI IPS 2', 'tingkat' => 11, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'XI Bahasa', 'tingkat' => 11, 'jurusan' => 'Bahasa'],
             // Tingkat 12
            ['nama_kelas' => 'XII IPA 1', 'tingkat' => 12, 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XII IPA 2', 'tingkat' => 12, 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XII IPS 1', 'tingkat' => 12, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'XII IPS 2', 'tingkat' => 12, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'XII Bahasa', 'tingkat' => 12, 'jurusan' => 'Bahasa'],
        ];

        foreach ($kelasData as $kelas) {
            Kelas::create([
                'nama_kelas' => $kelas['nama_kelas'],
                'tingkat' => $kelas['tingkat'],
                'jurusan' => $kelas['jurusan'],
                // wali_kelas_id akan diisi nanti setelah user guru dibuat
            ]);
        }
    }
}