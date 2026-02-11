<?php

function logToFile($msg) {
    echo $msg;
    file_put_contents('debug_result.txt', $msg, FILE_APPEND);
}

// Clear previous log
file_put_contents('debug_result.txt', "--- Debug Start ---\n");

logToFile("Script started...\n");

require __DIR__.'/vendor/autoload.php';

logToFile("Autoload loaded...\n");

$app = require_once __DIR__.'/bootstrap/app.php';

logToFile("App instance created...\n");

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

logToFile("Kernel bootstrapped...\n");

use App\Services\ZieApiService;

try {
    logToFile("Instantiating ZieApiService...\n");
    $service = new ZieApiService();
    
    logToFile("Base URL: " . env('ZIEAPI_BASE_URL') . "\n");
    
    logToFile("Attempting to fetch Kelas...\n");
    $data = $service->getKelas();
    
    if ($data === null) {
        logToFile("RESULT: NULL (This should trigger exception in Service, so seeing this means logic path issue)\n");
    } else {
        logToFile("RESULT: SUCCESS! " . count($data) . " items found.\n");
        logToFile("First item: " . print_r(array_slice($data, 0, 1), true) . "\n");
    }

} catch (\Exception $e) {
    logToFile("EXCEPTION CAUGHT:\n");
    logToFile($e->getMessage() . "\n");
    logToFile($e->getTraceAsString() . "\n");
}

logToFile("Script finished.\n");
