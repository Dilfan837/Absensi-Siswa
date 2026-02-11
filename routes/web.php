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
    });
    
    // ============================================
    // ADMIN ROUTES (Only Admin)
    // ============================================
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('jurusan', JurusanController::class);
        Route::resource('kelas', KelasController::class);
        Route::resource('siswa', SiswaController::class);
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        
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
        
        // Absensi Management (Guru Specifc Update/Close)
        Route::prefix('absensi')->group(function () {
            Route::post('/proses-scan', [AbsensiController::class, 'prosesScan'])->name('absensi.prosesScan');
            Route::post('/{id}/tutup', [AbsensiController::class, 'tutupAbsensi'])->name('absensi.tutup');
            Route::put('/detail/{id}/update', [AbsensiController::class, 'updateStatus'])->name('absensi.detail.update');
        });
        Route::resource('absensi', AbsensiController::class);
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
