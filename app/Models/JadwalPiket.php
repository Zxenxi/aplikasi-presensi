<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Jangan lupa import Carbon

class JadwalPiket extends Model
{
    use HasFactory;

    // Tentukan nama tabel jika tidak mengikuti konvensi Laravel
    protected $table = 'jadwal_piket';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'user_id',
        'hari_ke',
        'jam_mulai',
        'jam_selesai',
        'keterangan_tugas',
    ];

    /**
     * Relasi ke model User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Mendapatkan nama hari dari angka hari_ke.
     */
    public function getNamaHariAttribute()
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        return $days[$this->hari_ke] ?? 'Tidak Diketahui';
    }

    /**
     * ACCESSOR: Format jam_mulai saat diakses
     * Ini akan mengubah '08:30:00' menjadi '08:30'
     */
    public function getJamMulaiFormattedAttribute()
    {
        // Cek jika jam_mulai tidak null sebelum memformat
        return $this->jam_mulai ? Carbon::parse($this->jam_mulai)->format('H:i') : '-';
    }

    /**
     * ACCESSOR: Format jam_selesai saat diakses
     */
    public function getJamSelesaiFormattedAttribute()
    {
        // Cek jika jam_selesai tidak null sebelum memformat
        return $this->jam_selesai ? Carbon::parse($this->jam_selesai)->format('H:i') : '-';
    }
}