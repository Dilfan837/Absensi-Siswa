<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Siswa;
use App\Services\PointService;
use Illuminate\Http\Request;

class GuruPointController extends Controller
{
    /**
     * Berikan poin manual kepada siswa di sesi absensi tertentu.
     */
    public function store(Request $request, $id_absensi)
    {
        $request->validate([
            'id_siswa' => 'required|exists:siswa,id_siswa',
            'amount' => 'required|integer', // bisa positif atau negatif
            'reason' => 'required|string|max:255',
        ]);

        $absensi = Absensi::where('dibuat_oleh', auth()->id())->findOrFail($id_absensi);

        // Hanya bisa kasih poin kalau sesi belum ditutup
        if ($absensi->status !== 'aktif') {
            return redirect()->back()->with('error', 'Gagal! Tidak dapat memberikan poin pada sesi yang sudah ditutup.');
        }

        try {
            $pointService = new PointService();
            $pointService->manualByGuru(
                $request->id_siswa,
                $absensi->id_absensi,
                auth()->user()->guru->id_guru ?? auth()->id(), // Fallback ke id_user
                $request->amount,
                $request->reason
            );

            return redirect()->back()->with('success', 'Penilaian integritas berhasil dicatat.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
