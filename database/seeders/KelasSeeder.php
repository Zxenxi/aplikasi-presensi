<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kelas;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data kelas sudah bagus, tidak perlu diubah
        $kelasData = [
            // Tingkat 10
            ['nama_kelas' => 'IPA 1', 'tingkat' => 10, 'jurusan' => 'IPA'],
            ['nama_kelas' => 'IPA 2', 'tingkat' => 10, 'jurusan' => 'IPA'],
            ['nama_kelas' => 'IPS 1', 'tingkat' => 10, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'IPS 2', 'tingkat' => 10, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'Bahasa', 'tingkat' => 10, 'jurusan' => 'Bahasa'],
            // Tingkat 11
            ['nama_kelas' => 'IPA 1', 'tingkat' => 11, 'jurusan' => 'IPA'],
            ['nama_kelas' => 'IPA 2', 'tingkat' => 11, 'jurusan' => 'IPA'],
            ['nama_kelas' => 'IPS 1', 'tingkat' => 11, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'IPS 2', 'tingkat' => 11, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'Bahasa', 'tingkat' => 11, 'jurusan' => 'Bahasa'],
             // Tingkat 12
             ['nama_kelas' => 'IPA 1', 'tingkat' => 12, 'jurusan' => 'IPA'],
             ['nama_kelas' => 'IPA 2', 'tingkat' => 12, 'jurusan' => 'IPA'],
             ['nama_kelas' => 'IPS 1', 'tingkat' => 12, 'jurusan' => 'IPS'],
             ['nama_kelas' => 'IPS 2', 'tingkat' => 12, 'jurusan' => 'IPS'],
            ['nama_kelas' => 'Bahasa', 'tingkat' => 12, 'jurusan' => 'Bahasa'],
        ];

        foreach ($kelasData as $kelas) {
            // Pastikan tidak ada 'wali_kelas_id' di sini
            Kelas::create([
                'nama_kelas' => $kelas['nama_kelas'],
                'tingkat' => $kelas['tingkat'],
                'jurusan' => $kelas['jurusan'],
            ]);
        }
    }
}