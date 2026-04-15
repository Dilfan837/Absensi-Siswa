<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            // Hapus kolom string lama
            $table->dropColumn('mata_pelajaran');
            // Tambahkan foreign key baru
            $table->foreignId('id_mapel')->nullable()->after('status_aktif')->constrained('mata_pelajarans', 'id_mapel')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            // Drop foreign key & kolom
            $table->dropForeign(['id_mapel']);
            $table->dropColumn('id_mapel');
            // Kembalikan kolom lama
            $table->string('mata_pelajaran', 100)->nullable()->after('status_aktif');
        });
    }
};
