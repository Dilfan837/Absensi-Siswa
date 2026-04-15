<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_slots', function (Blueprint $table) {
            $table->id();
            $table->integer('jam_ke');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->enum('hari_berlaku', ['senin-kamis', 'jumat', 'semua'])->default('semua');
            $table->boolean('is_istirahat')->default(false);
            $table->timestamps();
        });

        // Seed default slots: Senin-Kamis
        $slotsRegular = [
            ['jam_ke' => 1, 'jam_mulai' => '06:30', 'jam_selesai' => '07:15', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => false],
            ['jam_ke' => 2, 'jam_mulai' => '07:15', 'jam_selesai' => '08:00', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => false],
            ['jam_ke' => 3, 'jam_mulai' => '08:00', 'jam_selesai' => '08:45', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => false],
            ['jam_ke' => 4, 'jam_mulai' => '08:45', 'jam_selesai' => '09:30', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => false],
            ['jam_ke' => 0, 'jam_mulai' => '09:30', 'jam_selesai' => '10:00', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => true],
            ['jam_ke' => 5, 'jam_mulai' => '10:00', 'jam_selesai' => '10:45', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => false],
            ['jam_ke' => 6, 'jam_mulai' => '10:45', 'jam_selesai' => '11:30', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => false],
            ['jam_ke' => 7, 'jam_mulai' => '11:30', 'jam_selesai' => '12:15', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => false],
            ['jam_ke' => 0, 'jam_mulai' => '12:15', 'jam_selesai' => '13:00', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => true],
            ['jam_ke' => 8, 'jam_mulai' => '13:00', 'jam_selesai' => '13:45', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => false],
            ['jam_ke' => 9, 'jam_mulai' => '13:45', 'jam_selesai' => '14:30', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => false],
            ['jam_ke' => 10, 'jam_mulai' => '14:30', 'jam_selesai' => '15:15', 'hari_berlaku' => 'senin-kamis', 'is_istirahat' => false],
        ];

        // Seed default slots: Jumat
        $slotsJumat = [
            ['jam_ke' => 1, 'jam_mulai' => '06:30', 'jam_selesai' => '07:15', 'hari_berlaku' => 'jumat', 'is_istirahat' => false],
            ['jam_ke' => 2, 'jam_mulai' => '07:15', 'jam_selesai' => '08:00', 'hari_berlaku' => 'jumat', 'is_istirahat' => false],
            ['jam_ke' => 3, 'jam_mulai' => '08:00', 'jam_selesai' => '08:45', 'hari_berlaku' => 'jumat', 'is_istirahat' => false],
            ['jam_ke' => 0, 'jam_mulai' => '08:45', 'jam_selesai' => '09:15', 'hari_berlaku' => 'jumat', 'is_istirahat' => true],
            ['jam_ke' => 4, 'jam_mulai' => '09:15', 'jam_selesai' => '10:00', 'hari_berlaku' => 'jumat', 'is_istirahat' => false],
            ['jam_ke' => 5, 'jam_mulai' => '10:00', 'jam_selesai' => '10:45', 'hari_berlaku' => 'jumat', 'is_istirahat' => false],
        ];

        $now = now();
        foreach (array_merge($slotsRegular, $slotsJumat) as $slot) {
            DB::table('jadwal_slots')->insert(array_merge($slot, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_slots');
    }
};
