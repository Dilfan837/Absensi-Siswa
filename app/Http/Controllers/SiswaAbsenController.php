<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\DetailAbsensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaAbsenController extends Controller
{
    public function index()
    {
        return view('siswa.scan');
    }

    public function prosesScan(Request $request)
    {
        $request->validate(['qr_token' => 'required']);

        // 1. Ambil data siswa yang login
        $siswa = Siswa::where('id_user', Auth::id())->first();

        if (!$siswa) {
            return response()->json(['status' => 'error', 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        // 2. Cari sesi absensi berdasarkan token
        $absensi = Absensi::where('qr_token', $request->qr_token)
                          ->where('status', 'aktif')
                          ->first();

        if (!$absensi) {
            return response()->json(['status' => 'error', 'message' => 'QR Code tidak valid atau sesi sudah berakhir.'], 404);
        }

        // 3. Cek apakah siswa ini terdaftar di kelas yang sedang absen
        $detail = DetailAbsensi::where('id_absensi', $absensi->id_absensi)
                                ->where('id_siswa', $siswa->id_siswa)
                                ->first();

        if (!$detail) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak terdaftar di sesi absen kelas ini.'], 403);
        }

        if ($detail->status == 'hadir') {
            return response()->json(['status' => 'info', 'message' => 'Anda sudah melakukan absensi sebelumnya.']);
        }

        // 4. Update status dari alpha menjadi hadir
        $detail->update([
            'status' => 'hadir',
            'waktu_scan' => now()
        ]);

        return response()->json(['status' => 'success', 'message' => 'Absensi berhasil! Selamat Belajar.']);
    }
}