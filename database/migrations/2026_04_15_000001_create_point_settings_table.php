<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->integer('value');
            $table->string('label');
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id_user')->nullOnDelete();
            $table->timestamps();
        });

        // Data awal konfigurasi poin
        $now = now();
        DB::table('point_settings')->insert([
            ['key' => 'poin_hadir',     'value' => 2,  'label' => 'Poin per Kehadiran',              'created_at' => $now, 'updated_at' => $now],
            ['key' => 'poin_alpha',     'value' => -1, 'label' => 'Penalti per Alpha',               'created_at' => $now, 'updated_at' => $now],
            ['key' => 'max_poin_guru',  'value' => 5,  'label' => 'Maks Poin Manual Guru per Sesi',  'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('point_settings');
    }
};
