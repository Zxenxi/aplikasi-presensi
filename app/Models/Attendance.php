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
        'updated_by_user_id', 'remarks' 
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_location_valid' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke user yang melakukan update (Admin/Petugas Piket)
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}