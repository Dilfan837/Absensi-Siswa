<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\PointLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointLeaderboardController extends Controller
{
    public function index(Request $request)
    {
        $kelas = Kelas::all();
        $query = Siswa::with('kelas')->where('status_aktif', true);

        // Filter berdasarkan kelas
        if ($request->filled('id_kelas')) {
            $query->where('id_kelas', $request->id_kelas);
        }

        $siswaAll = $query->get();

        // Hitung saldo poin tiap siswa
        $leaderboard = $siswaAll->map(function ($s) {
            $s->point_balance = $s->getPointBalance();
            return $s;
        })->sortByDesc('point_balance')->values();

        // 10 Histori Mutasi Terkini Semua Siswa
        $latestMutations = PointLedger::with(['siswa.kelas', 'guru', 'absensi'])
                            ->latest()
                            ->take(15)
                            ->get();

        return view('admin.leaderboard.index', compact('leaderboard', 'kelas', 'latestMutations'));
    }
}
