<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    SiswaController,
    AbsensiController,
    KelasController,
    KehadiranController,
    UserController,
    RoleController,
    JurusanController,
    SiswaAbsenController,
    ScanController,
    DashboardController,
    SettingController,
    AuthController,
    GuruDashboardController,
    SiswaDashboardController
};

// ============================================
// AUTHENTICATION ROUTES (Guest only)
// ============================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ============================================
// PROTECTED ROUTES (Authenticated users only)
// ============================================
Route::middleware('auth')->group(function () {
    
    // Logout (accessible by all authenticated users)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Global Search (accessible by all authenticated users)
    Route::get('/search', [\App\Http\Controllers\SearchController::class, 'search'])->name('search');
    
    // ============================================
    // PROFILE ROUTES (Authenticated users)
    // ============================================
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    });

    // ============================================
    // SISWA ROUTES (Only Siswa)
    // ============================================
    Route::middleware('role:siswa')->group(function () {
        Route::get('/siswa/dashboard', [SiswaDashboardController::class, 'index'])->name('siswa.dashboard');
        Route::get('/siswa-scan', [SiswaAbsenController::class, 'index'])->name('siswa.scan');
        Route::post('/proses-scan-siswa', [SiswaAbsenController::class, 'prosesScan'])->name('siswa.proses-scan');
        
        // Laporan Karakter (Radar) untuk Siswa sendiri
        Route::get('/siswa-laporanku', [\App\Http\Controllers\Siswa\SiswaReportController::class, 'myReport'])->name('siswa.reports.my');
    });
    
    // ============================================
    // ADMIN ROUTES (Only Admin)
    // ============================================
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
        Route::resource('jurusan', JurusanController::class);
        Route::resource('kelas', KelasController::class);
        Route::resource('data-guru', \App\Http\Controllers\GuruController::class)->names('guru')->parameters(['data-guru' => 'id']);
        Route::post('siswa/{id}/wajah', [SiswaController::class, 'updateWajah'])->name('siswa.updateWajah');
        Route::resource('siswa', SiswaController::class);
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('mata-pelajaran', \App\Http\Controllers\MataPelajaranController::class);
        Route::resource('assessment-categories', \App\Http\Controllers\Admin\AssessmentCategoryController::class);
        
        // Penilaian Guru oleh Admin
        Route::get('penilaian-guru', [\App\Http\Controllers\Admin\AdminAssessmentController::class, 'indexGuru'])->name('admin.assessments.guru');
        Route::post('penilaian-guru/store', [\App\Http\Controllers\Admin\AdminAssessmentController::class, 'storeGuru'])->name('admin.assessments.guru.store');
        
        // Monitoring & Dashboard Admin
        Route::prefix('monitoring')->name('admin.monitoring.')->group(function () {
            Route::get('siswa', [\App\Http\Controllers\Admin\AdminMonitoringController::class, 'monitorSiswa'])->name('siswa');
            Route::get('siswa/{id_siswa}/detail', [\App\Http\Controllers\Siswa\SiswaReportController::class, 'show'])->name('siswa.detail');
            Route::get('guru', [\App\Http\Controllers\Admin\AdminMonitoringController::class, 'monitorGuru'])->name('guru');
            Route::get('guru/{id_guru}/detail', [\App\Http\Controllers\Guru\GuruReportController::class, 'show'])->name('guru.detail');
            Route::get('rekap', [\App\Http\Controllers\Admin\AdminMonitoringController::class, 'recapReport'])->name('recap');
        });
        
        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('settings.index');
            Route::post('/toggle-geofence', [SettingController::class, 'toggleGeofence'])->name('settings.toggle-geofence');
            Route::post('/locations', [SettingController::class, 'storeLocation'])->name('settings.locations.store');
            Route::put('/locations/{id}', [SettingController::class, 'updateLocation'])->name('settings.locations.update');
            Route::delete('/locations/{id}', [SettingController::class, 'deleteLocation'])->name('settings.locations.delete');
        });
    });
    
    // ============================================
    // SCANNER & VERIFIKASI (Accessible by Admin, Guru & Siswa)
    // ============================================
    Route::middleware('role:admin,guru,siswa')->group(function () {
        // Scanner
        Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
        Route::post('/scan/proses', [ScanController::class, 'proses'])->name('scan.proses');
        Route::get('/get-siswa/{id_kelas}', [ScanController::class, 'getSiswaByKelas']);
        
        // Proses Absensi & Verifikasi
        Route::prefix('absensi')->group(function () {
            Route::get('/absensi-sukses', [AbsensiController::class, 'sukses'])->name('absensi.sukses');
            Route::post('/simpan', [AbsensiController::class, 'simpanKehadiran'])->name('absensi.simpan');
            Route::get('/verifikasi/{id_absensi}/{id_siswa}', [AbsensiController::class, 'verifikasiWajah'])->name('absensi.verifikasi');
        });
    });


    // ============================================
    // GURU ROUTES (Only Guru)
    // ============================================
    Route::middleware('role:guru')->group(function () {
        Route::get('/guru/dashboard', [GuruDashboardController::class, 'index'])->name('guru.dashboard');
        Route::get('/guru/dashboard/export', [GuruDashboardController::class, 'export'])->name('guru.dashboard.export');
        
        // Absensi Management (Guru Specifc Update/Close)
        Route::prefix('absensi')->group(function () {
            Route::post('/proses-scan', [AbsensiController::class, 'prosesScan'])->name('absensi.prosesScan');
            Route::post('/{id}/tutup', [AbsensiController::class, 'tutupAbsensi'])->name('absensi.tutup');
            Route::put('/detail/{id}/update', [AbsensiController::class, 'updateStatus'])->name('absensi.detail.update');
        });
        Route::resource('absensi', AbsensiController::class);
        
        // Penilaian Siswa oleh Guru
        Route::get('penilaian-siswa', [\App\Http\Controllers\Guru\GuruAssessmentController::class, 'index'])->name('guru.assessments.siswa');
        Route::post('penilaian-siswa/store', [\App\Http\Controllers\Guru\GuruAssessmentController::class, 'store'])->name('guru.assessments.siswa.store');
        
        // Monitoring Karakter Siswa
        Route::prefix('monitoring-kelas')->name('guru.monitoring.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Guru\GuruMonitoringController::class, 'monitorSiswa'])->name('siswa');
            Route::get('siswa/{id_siswa}/detail', [\App\Http\Controllers\Siswa\SiswaReportController::class, 'show'])->name('siswa.detail');
            Route::get('rekap', [\App\Http\Controllers\Guru\GuruMonitoringController::class, 'recapReport'])->name('recap');
        });
        
        // Laporan Kinerja (Radar) untuk Guru sendiri
        Route::get('laporanku', [\App\Http\Controllers\Guru\GuruReportController::class, 'myReport'])->name('guru.reports.my');
    });
    
    // Kehadiran (accessible by admin and guru)
    Route::middleware('role:admin,guru')->group(function () {
        Route::resource('kehadiran', KehadiranController::class);
    });
});

// Redirect root to login if not authenticated
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role->nama_role;
        return match($role) {
            'admin' => redirect('/dashboard'),
            'guru' => redirect('/guru/dashboard'),
            'siswa' => redirect('/siswa/dashboard'),
            default => redirect('/login')
        };
    }
    return redirect('/login');
});
