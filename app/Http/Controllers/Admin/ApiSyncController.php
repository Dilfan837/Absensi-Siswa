<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DataSyncService;
use App\Models\ApiSyncLog;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;

class ApiSyncController extends Controller
{
    protected $syncService;
    
    public function __construct(DataSyncService $syncService)
    {
        $this->middleware(['auth', 'role:admin']);
        $this->syncService = $syncService;
    }
    
    /**
     * Dashboard sync - Main page
     */
    public function index()
    {
        // Get last sync info per type
        $lastSyncSiswa = ApiSyncLog::where('api_type', 'siswa')
            ->latest('completed_at')
            ->first();
            
        $lastSyncGuru = ApiSyncLog::where('api_type', 'guru')
            ->latest('completed_at')
            ->first();
            
        $lastSyncKelas = ApiSyncLog::where('api_type', 'kelas')
            ->latest('completed_at')
            ->first();
        
        // Get stats
        $stats = [
            'total_siswa' => Siswa::count(),
            'total_guru' => Guru::count(),
            'total_kelas' => Kelas::count(),
        ];
        
        // Recent logs
        $recentLogs = ApiSyncLog::with('triggeredBy')
            ->latest('completed_at')
            ->limit(5)
            ->get();
        
        return view('admin.sync.index', compact(
            'lastSyncSiswa',
            'lastSyncGuru',
            'lastSyncKelas',
            'stats',
            'recentLogs'
        ));
    }
    
    /**
     * Sync siswa via AJAX
     */
    public function syncSiswa(Request $request)
    {
        try {
            // Rate limiting check
            $this->checkRateLimit('siswa');
            
            $result = $this->syncService->syncSiswa();
            
            return response()->json([
                'success' => true,
                'message' => 'Siswa data synced successfully',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sync guru via AJAX
     */
    public function syncGuru(Request $request)
    {
        try {
            // Rate limiting check
            $this->checkRateLimit('guru');
            
            $result = $this->syncService->syncGuru();
            
            return response()->json([
                'success' => true,
                'message' => 'Guru data synced successfully',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sync kelas via AJAX
     */
    public function syncKelas(Request $request)
    {
        try {
            // Rate limiting check
            $this->checkRateLimit('kelas');
            
            $result = $this->syncService->syncKelas();
            
            return response()->json([
                'success' => true,
                'message' => 'Kelas data synced successfully',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sync all data via AJAX
     */
    public function syncAll(Request $request)
    {
        try {
            // Rate limiting check
            $this->checkRateLimit('all');
            
            $results = $this->syncService->syncAll();
            
            return response()->json([
                'success' => true,
                'message' => 'All data synced successfully',
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * View sync history/logs
     */
    public function history()
    {
        $logs = ApiSyncLog::with('triggeredBy')
            ->latest('completed_at')
            ->paginate(20);
        
        return view('admin.sync.history', compact('logs'));
    }
    
    /**
     * Rate limiting to prevent abuse
     * Max 1 sync per 5 minutes per type
     */
    protected function checkRateLimit(string $type): void
    {
        $lastSync = ApiSyncLog::where('api_type', $type)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();
        
        if ($lastSync) {
            throw new \Exception("Please wait 5 minutes before syncing {$type} again");
        }
    }
}
