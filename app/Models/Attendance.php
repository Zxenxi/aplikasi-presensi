<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'tanggal', 'jam_masuk', 'status',
        'latitude', 'longitude', 'selfie_path', 'is_location_valid',
        'remarks'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_location_valid' => 'boolean',
    ];

    /**
     * Relasi ke User yang memiliki data presensi ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}