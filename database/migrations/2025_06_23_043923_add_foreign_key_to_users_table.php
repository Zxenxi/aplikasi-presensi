<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     * Method ini akan menambahkan foreign key constraint.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan foreign key constraint ke kolom 'kelas_id'
            // yang merujuk ke kolom 'id' di tabel 'kelas'
            $table->foreign('kelas_id')
                  ->references('id')
                  ->on('kelas')
                  ->onDelete('set null'); // Jika kelas dihapus, set kelas_id di user menjadi NULL
        });
    }

    /**
     * Batalkan migrasi.
     * Method ini akan menghapus foreign key constraint.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key. Nama constraint defaultnya adalah 'users_kelas_id_foreign'
            $table->dropForeign(['kelas_id']);
        });
    }
};