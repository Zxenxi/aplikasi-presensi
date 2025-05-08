<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import BelongsTo

class PicketSchedule extends Model
{
    use HasFactory;

    protected $table = 'picket_schedules'; // Nama tabel

    protected $fillable = [
        'user_id',
        'duty_date',
        'notes',
    ];

    // Cast tipe data date
    protected $casts = [
        'duty_date' => 'date',
    ];

    /**
     * Mendapatkan user (petugas) yang terkait dengan jadwal ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}