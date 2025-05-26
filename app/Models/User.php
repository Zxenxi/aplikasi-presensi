<?php

// app/Models/User.php
namespace App\Models;

use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\Attendance;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; // Jika pakai Sanctum

class User extends Authenticatable
{   
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // <-- Tambahkan role
        'kelas_id', // <-- Tambahkan kelas_id
    ];

    protected $hidden = [ 'password', 'remember_token', ];
    protected $casts = [ 'email_verified_at' => 'datetime', ];

    // Relasi ke Presensi
    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

    // Relasi ke Kelas (jika user adalah siswa)
    public function kelas() {
        return $this->belongsTo(Kelas::class);
    }

    // Relasi ke Kelas (jika user adalah wali kelas)
    public function kelasWali() {
        return $this->hasOne(Kelas::class, 'wali_kelas_id');
    }

    // Helper Methods (Opsional)
    public function isSuperAdmin()
    {
        return $this->role === 'Super Admin';
    }

    /**
     * Check if the user has the "Petugas Piket" role.
     *
     * @return bool
     */
 public function isPetugasPiket(): bool // <-- GANTI FUNGSI LAMA DENGAN INI
    {
        // Kondisi 1: User memang punya peran 'Petugas Piket' secara eksplisit.
        if ($this->role === 'Petugas Piket') {
            return true;
        }

        // Kondisi 2: User adalah 'Guru' DAN punya jadwal piket hari ini.
        if ($this->role === 'Guru') {
            // Dapatkan hari ini dalam format angka (1 untuk Senin, 2 untuk Selasa, dst.)
            $hariIni = Carbon::now(config('app.timezone'))->dayOfWeekIso;

            // Cek apakah ada jadwal piket untuk guru ini pada hari ini di database.
            return JadwalPiket::where('user_id', $this->id)
                               ->where('hari_ke', $hariIni)
                               ->exists();
        }

        // Jika bukan keduanya, maka bukan petugas piket.
        return false;
    }
     public function jadwal()
    {
        return $this->role === 'Petugas Piket';
    }
    // public function isSuperAdmin(): bool { return $this->role === 'Super Admin'; }
    // public function isPetugasPiket(): bool { return $this->role === 'Petugas Piket'; }
    public function isGuru(): bool { return $this->role === 'Guru'; }
    public function isSiswa(): bool { return $this->role === 'Siswa'; }
    
    public function jadwalPiket()
    {
        return $this->hasMany(JadwalPiket::class, 'user_id');
    }
}