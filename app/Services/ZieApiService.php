<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ZieApiService
{
    protected $client;
    protected $baseUrl;
    protected $tahun;
    protected $timeout;
    
    public function __construct()
    {
        $this->baseUrl = env('ZIEAPI_BASE_URL', 'https://zieapi.zielabs.id/api');
        $this->tahun = env('ZIEAPI_TAHUN', '2025');
        $this->timeout = env('ZIEAPI_TIMEOUT', 30);
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'http_errors' => false, // Handle errors manually
        ]);
    }
    
    /**
     * Fetch data siswa from API
     * @param int|null $tahun
     * @return array|null
     */
    public function getSiswa($tahun = null)
    {
        $tahun = $tahun ?? $this->tahun;
        
        try {
            $response = $this->withRetry(function() use ($tahun) {
                return $this->client->get('getsiswa', [
                    'query' => ['tahun' => $tahun],
                ]);
            });
            
            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                $data = $body['data'] ?? []; // Extract data from wrapper
                
                Log::info("ZieAPI: Siswa data fetched successfully", [
                    'count' => is_array($data) ? count($data) : 0
                ]);
                
                return is_array($data) ? $data : [];
            }
            
            throw new \Exception("ZieAPI Error: " . $response->getStatusCode() . " - " . substr($response->getBody()->getContents(), 0, 500));
            
        } catch (\Exception $e) {
            // Fallback: Try file_get_contents if Guzzle fails (e.g. SSL/cURL issues)
            try {
                Log::warning("ZieAPI: Guzzle failed, trying file_get_contents fallback. Error: " . $e->getMessage());
                
                $url = $this->baseUrl . "/getsiswa?tahun=" . $tahun;
                $context = stream_context_create([
                    'http' => ['ignore_errors' => true, 'timeout' => 30],
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
                ]);
                
                $response = file_get_contents($url, false, $context);
                
                if ($response !== false) {
                    $body = json_decode($response, true);
                    $data = $body['data'] ?? [];
                    
                    echo "DEBUG: Fallback fetch success. Count: " . count($data) . "\n";
                    Log::info("ZieAPI: Siswa data fetched via fallback", ['count' => count($data)]);
                    return $data;
                }
            } catch (\Exception $ex) {
                echo "DEBUG: Fallback failed: " . $ex->getMessage() . "\n";
            }

            throw $e; 
        }
    }
    
    /**
     * Fetch data guru from API
     * @param int|null $tahun
     * @return array|null
     */
    public function getGuru($tahun = null)
    {
        $tahun = $tahun ?? $this->tahun;
        
        try {
            $response = $this->withRetry(function() use ($tahun) {
                return $this->client->get('getguru', [
                    'query' => ['tahun' => $tahun],
                ]);
            });
            
            if ($response->getStatusCode() === 200) {
                $data = json_decode($response->getBody(), true);
                
                Log::info("ZieAPI: Guru data fetched successfully", [
                    'count' => is_array($data) ? count($data) : 0
                ]);
                
                return is_array($data) ? $data : [];
            }
            
            throw new \Exception("ZieAPI Error: " . $response->getStatusCode() . " - " . substr($response->getBody()->getContents(), 0, 500));
            
        } catch (\Exception $e) {
            throw $e; // Debugging: Re-throw to see error in console
        }
    }
    
    /**
     * Fetch data kelas from API
     * @param int|null $tahun
     * @return array|null
     */
    public function getKelas($tahun = null)
    {
        $tahun = $tahun ?? $this->tahun;
        
        try {
            $response = $this->withRetry(function() use ($tahun) {
                return $this->client->get('getkelas', [
                    'query' => ['tahun' => $tahun],
                ]);
            });
            
            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody(), true);
                
                // API kelas returns {status: true, semester_id: "20251", data: [...]}
                $data = $body['data'] ?? [];
                
                Log::info("ZieAPI: Kelas data fetched successfully", [
                    'count' => count($data),
                    'semester_id' => $body['semester_id'] ?? null
                ]);
                
                return $data;
            }
            
            throw new \Exception("ZieAPI Error: " . $response->getStatusCode() . " - " . substr($response->getBody()->getContents(), 0, 500));
            
        } catch (\Exception $e) {
            throw $e; // Debugging: Re-throw to see error in console
        }
    }
    
    /**
     * Retry wrapper for API calls
     * @param callable $callback
     * @param int $maxRetries
     * @return mixed
     */
    protected function withRetry(callable $callback, $maxRetries = 3)
    {
        $attempt = 1;
        $lastException = null;
        
        while ($attempt <= $maxRetries) {
            try {
                return $callback();
            } catch (\Exception $e) {
                $lastException = $e;
                
                if ($attempt === $maxRetries) {
                    throw $e;
                }
                
                Log::warning("ZieAPI: Retry attempt {$attempt}/{$maxRetries}", [
                    'error' => $e->getMessage()
                ]);
                
                // Exponential backoff: 1s, 2s, 4s
                sleep(pow(2, $attempt - 1));
                $attempt++;
            }
        }
        
        throw $lastException;
    }
}
