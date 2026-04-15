<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\DetailAbsensi; // Pastikan ini diimport

class ScanController extends Controller
{
    public function index()
    {
        $list_kelas = Kelas::all();
        $userSiswa = null;

        if (auth()->check() && auth()->user()->role->nama_role === 'siswa') {
            $userSiswa = Siswa::where('id_user', auth()->id())->first();
        }

        return view('scan', compact('list_kelas', 'userSiswa'));
    }

    public function getSiswaByKelas($id_kelas)
    {
        $siswa = Siswa::where('id_kelas', $id_kelas)->get();
        return response()->json($siswa);
    }

    public function proses(Request $request)
    {
        $token = $request->qr_token;
        $id_siswa = $request->id_siswa;

        // 1. Cari sesi absensi aktif
        $absensi = \App\Models\Absensi::where('qr_token', $token)
            ->where('status', 'aktif')
            ->first();

        if (!$absensi) {
            return response()->json(['status' => 'error', 'message' => 'QR Code tidak valid!']);
        }

        // 2. Cari data siswa
        $siswa = \App\Models\Siswa::find($id_siswa);
        if (!$siswa) {
            return response()->json(['status' => 'error', 'message' => 'Data siswa tidak ditemukan!']);
        }

        // 3. Cek apakah siswa terdaftar & statusnya
        $detail = \App\Models\DetailAbsensi::where('id_absensi', $absensi->id_absensi)
            ->where('id_siswa', $id_siswa)
            ->first();

        if (!$detail) {
            if ($siswa->id_kelas == $absensi->id_kelas) {
                // Auto-create DetailAbsensi if the student was added to the class after the session started
                $detail = \App\Models\DetailAbsensi::create([
                    'id_absensi' => $absensi->id_absensi,
                    'id_siswa' => $id_siswa,
                    'status' => 'alpha'
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Anda tidak terdaftar di kelas ini!']);
            }
        }

        if ($detail->status == 'hadir') {
            return response()->json(['status' => 'warning', 'message' => 'Anda sudah melakukan absensi sebelumnya.']);
        }

        // JIKA QR VALID, JANGAN UPDATE DATABASE DULU!
        // Kirim sinyal sukses agar JavaScript melakukan redirect ke Verifikasi Wajah
        return response()->json([
            'status' => 'success',
            'message' => 'QR Valid! Silakan verifikasi wajah Anda.',
            'redirect_url' => route('absensi.verifikasi', ['id_absensi' => $absensi->id_absensi, 'id_siswa' => $id_siswa])
        ]);
    }
}