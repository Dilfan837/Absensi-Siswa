<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('detail_absensi', function (Blueprint $table) {
            $table->id('id_detail');
            // Relasi ke tabel absensi (Induk)
            $table->foreignId('id_absensi')->constrained('absensi', 'id_absensi')->onDelete('cascade');
            // Relasi ke tabel siswa
            $table->foreignId('id_siswa')->constrained('siswa', 'id_siswa')->onDelete('cascade');

            $table->timestamp('waktu_scan')->nullable();
            // Status awal otomatis 'alpha'
            $table->enum('status', ['hadir', 'alpha', 'izin', 'sakit'])->default('alpha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_absensi');
    }
};
