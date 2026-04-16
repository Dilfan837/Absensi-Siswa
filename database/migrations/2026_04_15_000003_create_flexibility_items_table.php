<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flexibility_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->enum('item_type', ['BEBAS_ALPHA', 'WFH', 'IZIN_MENDADAK', 'TOLERANSI_TELAT', 'CUSTOM']);
            // TRUE = hanya bisa dipakai saat sesi masih AKTIF (contoh: BEBAS_ALPHA)
            // FALSE = bisa dipakai kapan saja meski sesi sudah tutup
            $table->boolean('requires_active_session')->default(false);
            $table->integer('point_cost'); // harga dalam poin
            $table->integer('stock_limit')->nullable(); // batas beli per bulan, null = unlimited
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users', 'id_user')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flexibility_items');
    }
};
