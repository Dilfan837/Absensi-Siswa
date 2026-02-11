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
        Schema::create('api_sync_logs', function (Blueprint $table) {
            $table->id();
            
            // Jenis sync (siswa/guru/kelas)
            $table->string('api_type', 20);
            
            // Statistik
            $table->integer('records_fetched')->default(0); // Total dari API
            $table->integer('records_created')->default(0); // Berapa yang baru
            $table->integer('records_updated')->default(0); // Berapa yang di-update
            $table->integer('records_failed')->default(0);  // Berapa yang gagal
            
            // Error details (JSON)
            $table->json('error_details')->nullable();
            
            // Status: success, partial, failed
            $table->string('status', 20);
            
            // Timing
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable(); // Berapa lama prosesnya
            
            // User yang trigger (null jika auto via cron)
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users', 'id_user')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_sync_logs');
    }
};
