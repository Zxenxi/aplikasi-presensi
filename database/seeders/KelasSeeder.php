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
        $kelasData = [
            ['nama_kelas' => '11 TJKT', 'tingkat' => 11, 'jurusan' => 'TJKT'],
            ['nama_kelas' => '11 Farmasi', 'tingkat' => 11, 'jurusan' => 'Farmasi'],
        ];

        foreach ($kelasData as $kelas) {
            Kelas::create([
                'nama_kelas' => $kelas['nama_kelas'],
                'tingkat' => $kelas['tingkat'],
                'jurusan' => $kelas['jurusan'],
            ]);
        }
    }
}