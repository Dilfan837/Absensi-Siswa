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
        Schema::table('guru', function (Blueprint $table) {
            // Kita tambahkan pengecekan kolom agar aman
            if (!Schema::hasColumn('guru', 'guru_id')) {
                $table->uuid('guru_id')->nullable()->unique()->after('id_user');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (Schema::hasColumn('guru', 'guru_id')) {
                $table->dropColumn('guru_id');
            }
        });
    }
};
