<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Cleanup: Hapus tabel dan kolom yang tidak terpakai (sisa rencana API).
     */
    public function up(): void
    {
        // 1. Hapus tabel api_sync_logs (tidak terpakai sama sekali)
        Schema::dropIfExists('api_sync_logs');

        // 2. Hapus kolom API-specific di tabel siswa
        Schema::table('siswa', function (Blueprint $table) {
            $columns = [];
            $columnList = [
                'peserta_didik_id',
                'rombel_id',
                'nama_rombel',
                'diterima_kelas_smk',
                'sekolah_asal',
                'kerja_ayah_id',
                'kerja_ibu_id',
                'kerja_wali_id',
                'last_synced_at',
            ];
            foreach ($columnList as $col) {
                if (Schema::hasColumn('siswa', $col)) {
                    $columns[] = $col;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        // 3. Hapus kolom API-specific di tabel guru
        Schema::table('guru', function (Blueprint $table) {
            $columns = [];
            $columnList = [
                'guru_id',
                'jenis_ptk_id',
                'status_kepegawaian_id',
                'sekolah_id',
                'last_synced_at',
            ];
            foreach ($columnList as $col) {
                if (Schema::hasColumn('guru', $col)) {
                    $columns[] = $col;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate api_sync_logs
        Schema::create('api_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('api_type', 20);
            $table->integer('records_fetched')->default(0);
            $table->integer('records_created')->default(0);
            $table->integer('records_updated')->default(0);
            $table->integer('records_failed')->default(0);
            $table->json('error_details')->nullable();
            $table->string('status', 20);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users', 'id_user')->onDelete('set null');
            $table->timestamps();
        });

        // Re-add siswa columns
        Schema::table('siswa', function (Blueprint $table) {
            $table->uuid('peserta_didik_id')->nullable()->unique()->after('id_siswa');
            $table->string('rombel_id', 50)->nullable();
            $table->string('nama_rombel', 100)->nullable();
            $table->string('diterima_kelas_smk', 50)->nullable();
            $table->string('sekolah_asal', 200)->nullable();
            $table->integer('kerja_ayah_id')->nullable();
            $table->integer('kerja_ibu_id')->nullable();
            $table->integer('kerja_wali_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();
        });

        // Re-add guru columns
        Schema::table('guru', function (Blueprint $table) {
            $table->uuid('guru_id')->unique()->after('id_user');
            $table->integer('jenis_ptk_id')->nullable();
            $table->integer('status_kepegawaian_id')->nullable();
            $table->integer('sekolah_id')->nullable();
            $table->timestamp('last_synced_at')->nullable();
        });
    }
};
