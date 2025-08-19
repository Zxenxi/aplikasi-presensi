<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas');
            $table->integer('tingkat');
            $table->string('jurusan')->nullable();
            $table->timestamps();
            
            // $table->unsignedBigInteger('wali_kelas_id')->nullable(); // Hanya kolom, bukan constraint
        // $table->foreign('id')->references('kelas_id')->on('users')->onDelete('set null');
        // $table->foreign('id')->references('kelas_id')->on('users');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};