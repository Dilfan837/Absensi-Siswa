<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ZieApiService;
use Illuminate\Support\Facades\Log;

try {
    echo "Testing ZieApiService...\n";
    
    $service = new ZieApiService();
    
    // Check Config
    $baseUrl = env('ZIEAPI_BASE_URL');
    $tahun = env('ZIEAPI_TAHUN');
    echo "Config: BaseURL={$baseUrl}, Tahun={$tahun}\n";
    
    echo "Fetching Kelas...\n";
    $kelas = $service->getKelas();
    
    if ($kelas === null) {
        echo "FAILED to fetch Kelas.\n";
        // Check log file content programmatically since we can't easily see it
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $lastLines = array_slice($lines, -20);
            echo "Last 20 log lines:\n";
            foreach ($lastLines as $line) {
                echo $line;
            }
        }
    } else {
        echo "SUCCESS! Fetched " . count($kelas) . " kelas.\n";
        print_r(array_slice($kelas, 0, 1)); // Print first item
    }

} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
