<?php

use App\Services\ZieApiService;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function logMsg($msg) {
    echo $msg . "\n";
    file_put_contents('debug_structure.txt', $msg . "\n", FILE_APPEND);
}

file_put_contents('debug_structure.txt', "--- START ---\n");

try {
    $service = new ZieApiService();
    
    // Check Siswa Structure
    logMsg("Fetching Siswa (Raw)...");
    // Reflection to access protected client if needed, or just use public method and dump result
    // But public method might be crashing if it expects array but gets something else.
    // Let's assume the public method 'getSiswa' is what we call.
    
    // We want to see what getSiswa actually returns.
    $siswa = $service->getSiswa();
    logMsg("Type of getSiswa result: " . gettype($siswa));
    if (is_array($siswa)) {
        logMsg("Count: " . count($siswa));
        $first = reset($siswa);
        logMsg("First item type: " . gettype($first));
        logMsg("First item content: " . print_r($first, true));
    } else {
        logMsg("Content: " . print_r($siswa, true));
    }

} catch (\Exception $e) {
    logMsg("Siswa Error: " . $e->getMessage());
}

try {
    // Check Guru Structure for NIP
    logMsg("\nFetching Guru (Raw)...");
    $guru = $service->getGuru();
    if (is_array($guru)) {
        logMsg("Count: " . count($guru));
        $first = reset($guru);
        logMsg("First item: " . print_r($first, true));
        
        // Check for empty NIPs
        $emptyNips = 0;
        foreach ($guru as $g) {
            if (empty($g['nip']) || trim($g['nip']) === '') {
                $emptyNips++;
            }
        }
        logMsg("Gurus with empty/blank NIP: " . $emptyNips);
    }

} catch (\Exception $e) {
    logMsg("Guru Error: " . $e->getMessage());
}
