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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relasi ke users
            $table->date('tanggal');
            $table->time('jam_masuk');
            $table->enum('status', ['Hadir', 'Telat', 'Izin', 'Sakit', 'Absen']);
            $table->decimal('latitude', 10, 7)->nullable(); // Presisi untuk GPS
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('selfie_path')->nullable(); // Path ke file gambar
            $table->boolean('is_location_valid')->nullable(); // Status validasi lokasi
            $table->timestamps();
    
            $table->unique(['user_id', 'tanggal']); // User hanya bisa presensi sekali sehari
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};