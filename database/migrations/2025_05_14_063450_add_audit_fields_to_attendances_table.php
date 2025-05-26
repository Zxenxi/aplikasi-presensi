<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Kolom untuk menyimpan ID user yang melakukan update terakhir
            $table->foreignId('updated_by_user_id')->nullable()->after('is_location_valid')
                  ->comment('ID user (admin/petugas piket) yang terakhir mengubah rekaman')
                  ->constrained('users')->onDelete('set null'); // Jika user dihapus, ID di sini jadi NULL

            // Kolom untuk catatan/remarks dari admin atau petugas piket
            // Anda mungkin sudah memiliki kolom 'keterangan'. Jika ya, Anda bisa menyesuaikan ini
            // atau memutuskan untuk menggunakan 'remarks' sebagai standar baru.
            // Jika 'keterangan' sudah ada dan ingin diganti/dilengkapi:
            // $table->text('keterangan')->nullable()->change(); // Contoh jika ingin memastikan nullable & text
            $table->text('remarks')->nullable()->after('updated_by_user_id')
                  ->comment('Catatan atau keterangan dari admin/petugas piket');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Hati-hati dengan menghapus foreign key jika nama constraintnya custom
            // Laravel biasanya membuat nama constraint otomatis.
            // Cek nama constraint jika error: $table->dropForeign(['updated_by_user_id']);
            if (Schema::hasColumn('attendances', 'updated_by_user_id')) { // Cek sebelum drop
                // Perlu nama constraint yg benar jika tidak default
                // $table->dropForeign('attendances_updated_by_user_id_foreign'); // Contoh nama default
                $table->dropConstrainedForeignId('updated_by_user_id'); // Cara lebih aman di Laravel 9+
            }
            if (Schema::hasColumn('attendances', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};