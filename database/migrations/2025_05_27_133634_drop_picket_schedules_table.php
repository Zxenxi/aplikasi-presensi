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
        Schema::dropIfExists('picket_schedules');
    }

    /**
     * Reverse the migrations.
     * (Opsional: Anda bisa membuat ulang tabel jika rollback diperlukan,
     * tapi biasanya untuk penghapusan, dropIfExists sudah cukup di 'up'
     * dan 'down' bisa dikosongkan atau membuat ulang struktur jika sangat penting).
     */
    public function down(): void
    {
        // Jika Anda ingin bisa rollback dan membuat ulang tabel:
        /*
        Schema::create('picket_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('duty_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            // $table->unique(['user_id', 'duty_date']); // Jika ada sebelumnya
        });
        */
        // Atau biarkan kosong jika penghapusan bersifat final
    }
};