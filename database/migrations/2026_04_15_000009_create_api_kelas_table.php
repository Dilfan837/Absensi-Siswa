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
        Schema::create('api_kelas', function (Blueprint $table) {
            $table->id();
            $table->string('kelas_id')->unique(); // ID dari API (misal 579)
            $table->string('nama', 150);
            $table->string('jurusan_api', 100);

            // Fiksasi State
            $table->boolean('is_activated')->default(false);
            $table->foreignId('id_kelas_lokal')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_kelas');
    }
};
