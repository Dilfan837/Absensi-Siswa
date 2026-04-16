<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa', 'id_siswa')->onDelete('cascade');
            $table->foreignId('id_item')->constrained('flexibility_items')->onDelete('cascade');
            $table->enum('status', ['AVAILABLE', 'USED', 'EXPIRED'])->default('AVAILABLE');
            // Sesi absensi di mana token ini dipakai (hanya terisi untuk BEBAS_ALPHA)
            $table->unsignedBigInteger('id_absensi_used')->nullable();
            $table->foreign('id_absensi_used')->references('id_absensi')->on('absensi')->nullOnDelete();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tokens');
    }
};
