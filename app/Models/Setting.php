<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model {
    use HasFactory;
    protected $fillable = [
        'school_latitude', 'school_longitude', 'allowed_radius_meters',
        'attendance_start_time', 'attendance_end_time', 'late_threshold_time'
    ];
}