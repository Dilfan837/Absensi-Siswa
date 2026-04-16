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
        Schema::create('api_gurus', function (Blueprint $table) {
            $table->id();
            $table->string('guru_id')->unique(); // UUID dari API
            $table->string('nama', 150);
            $table->string('nuptk', 50)->nullable();
            $table->string('nip', 50)->nullable();
            $table->string('jenis_kelamin', 1);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('nik', 50)->nullable();
            
            $table->string('email', 100)->nullable();
            $table->string('no_hp', 20)->nullable();
            
            // Fiksasi State
            $table->boolean('is_activated')->default(false);
            // Kalo udah aktivasi simpan foreign key guru nya
            $table->foreignId('id_guru_lokal')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_gurus');
    }
};
