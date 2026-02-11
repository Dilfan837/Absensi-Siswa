<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\Jurusan;
use App\Models\DetailAbsensi;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik Dasar
        $totalSiswa = Siswa::count();
        $totalKelas = Kelas::count();
        $totalJurusan = Jurusan::count();
        
        // Cek sesi aktif dari tabel absensi
        $sesiAktif = Absensi::where('status', 'aktif')->count();
        
        // Statistik Kehadiran HARI INI (Menggunakan DetailAbsensi yang benar)
        // Kita harus filter berdasarkan tanggal di tabel Absensi (parent)
        $hadirHariIni = DetailAbsensi::where('status', 'hadir')
            ->whereHas('absensi', function($q) {
                $q->whereDate('tanggal', now());
            })
            ->count();

        $statAlpa = DetailAbsensi::where('status', 'alpha') // Atau 'tidak hadir' sesuai enum kamu
            ->whereHas('absensi', function($q) {
                $q->whereDate('tanggal', now());
            })
            ->count();
        
        // Ambil 5 sesi absensi terbaru
        $sesiTerakhir = Absensi::with('kelas')->latest()->take(5)->get();

        return view('dashboard', compact(
            'totalSiswa', 
            'totalKelas', 
            'totalJurusan', 
            'sesiAktif', 
            'hadirHariIni',
            'statAlpa',
            'sesiTerakhir'
        ));
    }
}