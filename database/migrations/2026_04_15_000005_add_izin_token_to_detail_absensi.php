<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL tidak bisa langsung modify ENUM, jadi kita ubah via raw SQL
        // Kita juga menambahkan 'dispen' karena sepertinya ada data/fitur tersebut di controller
        DB::statement("ALTER TABLE detail_absensi MODIFY COLUMN status ENUM('hadir','alpha','izin','sakit','dispen','izin_token') NOT NULL DEFAULT 'alpha'");
    }

    public function down(): void
    {
        // Rollback: hapus izin_token dari enum
        DB::statement("ALTER TABLE detail_absensi MODIFY COLUMN status ENUM('hadir','alpha','izin','sakit','dispen') NOT NULL DEFAULT 'alpha'");
    }
};
