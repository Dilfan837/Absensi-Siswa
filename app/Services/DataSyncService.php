<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use App\Models\Role;
use App\Models\ApiSyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DataSyncService
{
    protected $apiService;
    
    public function __construct(ZieApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    /**
     * Sync data kelas dari API
     */
    public function syncKelas()
    {
        $startTime = now();
        $stats = [
            'fetched' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        try {
            DB::beginTransaction();
            
            // 1. Fetch data from API
            $kelasData = $this->apiService->getKelas();
            
            if ($kelasData === null) {
                throw new \Exception("Failed to fetch kelas data from API");
            }
            
            $stats['fetched'] = count($kelasData);
            
            // 2. Process each record
            foreach ($kelasData as $item) {
                try {
                    $this->processKelasRecord($item, $stats);
                } catch (\Exception $e) {
                    $stats['failed']++;
                    $stats['errors'][] = [
                        'id' => $item['id'] ?? 'unknown',
                        'nama' => $item['nama'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ];
                    // Continue dengan record berikutnya
                    continue;
                }
            }
            
            DB::commit();
            
            // 3. Create sync log
            $this->createSyncLog('kelas', $stats, $startTime, 'success');
            
            return $stats;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Sync Kelas Failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->createSyncLog('kelas', $stats, $startTime, 'failed');
            
            throw $e;
        }
    }
    
    /**
     * Process single kelas record
     */
    protected function processKelasRecord(array $data, array &$stats)
    {
        // Extract tingkat dari nama (X AKKUL 1 -> X)
        $tingkat = $this->extractTingkat($data['nama']);
        
        // Find or create jurusan
        $jurusan = $this->findOrCreateJurusan($data['jurusan']);
        
        // Update or Create Kelas
        $kelas = Kelas::updateOrCreate(
            ['api_kelas_id' => $data['id']],  // Unique identifier dari API
            [
                'id_jurusan' => $jurusan->id_jurusan,
                'nama_kelas' => $data['nama'],
                'tingkat' => $tingkat,
            ]
        );
        
        // Track stats
        if ($kelas->wasRecentlyCreated) {
            $stats['created']++;
        } else {
            $stats['updated']++;
        }
    }
    
    /**
     * Extract tingkat from kelas name
     * "X AKKUL 1" -> "X"
     * "XI TJKT 2" -> "XI"
     */
    protected function extractTingkat($namaKelas)
    {
        // Extract first word (X, XI, XII)
        $parts = explode(' ', $namaKelas);
        $tingkat = $parts[0] ?? '';
        
        // Convert to number if needed (X->10, XI->11, XII->12)
        $tingkatMap = ['X' => '10', 'XI' => '11', 'XII' => '12'];
        
        return $tingkatMap[$tingkat] ?? $tingkat;
    }
    
    /**
     * Find or create jurusan by kode
     */
    protected function findOrCreateJurusan($kodeJurusan)
    {
        $jurusan = Jurusan::where('kode_jurusan', $kodeJurusan)->first();
        
        if (!$jurusan) {
            // Auto-create jurusan baru
            $jurusan = Jurusan::create([
                'kode_jurusan' => $kodeJurusan,
                'nama_jurusan' => $kodeJurusan, // Temporary, admin bisa edit nanti
            ]);
            
            Log::info("Auto-created new jurusan", [
                'kode' => $kodeJurusan
            ]);
        }
        
        return $jurusan;
    }
    
    /**
     * Sync data siswa dari API
     */
    public function syncSiswa()
    {
        $startTime = now();
        $stats = [
            'fetched' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        try {
            // 1. Fetch data from API
            $siswaData = $this->apiService->getSiswa();
            file_put_contents('debug_trace.txt', "DEBUG: Fetched " . (is_array($siswaData) ? count($siswaData) : 'null') . " siswa records.\n", FILE_APPEND);
            
            if ($siswaData === null) {
                throw new \Exception("Failed to fetch siswa data from API");
            }
            
            $stats['fetched'] = count($siswaData);
            
            // 2. Process in chunks
            $chunkSize = 50;
            $chunks = array_chunk($siswaData, $chunkSize);
            
            foreach ($chunks as $chunkIndex => $chunk) {
                DB::beginTransaction();
                try {
                    foreach ($chunk as $index => $item) {
                        try {
                            $realIndex = ($chunkIndex * $chunkSize) + $index;
                            if ($realIndex % 100 == 0) {
                                file_put_contents('debug_trace.txt', "DEBUG: Processing index $realIndex ...\n", FILE_APPEND);
                            }
                            $this->processSiswaRecord($item, $stats);
                        } catch (\Exception $e) {
                            $stats['failed']++;
                            $stats['errors'][] = [
                                'peserta_didik_id' => $item['peserta_didik_id'] ?? 'unknown',
                                'nama' => $item['nama'] ?? 'unknown',
                                'error' => $e->getMessage()
                            ];
                            continue;
                        }
                    }
                    DB::commit();
                    file_put_contents('debug_trace.txt', "DEBUG: Committed chunk $chunkIndex\n", FILE_APPEND);
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }
            
            // 3. Create sync log
            $this->createSyncLog('siswa', $stats, $startTime, $stats['failed'] == 0 ? 'success' : 'partial');
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error("Sync Siswa Failed", [
                'error' => $e->getMessage()
            ]);
            
            $this->createSyncLog('siswa', $stats, $startTime, 'failed');
            
            throw $e;
        }
    }
    
    /**
     * Process single siswa record
     */
    protected function processSiswaRecord(array $data, array &$stats)
    {
        // Validasi required fields
        if (empty($data['peserta_didik_id']) || empty($data['nama'])) {
            throw new \Exception("Missing required fields: peserta_didik_id or nama");
        }
        
        // Find kelas by nama_rombel
        $kelas = null;
        if (!empty($data['nama_rombel'])) {
            $kelas = Kelas::where('nama_kelas', $data['nama_rombel'])->first();
        }
        
        // Mapping data
        $siswaData = [
            'peserta_didik_id' => $data['peserta_didik_id'],
            'nis' => $data['no_induk'] ?? null,
            'nisn' => $data['nisn'] ?? null,
            'nik' => $data['nik'] ?? null,
            'no_induk' => $data['no_induk'] ?? null,
            'nama_siswa' => $data['nama'],
            'id_kelas' => $kelas->id_kelas ?? null,
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => ($data['tanggal_lahir'] ?? null) !== '0000-00-00' ? $data['tanggal_lahir'] : null,
            'agama_id' => $data['agama_id'] ?? null,
            'anak_ke' => $data['anak_ke'] ?? null,
            'email' => $data['email'] ?? null,
            'no_telp' => $data['no_telp'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'rt' => $data['rt'] ?? null,
            'rw' => $data['rw'] ?? null,
            'desa_kelurahan' => $data['desa_kelurahan'] ?? null,
            'kecamatan' => $data['kecamatan'] ?? null,
            'kode_pos' => $data['kode_pos'] ?? null,
            'kode_wilayah' => $data['kode_wilayah'] ?? null,
            'sekolah_asal' => $data['sekolah_asal'] ?? null,
            'diterima_kelas_smk' => $data['diterima_kelas_smk'] ?? null,
            'nama_rombel' => $data['nama_rombel'] ?? null,
            'rombel_id' => $data['rombel_id'] ?? null,
            'nama_ayah' => $data['nama_ayah'] ?? null,
            'nama_ibu' => $data['nama_ibu'] ?? null,
            'kerja_ayah_id' => $data['kerja_ayah_id'] ?? null,
            'kerja_ibu_id' => $data['kerja_ibu_id'] ?? null,
            'nama_wali' => $data['nama_wali'] ?? null,
            'alamat_wali' => $data['alamat_wali'] ?? null,
            'telp_wali' => $data['telp_wali'] ?? null,
            'kerja_wali_id' => $data['kerja_wali_id'] ?? null,
            'status_aktif' => ($data['active'] ?? '1') == '1',
            'last_synced_at' => now(),
        ];
        
        // Check if siswa exists (preserve foto & face_descriptor)
        $existingSiswa = Siswa::where('peserta_didik_id', $data['peserta_didik_id'])->first();
        
        if ($existingSiswa) {
            // Update - preserve foto & face_descriptor
            $siswaData['foto'] = $existingSiswa->foto;
            $siswaData['face_descriptor'] = $existingSiswa->face_descriptor;
            $siswaData['id_user'] = $existingSiswa->id_user; // Preserve existing user
            
            $existingSiswa->update($siswaData);
            $siswa = $existingSiswa;
            $stats['updated']++;
        } else {
            // Create new
            $siswa = Siswa::create($siswaData);
            $stats['created']++;
            
            // Create user account for new siswa
            $this->createUserForSiswa($siswa, $data);
        }
    }
    
    /**
     * Create user account for new siswa
     */
    protected function createUserForSiswa(Siswa $siswa, array $apiData)
    {
        // Cari role siswa
        $roleSiswa = Role::where('nama_role', 'siswa')->first();
        
        if (!$roleSiswa) {
            Log::warning("Role 'siswa' not found, skipping user creation");
            return;
        }
        
        // Username: NISN
        $username = $apiData['nisn'] ?? $apiData['no_induk'];
        
        // Password: Random string
        $password = Str::random(8);
        
        // Check if username already exists
        if (User::where('username', $username)->exists()) {
            Log::warning("Username already exists", ['username' => $username]);
            return;
        }
        
        try {
            $user = User::create([
                'username' => $username,
                'email' => $apiData['email'] ?? null,
                'password' => Hash::make($password),
                'nama' => $apiData['nama'],
                'id_role' => $roleSiswa->id_role,
            ]);
            
            // Update siswa dengan id_user
            $siswa->update(['id_user' => $user->id_user]);
            
            // Log password for admin (TODO: send via email or save to special log)
            Log::info("New student user created", [
                'username' => $username,
                'password' => $password,
                'nama' => $apiData['nama']
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to create user for siswa", [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Sync data guru dari API
     */

    public function syncGuru()
    {
        $startTime = now();
        $stats = [
            'fetched' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        try {
            // 1. Fetch data from API
            $guruData = $this->apiService->getGuru();
            
            if ($guruData === null) {
                throw new \Exception("Failed to fetch guru data from API");
            }
            
            $stats['fetched'] = count($guruData);
            
            // 2. Process in chunks
            $chunkSize = 50;
            $chunks = array_chunk($guruData, $chunkSize);
            
            foreach ($chunks as $chunk) {
                DB::beginTransaction();
                try {
                    foreach ($chunk as $item) {
                        try {
                            $this->processGuruRecord($item, $stats);
                        } catch (\Exception $e) {
                            $stats['failed']++;
                            $stats['errors'][] = [
                                'guru_id' => $item['guru_id'] ?? 'unknown',
                                'nama' => $item['nama'] ?? 'unknown',
                                'error' => $e->getMessage()
                            ];
                            continue;
                        }
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }
            
            // 3. Create sync log
            $this->createSyncLog('guru', $stats, $startTime, $stats['failed'] == 0 ? 'success' : 'partial');
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error("Sync Guru Failed", [
                'error' => $e->getMessage()
            ]);
            
            $this->createSyncLog('guru', $stats, $startTime, 'failed');
            
            throw $e;
        }
    }
    
    /**
     * Process single guru record
     */
    protected function processGuruRecord(array $data, array &$stats)
    {
        // Validasi required fields
        if (empty($data['guru_id']) || empty($data['nama'])) {
            throw new \Exception("Missing required fields: guru_id or nama");
        }
        
        // Sanitize NIP: Convert empty strings/whitespace to NULL to avoid unique constraint violation
        $nip = isset($data['nip']) && trim($data['nip']) !== '' ? trim($data['nip']) : null;

        // Mapping data
        $guruData = [
            'guru_id' => $data['guru_id'],
            'nuptk' => $data['nuptk'] ?? null,
            'nip' => $nip, // Use sanitized NIP
            'nik' => $data['nik'] ?? null,
            'nama' => $data['nama'],
            'jenis_kelamin' => $data['jenis_kelamin'],
            'tempat_lahir' => $data['tempat_lahir'] ?? null,
            'tanggal_lahir' => ($data['tanggal_lahir'] ?? null) !== '0000-00-00' ? $data['tanggal_lahir'] : null,
            'agama_id' => $data['agama_id'] ?? null,
            'jenis_ptk_id' => $data['jenis_ptk_id'] ?? null,
            'status_kepegawaian_id' => $data['status_kepegawaian_id'] ?? null,
            'sekolah_id' => $data['sekolah_id'] ?? null,
            'email' => $data['email'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'rt' => $data['rt'] ?? null,
            'rw' => $data['rw'] ?? null,
            'desa_kelurahan' => $data['desa_kelurahan'] ?? null,
            'kecamatan' => $data['kecamatan'] ?? null,
            'kode_wilayah' => $data['kode_wilayah'] ?? null,
            'kode_pos' => $data['kode_pos'] ?? null,
            'photo' => $data['photo'] ?? null,
            'status_aktif' => true,
            'last_synced_at' => now(),
        ];
        
        // Check if guru exists (preserve id_user)
        $existingGuru = Guru::where('guru_id', $data['guru_id'])->first();
        
        if ($existingGuru) {
            // Update - preserve id_user & photo
            $guruData['id_user'] = $existingGuru->id_user;
            $guruData['photo'] = $existingGuru->photo;
            
            $existingGuru->update($guruData);
            $guru = $existingGuru;
            $stats['updated']++;
        } else {
            // Create new
            $guru = Guru::create($guruData);
            $stats['created']++;
            
            // Create user account for new guru
            $this->createUserForGuru($guru, $data);
        }
    }
    
    /**
     * Create user account for new guru
     */
    protected function createUserForGuru(Guru $guru, array $apiData)
    {
        // Cari role guru
        $roleGuru = Role::where('nama_role', 'guru')->first();
        
        if (!$roleGuru) {
            Log::warning("Role 'guru' not found, skipping user creation");
            return;
        }
        
        // Username: NIP
        $username = $apiData['nip'] ?? $apiData['nuptk'];
        
        // Password: Random string
        $password = Str::random(8);
        
        // Check if username already exists
        if (User::where('username', $username)->exists()) {
            Log::warning("Username already exists", ['username' => $username]);
            return;
        }
        
        try {
            $user = User::create([
                'username' => $username,
                'email' => $apiData['email'] ?? null,
                'password' => Hash::make($password),
                'nama' => $apiData['nama'],
                'id_role' => $roleGuru->id_role,
            ]);
            
            // Update guru dengan id_user
            $guru->update(['id_user' => $user->id_user]);
            
            // Log password for admin
            Log::info("New teacher user created", [
                'username' => $username,
                'password' => $password,
                'nama' => $apiData['nama']
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to create user for guru", [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Sync all data types
     */
    public function syncAll()
    {
        $results = [];
        
        // Kelas dulu (karena siswa depend on kelas)
        $results['kelas'] = $this->syncKelas();
        
        // Then siswa & guru (parallel, no dependency)
        $results['siswa'] = $this->syncSiswa();
        $results['guru'] = $this->syncGuru();
        
        return $results;
    }
    
    /**
     * Create sync log entry
     */
    protected function createSyncLog($apiType, $stats, $startTime, $status)
    {
        $completedAt = now();
        $duration = $completedAt->diffInSeconds($startTime);
        
        ApiSyncLog::create([
            'api_type' => $apiType,
            'records_fetched' => $stats['fetched'],
            'records_created' => $stats['created'],
            'records_updated' => $stats['updated'],
            'records_failed' => $stats['failed'],
            'error_details' => !empty($stats['errors']) ? $stats['errors'] : null,
            'status' => $status,
            'started_at' => $startTime,
            'completed_at' => $completedAt,
            'duration_seconds' => $duration,
            'triggered_by_user_id' => auth()->id() ?? null,
        ]);
    }
}
