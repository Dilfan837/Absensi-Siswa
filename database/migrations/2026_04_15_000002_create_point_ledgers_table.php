<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa', 'id_siswa')->onDelete('cascade');
            $table->enum('transaction_type', ['EARN', 'PENALTY', 'REWARD', 'SPEND']);
            $table->integer('amount'); // positif atau negatif
            $table->integer('current_balance'); // saldo SETELAH transaksi ini (audit trail)
            $table->text('description');
            // Relasi opsional ke sesi absensi yang memicu
            $table->unsignedBigInteger('id_absensi')->nullable();
            $table->foreign('id_absensi')->references('id_absensi')->on('absensi')->nullOnDelete();
            // Relasi opsional ke guru yang input manual
            $table->unsignedBigInteger('id_guru')->nullable();
            $table->foreign('id_guru')->references('id_guru')->on('guru')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_ledgers');
    }
};
