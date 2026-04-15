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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluator_id')->constrained('users', 'id_user')->onDelete('cascade');
            $table->foreignId('evaluatee_id')->constrained('users', 'id_user')->onDelete('cascade');
            $table->string('context_type')->nullable(); // e.g., 'kelas', 'mata_pelajaran'
            $table->unsignedBigInteger('context_id')->nullable();
            $table->date('assessment_date');
            $table->string('period'); // e.g., 'Minggu 1, Bulan Maret 2024'
            $table->text('general_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
