<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id(); // Hanya akan ada 1 baris
            $table->decimal('school_latitude', 10, 7);
            $table->decimal('school_longitude', 10, 7);
            $table->integer('allowed_radius_meters')->default(100); // Default 100 meter
            $table->time('attendance_start_time')->default('07:00:00');
            $table->time('attendance_end_time')->default('16:00:00');
            $table->time('late_threshold_time')->default('07:15:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};