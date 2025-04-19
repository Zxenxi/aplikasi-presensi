<?php

// app/Models/User.php
// app/Models/Kelas.php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model {
    use HasFactory;
    protected $table = 'kelas'; // Eksplisit nama tabel
    protected $fillable = ['nama_kelas', 'tingkat', 'jurusan', 'wali_kelas_id'];

    public function students() { return $this->hasMany(User::class); }
    public function waliKelas() { return $this->belongsTo(User::class, 'wali_kelas_id'); }
}