<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id();
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat']);
            $table->foreignId('jadwal_slot_id')->constrained('jadwal_slots')->onDelete('cascade');
            $table->unsignedBigInteger('id_guru')->nullable();
            $table->unsignedBigInteger('id_kelas');
            $table->unsignedBigInteger('id_mapel')->nullable();
            $table->timestamps();

            $table->foreign('id_guru')->references('id_guru')->on('guru')->onDelete('set null');
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
            $table->foreign('id_mapel')->references('id_mapel')->on('mata_pelajarans')->onDelete('set null');

            // 1 kelas hanya 1 pelajaran per slot per hari
            $table->unique(['hari', 'jadwal_slot_id', 'id_kelas'], 'jadwal_unique_slot_kelas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwals');
    }
};
