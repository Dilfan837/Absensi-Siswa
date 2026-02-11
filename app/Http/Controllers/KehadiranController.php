<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Untuk simpan foto selfie

class KehadiranController extends Controller
{
    /**
     * Menampilkan rekap kehadiran untuk Admin/Guru
     */
    public function index(Request $request)
    {
        // Menampilkan kehadiran berdasarkan sesi absensi tertentu
        $query = Kehadiran::with(['siswa', 'absensi']);

        if ($request->has('id_absensi')) {
            $query->where('id_absensi', $request->id_absensi);
        }

        $kehadiran = $query->latest()->get();
        return view('kehadiran.index', compact('kehadiran'));
    }

    /**
     * Store data kehadiran (Dipicu oleh Siswa setelah Scan & Face Verify)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_absensi' => 'required',
            'foto_selfie' => 'required', // Data base64 dari kamera atau file
        ]);

        // Logika Simpan Foto Selfie
        $img = $request->foto_selfie;
        $folderPath = "public/absensi/selfie/";
        
        // Menangani jika foto dikirim dalam format Base64 (umum untuk Face Recognition JS)
        $image_parts = explode(";base64,", $img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = uniqid() . '.png';
        $file = $folderPath . $fileName;
        Storage::put($file, $image_base64);

        // Simpan ke Database
        $kehadiran = Kehadiran::create([
            'id_absensi' => $request->id_absensi,
            'id_siswa' => auth()->user()->siswa->id_siswa,
            'waktu_absen' => now(),
            'status_kehadiran' => 'Hadir',
            'lampiran_foto' => $fileName,
            'keterangan' => $request->keterangan ?? 'Hadir melalui sistem QR & Face Recognition'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat!',
            'data' => $kehadiran
        ]);
    }

    /**
     * Hapus data kehadiran jika ada kesalahan
     */
    public function destroy($id)
    {
        $kehadiran = Kehadiran::findOrFail($id);
        // Hapus foto dari storage
        Storage::delete('public/absensi/selfie/' . $kehadiran->lampiran_foto);
        $kehadiran->delete();

        return redirect()->back()->with('success', 'Data kehadiran berhasil dihapus');
    }
}