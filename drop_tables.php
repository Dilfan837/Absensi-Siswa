<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    echo "Disabling foreign key checks...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    echo "Dropping tables...\n";
    Schema::dropIfExists('assessment_details');
    Schema::dropIfExists('assessments');
    Schema::dropIfExists('assessment_categories');
    
    echo "Re-enabling foreign key checks...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    echo "Cleaning up migrations table...\n";
    DB::table('migrations')->where('migration', 'like', '%assessment%')->delete();

    echo "Done resolving table conflicts.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
