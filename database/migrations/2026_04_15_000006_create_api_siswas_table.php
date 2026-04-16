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
        Schema::create('api_siswas', function (Blueprint $table) {
            $table->id();
            $table->uuid('peserta_didik_id')->nullable()->unique();
            $table->string('sekolah_id')->nullable();
            $table->string('nama')->nullable();
            $table->string('no_induk')->nullable();
            $table->string('nisn')->nullable();
            $table->string('nik')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama_id')->nullable();
            $table->string('anak_ke')->nullable();
            
            // Address
            $table->text('alamat')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('desa_kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('no_telp')->nullable();
            
            // Education
            $table->string('sekolah_asal')->nullable();
            $table->string('diterima_kelas')->nullable();
            $table->string('diterima_kelas_smk')->nullable();
            $table->string('rombel_id')->nullable();
            $table->string('nama_rombel')->nullable();
            
            // Parents
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('nama_wali')->nullable();
            
            // System flags
            // is_activated indicates if the student has been linked/transferred to main `siswa` table
            $table->boolean('is_activated')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_siswas');
    }
};
