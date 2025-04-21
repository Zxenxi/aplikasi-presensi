<?php

// app/Models/User.php
namespace App\Models;

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
    public function isPetugasPiket()
    {
        return $this->role === 'Petugas Piket';
    }
    // public function isSuperAdmin(): bool { return $this->role === 'Super Admin'; }
    // public function isPetugasPiket(): bool { return $this->role === 'Petugas Piket'; }
    public function isGuru(): bool { return $this->role === 'Guru'; }
    public function isSiswa(): bool { return $this->role === 'Siswa'; }
    
}