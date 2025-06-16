<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_piket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->comment('ID Guru yang bertugas')
                  ->constrained('users') // Merujuk ke tabel users
                  ->onDelete('cascade'); // Jika user (guru) dihapus, jadwal piketnya ikut terhapus
            $table->tinyInteger('hari_ke')->comment('1:Senin, 2:Selasa, ..., 7:Minggu (sesuai Carbon::dayOfWeekIso)');
            $table->time('jam_mulai')->nullable()->comment('Format HH:MM');
            $table->time('jam_selesai')->nullable()->comment('Format HH:MM');
            $table->text('keterangan_tugas')->nullable();
            $table->timestamps();

            // Indeks untuk pencarian
            $table->index(['user_id', 'hari_ke']);

            // Unique constraint: Seorang guru hanya boleh memiliki satu entri jadwal piket per hari_ke.
            // Jika Anda ingin mengizinkan satu guru memiliki beberapa slot waktu berbeda di hari yang sama,
            // maka unique constraint ini perlu diubah, misal: unique(['user_id', 'hari_ke', 'jam_mulai']).
            // Untuk jadwal piket standar, satu tugas per hari per guru biasanya cukup.
            $table->unique(['user_id', 'hari_ke'], 'user_hari_piket_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_piket');
    }
};