<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

 class UserSeeder extends Seeder {
        public function run() {
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@sekolah.sch.id', // Ganti email admin
                'password' => Hash::make('password'), // Ganti password default
                'role' => 'Super Admin',
            ]);
            // Tambahkan user lain jika perlu (contoh Guru, Siswa)
            User::create([
                'name' => 'Contoh Guru',
                'email' => 'guru@sekolah.sch.id',
                'password' => Hash::make('password'),
                'role' => 'Guru',
            ]);
             User::create([
                'name' => 'Contoh Siswa',
                'email' => 'siswa@sekolah.sch.id',
                'password' => Hash::make('password'),
                'role' => 'Siswa',
                 // 'kelas_id' => 1, // Jika tabel kelas sudah ada isinya
            ]);
        }
    }