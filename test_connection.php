<?php

$url = "https://zieapi.zielabs.id/api/getsiswa?tahun=2025";

echo "Testing connection to: $url\n";

$context = stream_context_create([
    'http' => [
        'ignore_errors' => true,
        'timeout' => 10
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
]);

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "FAILED: " . error_get_last()['message'];
} else {
    echo "SUCCESS: " . substr($response, 0, 100) . "...";
}
