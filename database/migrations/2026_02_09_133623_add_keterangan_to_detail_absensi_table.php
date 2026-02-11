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
        Schema::table('detail_absensi', function (Blueprint $table) {
            $table->string('status')->change(); // Ubah enum jadi string agar fleksibel
            $table->text('keterangan')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_absensi', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
