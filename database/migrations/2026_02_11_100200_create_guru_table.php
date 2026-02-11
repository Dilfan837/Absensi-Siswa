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
        Schema::create('guru', function (Blueprint $table) {
            $table->id('id_guru');
            $table->foreignId('id_user')->nullable()->constrained('users', 'id_user')->onDelete('set null');
            
            // UUID dari API - CRITICAL untuk sync
            $table->uuid('guru_id')->unique();
            
            // Identitas
            $table->string('nuptk', 20)->nullable()->unique();
            $table->string('nip', 20)->nullable()->unique();
            $table->string('nik', 20)->nullable();
            $table->string('nama', 150);
            
            // Biodata
            $table->string('jenis_kelamin', 1); // L/P
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->integer('agama_id')->nullable();
            
            // Kepegawaian
            $table->integer('jenis_ptk_id')->nullable();
            $table->integer('status_kepegawaian_id')->nullable();
            $table->integer('sekolah_id')->nullable();
            
            // Kontak & Alamat
            $table->string('email', 100)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('desa_kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kode_wilayah', 20)->nullable();
            $table->string('kode_pos', 10)->nullable();
            
            // Media
            $table->string('photo')->nullable();
            
            // Status
            $table->boolean('status_aktif')->default(true);
            
            // Metadata
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};
