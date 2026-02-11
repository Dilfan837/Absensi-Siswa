<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Migrate existing data to new 'locations' table
        $setting = DB::table('sekolah_settings')->first();
        if ($setting) {
            DB::table('locations')->insert([
                'nama_lokasi' => 'Kampus Utama',
                'latitude' => $setting->latitude,
                'longitude' => $setting->longitude,
                'radius_meter' => $setting->radius_meter,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // 2. Remove columns from 'sekolah_settings'
        Schema::table('sekolah_settings', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_meter']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah_settings', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->default(-6.8253015);
            $table->decimal('longitude', 10, 7)->default(107.1370937);
            $table->integer('radius_meter')->default(100);
        });
    }
};
