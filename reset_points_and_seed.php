<?php

use Illuminate\Support\Facades\DB;
use App\Models\FlexibilityItem;
use App\Models\PointLedger;
use App\Models\UserToken;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Mulai proses reset poin dan seed dummy...\n";

DB::transaction(function () {
    // 1. Reset Poin dan Token karena tadi ngebug gara2 timer loop
    echo "1. Resetting database log poin...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    DB::table('point_ledgers')->truncate();
    DB::table('user_tokens')->truncate();
    DB::table('flexibility_items')->truncate();
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
    // 2. Tambahkan Data Dummy untuk Marketplace Token Admin
    echo "2. Seeding Item Dummy Marketplace...\n";
    $dummyItems = [
        [
            'item_name' => 'Bebas Alpha 1x',
            'description' => 'Gunakan ini saat sesi absen sedang berjalan untuk mengubah statusmu dari Alpha menjadi Izin Token.',
            'item_type' => 'BEBAS_ALPHA',
            'requires_active_session' => true,
            'point_cost' => 50,
            'stock_limit' => 2,
            'is_active' => true,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'item_name' => 'WFH Bebas Tugas',
            'description' => 'Kamu diberikan kebebasan untuk Remote dari rumah.',
            'item_type' => 'WFH',
            'requires_active_session' => false,
            'point_cost' => 120,
            'stock_limit' => 1,
            'is_active' => true,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'item_name' => 'Kupon Toleransi Telat',
            'description' => 'Telat tanpa dicatat di buku pelanggaran.',
            'item_type' => 'TOLERANSI_TELAT',
            'requires_active_session' => false,
            'point_cost' => 30,
            'stock_limit' => 3,
            'is_active' => true,
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ];

    DB::table('flexibility_items')->insert($dummyItems);
    
    echo "Semua selesai! Database poin sudah suci kembali dan marketplace sudah punya isi.\n";
});
