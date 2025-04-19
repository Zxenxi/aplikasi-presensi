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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas');
            $table->integer('tingkat'); // 10, 11, 12
            $table->string('jurusan')->nullable(); // IPA, IPS, dll.
            $table->unsignedBigInteger('wali_kelas_id')->nullable(); // Foreign key ke users (guru)
            $table->timestamps();
    
            // Opsional: Foreign key constraint jika tabel users sudah ada
            // $table->foreign('wali_kelas_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};