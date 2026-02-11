<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\DetailAbsensi;

class GuruDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        // Statistik personal guru
        // Total absensi yang dibuat oleh guru ini
        $totalAbsensi = Absensi::where('dibuat_oleh', $userId)->count();
        
        // Sesi absensi aktif yang dibuat guru ini
        $sesiAktif = Absensi::where('dibuat_oleh', $userId)
            ->where('status', 'aktif')
            ->count();
        
        // Statistik kehadiran hari ini dari absensi yang dibuat guru
        $hadirHariIni = DetailAbsensi::whereHas('absensi', function($q) use ($userId) {
            $q->where('dibuat_oleh', $userId)
              ->whereDate('tanggal', now());
        })->where('status', 'hadir')->count();
        
        $alpaHariIni = DetailAbsensi::whereHas('absensi', function($q) use ($userId) {
            $q->where('dibuat_oleh', $userId)
              ->whereDate('tanggal', now());
        })->where('status', 'alpha')->count();
        
        // 5 Sesi absensi terbaru yang dibuat guru ini
        $sesiTerakhir = Absensi::with('kelas')
            ->where('dibuat_oleh', $userId)
            ->latest()
            ->take(5)
            ->get();

        return view('guru.dashboard', compact(
            'totalAbsensi',
            'sesiAktif',
            'hadirHariIni',
            'alpaHariIni',
            'sesiTerakhir'
        ));
    }
}
