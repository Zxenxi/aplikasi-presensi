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

            // Kolom utama presensi
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->enum('status', ['Hadir', 'Telat', 'Izin', 'Sakit', 'Absen']);

            // Kolom untuk fitur GPS dan Foto
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('selfie_path')->nullable();
            $table->boolean('is_location_valid')->nullable();

            // Kolom audit yang dipindahkan dari file migrasi lain
            // $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Constraint unik agar user hanya bisa presensi sekali sehari
            $table->unique(['user_id', 'tanggal']);
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