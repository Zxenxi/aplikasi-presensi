<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model {
    use HasFactory;
    protected $table = 'kelas'; // Eksplisit nama tabel
    protected $fillable = ['nama_kelas', 'tingkat', 'jurusan'];
    // protected $fillable = ['nama_kelas', 'tingkat', 'jurusan', 'wali_kelas_id'];

    // public function students() { return $this->hasMany(User::class); }
    public function students()
    {
        return $this->hasMany(User::class, 'kelas_id'); // Pastikan foreign key 'kelas_id' benar
    }
}