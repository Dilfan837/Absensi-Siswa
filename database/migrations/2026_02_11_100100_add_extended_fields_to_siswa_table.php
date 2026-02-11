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
        Schema::table('siswa', function (Blueprint $table) {
            // UUID dari API pusat - CRITICAL untuk sync
            $table->uuid('peserta_didik_id')->nullable()->unique()->after('id_siswa');
            
            // Identitas tambahan
            $table->string('nisn', 20)->nullable()->unique()->after('nis');
            $table->string('nik', 20)->nullable()->after('nisn');
            $table->string('no_induk', 50)->nullable()->after('nik');
            
            // Biodata lengkap
            $table->string('tempat_lahir', 100)->nullable()->after('jenis_kelamin');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->integer('agama_id')->nullable()->after('tanggal_lahir');
            $table->integer('anak_ke')->nullable()->after('agama_id');
            
            // Kontak & Alamat
            $table->string('email', 100)->nullable()->after('anak_ke');
            $table->string('no_telp', 20)->nullable()->after('email');
            $table->text('alamat')->nullable()->after('no_telp');
            $table->string('rt', 5)->nullable()->after('alamat');
            $table->string('rw', 5)->nullable()->after('rt');
            $table->string('desa_kelurahan', 100)->nullable()->after('rw');
            $table->string('kecamatan', 100)->nullable()->after('desa_kelurahan');
            $table->string('kode_pos', 10)->nullable()->after('kecamatan');
            $table->string('kode_wilayah', 20)->nullable()->after('kode_pos');
            
            // Akademik
            $table->string('sekolah_asal', 200)->nullable()->after('kode_wilayah');
            $table->string('diterima_kelas_smk', 50)->nullable()->after('sekolah_asal');
            $table->string('nama_rombel', 100)->nullable()->after('diterima_kelas_smk');
            $table->string('rombel_id', 50)->nullable()->after('nama_rombel');
            
            // Data Orang Tua
            $table->string('nama_ayah', 150)->nullable()->after('rombel_id');
            $table->string('nama_ibu', 150)->nullable()->after('nama_ayah');
            $table->integer('kerja_ayah_id')->nullable()->after('nama_ibu');
            $table->integer('kerja_ibu_id')->nullable()->after('kerja_ayah_id');
            
            // Data Wali
            $table->string('nama_wali', 150)->nullable()->after('kerja_ibu_id');
            $table->text('alamat_wali')->nullable()->after('nama_wali');
            $table->string('telp_wali', 20)->nullable()->after('alamat_wali');
            $table->integer('kerja_wali_id')->nullable()->after('telp_wali');
            
            // Metadata API
            $table->timestamp('last_synced_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn([
                'peserta_didik_id',
                'nisn',
                'nik',
                'no_induk',
                'tempat_lahir',
                'tanggal_lahir',
                'agama_id',
                'anak_ke',
                'email',
                'no_telp',
                'alamat',
                'rt',
                'rw',
                'desa_kelurahan',
                'kecamatan',
                'kode_pos',
                'kode_wilayah',
                'sekolah_asal',
                'diterima_kelas_smk',
                'nama_rombel',
                'rombel_id',
                'nama_ayah',
                'nama_ibu',
                'kerja_ayah_id',
                'kerja_ibu_id',
                'nama_wali',
                'alamat_wali',
                'telp_wali',
                'kerja_wali_id',
                'last_synced_at'
            ]);
        });
    }
};
