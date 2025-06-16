<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Untuk helper nama hari

class JadwalPiket extends Model
{
    use HasFactory;

    protected $table = 'jadwal_piket';

    protected $fillable = [
        'user_id',
        'hari_ke',
        'jam_mulai',
        'jam_selesai',
        'keterangan_tugas',
    ];

    protected $casts = [
        'hari_ke' => 'integer',
        // jam_mulai & jam_selesai bisa string jika formatnya HH:MM atau HH:MM:SS
        // atau 'jam_mulai' => 'datetime:H:i', // Hati-hati dengan bagian tanggal jika pakai ini
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Accessor untuk mendapatkan nama hari
    public function getNamaHariAttribute(): string
    {
        $days = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
            5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];
        return $days[$this->hari_ke] ?? 'Tidak Valid';
    }

    // Helper statis untuk nama hari (bisa dipanggil dari view tanpa objek)
    public static function getDayNameFromNumber(int $dayNumber): string
    {
        $days = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
            5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];
        return $days[$dayNumber] ?? 'Tidak Valid';
    }
}