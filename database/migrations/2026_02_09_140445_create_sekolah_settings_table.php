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
        Schema::create('sekolah_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 7)->default(-6.8253015); // SMK Negeri 1 Cianjur
            $table->decimal('longitude', 10, 7)->default(107.1370937);
            $table->integer('radius_meter')->default(100); // Radius dalam meter
            $table->boolean('is_geofence_active')->default(true); // Toggle ON/OFF
            $table->timestamps();
        });

        // Seed data default
        \DB::table('sekolah_settings')->insert([
            'latitude' => -6.8253015,
            'longitude' => 107.1370937,
            'radius_meter' => 100,
            'is_geofence_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolah_settings');
    }
};
