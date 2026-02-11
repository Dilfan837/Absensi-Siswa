<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DataSyncService;

class SyncZieApiData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:zieapi 
                            {--type=all : Type of data to sync (all|siswa|guru|kelas)}
                            {--force : Force sync without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from ZieLabs API (siswa, guru, kelas)';

    protected $syncService;

    /**
     * Create a new command instance.
     */
    public function __construct(DataSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $type = $this->option('type');
        $force = $this->option('force');
        
        // Validate type
        if (!in_array($type, ['all', 'siswa', 'guru', 'kelas'])) {
            $this->error("Invalid type: {$type}. Must be one of: all, siswa, guru, kelas");
            return Command::FAILURE;
        }
        
        // Confirmation
        if (!$force) {
            if (!$this->confirm("Are you sure you want to sync {$type} data from ZieLabs API?")) {
                $this->info('Sync cancelled.');
                return Command::SUCCESS;
            }
        }
        
        $this->info("🚀 Starting sync for: {$type}");
        $this->getOutput()->writeln("DEBUG: Starting performSync..."); 
        $this->newLine();
        
        try {
            $results = $this->performSync($type);
            $this->displayResults($results);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("❌ Sync failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Perform the sync operation
     */
    protected function performSync(string $type): array
    {
        switch ($type) {
            case 'siswa':
                $this->info('📚 Syncing Siswa data...');
                return ['siswa' => $this->syncService->syncSiswa()];
                
            case 'guru':
                $this->info('👨‍🏫 Syncing Guru data...');
                return ['guru' => $this->syncService->syncGuru()];
                
            case 'kelas':
                $this->info('🏫 Syncing Kelas data...');
                return ['kelas' => $this->syncService->syncKelas()];
                
            case 'all':
                $this->info('🔄 Syncing ALL data...');
                return $this->syncService->syncAll();
                
            default:
                throw new \Exception("Invalid type: {$type}");
        }
    }
    
    /**
     * Display sync results
     */
    protected function displayResults(array $results): void
    {
        foreach ($results as $type => $stats) {
            $this->newLine();
            $this->info("✓ {$type} sync completed:");
            
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Fetched from API', $stats['fetched']],
                    ['✅ Created (new)', $stats['created']],
                    ['🔄 Updated (existing)', $stats['updated']],
                    ['❌ Failed', $stats['failed']],
                ]
            );
            
            if (!empty($stats['errors'])) {
                $this->warn("⚠️  Errors encountered:");
                foreach ($stats['errors'] as $error) {
                    $nama = $error['nama'] ?? 'unknown';
                    $errorMsg = $error['error'] ?? 'unknown error';
                    $this->line("  - {$nama}: {$errorMsg}");
                }
            }
        }
        
        $this->newLine();
        $this->info('✨ Sync process completed!');
    }
}
