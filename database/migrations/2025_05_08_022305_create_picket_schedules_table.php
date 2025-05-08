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
        Schema::create('picket_schedules', function (Blueprint $table) {
            $table->id();
            // Foreign key ke tabel users (petugas yang dijadwalkan)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // Tanggal bertugas
            $table->date('duty_date');
            // Catatan tambahan (misal: fokus area, tugas khusus)
            $table->text('notes')->nullable();
            $table->timestamps();

            // Opsional: Pastikan satu user tidak bisa dijadwal di tanggal yang sama lebih dari sekali
            // $table->unique(['user_id', 'duty_date']);

             // Opsional: Pastikan hanya satu user yang bertugas per hari (jika aturannya begitu)
             // $table->unique('duty_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picket_schedules');
    }
};