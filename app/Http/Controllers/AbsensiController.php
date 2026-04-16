<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\DetailAbsensi;
use App\Models\Location;
use App\Http\Controllers\Controller;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function index()
    {
        // === LAZY AUTO-CLOSE LOGIC ===
        // Cek sesi 'aktif' yang sudah lewat waktu, lalu tutup otomatis.
        // Logic: Tanggal < hari ini ATAU (Tanggal = hari ini DAN Jam Selesai < Jam Sekarang)
        $sekarang = now();
        $tanggal = $sekarang->toDateString();
        $jam = $sekarang->toTimeString();

        \App\Models\Absensi::where('status', 'aktif')
            ->where(function ($query) use ($tanggal, $jam) {
                $query->where('tanggal', '<', $tanggal)
                      ->orWhere(function ($q) use ($tanggal, $jam) {
                          $q->where('tanggal', $tanggal)
                            ->where('jam_selesai', '<', $jam);
                      });
            })
            ->update(['status' => 'selesai']);
        // =============================

        if (auth()->check() && auth()->user()->role->nama_role === 'guru') {
            $data = Absensi::with('kelas')->where('dibuat_oleh', auth()->id())->latest()->get();
        } else {
            $data = Absensi::with('kelas')->latest()->get();
        }
        $kelas = Kelas::all();
        
        $guruProfile = null;
        if (auth()->check() && auth()->user()->role->nama_role === 'guru') {
            $guruProfile = \App\Models\Guru::with('mataPelajaran')->where('id_user', auth()->id())->first();
        }

        return view('absensi.index', compact('data', 'kelas', 'guruProfile'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_kelas' => 'required',
            'nama_absensi' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        try {
            \DB::beginTransaction();

            $user = \App\Models\User::first();
            if (!$user) {
                return redirect()->back()->with('error', 'Tabel Users kosong!');
            }

            // 1. Simpan Sesi Absensi
            $absensi = Absensi::create([
                'id_kelas' => $request->id_kelas,
                'dibuat_oleh' => auth()->id() ?? $user->id_user,
                'nama_absensi' => $request->nama_absensi,
                'tanggal' => now()->toDateString(),
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'qr_token' => \Str::random(40),
                'status' => 'aktif',
            ]);

            // 2. AMBIL SISWA (Kunci agar daftar siswa tidak kosong)
            // Pastikan di model Siswa, id_kelas-nya sesuai
            $siswa = \App\Models\Siswa::where('id_kelas', $request->id_kelas)->get();

            if ($siswa->count() == 0) {
                \DB::rollBack();
                return redirect()->back()->with('error', 'Gagal! Tidak ada siswa di kelas yang dipilih.');
            }

            // 3. Masukkan ke Detail Absensi
            foreach ($siswa as $s) {
                \App\Models\DetailAbsensi::create([
                    'id_absensi' => $absensi->id_absensi,
                    'id_siswa' => $s->id_siswa,
                    'status' => 'alpha', // Default awal
                ]);
            }

            \DB::commit();
            return redirect()->route('absensi.index')->with('success', 'Sesi Berhasil Diaktifkan!');

        } catch (\Exception $e) {
            \DB::rollBack();
            dd("Gagal Simpan! Pesan Error: " . $e->getMessage());
        }
    }

    public function show($id)
    {
        // === LAZY AUTO-CLOSE SPECIFIC ===
        $cek = Absensi::find($id);
        if ($cek && $cek->status == 'aktif') {
            $sekarang = now();
            if ($cek->tanggal < $sekarang->toDateString() || 
               ($cek->tanggal == $sekarang->toDateString() && $cek->jam_selesai < $sekarang->toTimeString())) {
                $cek->update(['status' => 'selesai']);
            }
        }
        // ================================

        $absensi = Absensi::with(['kelas', 'details.siswa'])->findOrFail($id);

        // Ambil riwayat poin manual yang diberikan guru di sesi ini
        $manualPoints = \App\Models\PointLedger::where('id_absensi', $id)
            ->whereIn('transaction_type', ['REWARD', 'PENALTY'])
            ->select('id_siswa', \Illuminate\Support\Facades\DB::raw('SUM(amount) as total_point'))
            ->groupBy('id_siswa')
            ->pluck('total_point', 'id_siswa');

        return view('absensi.show', compact('absensi', 'manualPoints'));
    }

    public function tutupAbsensi($id)
    {
        $absensi = Absensi::with('details.siswa')->findOrFail($id);
        
        // Mencegah ekseskusi tutup sesi dan penalti berkali-kali jika sudah selesai
        if ($absensi->status === 'selesai') {
            return redirect()->back()->with('success', 'Sesi absen sudah ditutup sebelumnya.');
        }

        $absensi->update(['status' => 'selesai']);

        // === HOOK: Berikan PENALTY ke semua siswa yang masih ALPHA ===
        $pointService = new PointService();
        foreach ($absensi->details as $detail) {
            if ($detail->status === 'alpha') {
                try {
                    $pointService->penalty($detail->id_siswa, $absensi->id_absensi, $absensi->nama_absensi);
                } catch (\Exception $e) {
                    // Log error tapi jangan stop proses penutupan sesi
                    \Log::warning("Point penalty error siswa #{$detail->id_siswa}: " . $e->getMessage());
                }
            }
        }
        // ============================================================

        return redirect()->back()->with('success', 'Sesi absen ditutup.');
    }
    public function verifikasiWajah($id_absensi, $id_siswa)
    {
        $siswa = \App\Models\Siswa::findOrFail($id_siswa);
        $absensi = \App\Models\Absensi::findOrFail($id_absensi); // Validasi sesi ada
        return view('absensi.verifikasi', compact('siswa', 'absensi'));
    }

    public function sukses()
    {
        return view('absensi.sukses');
    }

    public function simpanKehadiran(Request $request)
    {
        try {
            $id_siswa = $request->id_siswa;
            $sekarang = now();
            $tanggalHariIni = $sekarang->toDateString();
            $jamSekarang = $sekarang->toTimeString();

            $id_absensi = $request->id_absensi;

            if (!$id_siswa || !$id_absensi) {
                return response()->json(['success' => false, 'message' => 'Data tidak lengkap.'], 400);
            }


            // === VALIDASI GEOFENCING (GPS) ===
            $settings = \App\Models\SekolahSetting::first();
            if ($settings && $settings->is_geofence_active) {
                $userLat = $request->latitude;
                $userLong = $request->longitude;

                if (!$userLat || !$userLong) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Lokasi Anda tidak terdeteksi. Pastikan GPS aktif dan izinkan akses lokasi.'
                    ], 400);
                }

                // Ambil semua lokasi aktif
                $locations = \App\Models\Location::active()->get();
                
                if ($locations->isEmpty()) {
                    // Fallback jika tidak ada lokasi aktif tapi geofence ON (Safety net)
                    // Atau bisa return error setting
                }

                $isWithinAnyLocation = false;
                $nearestDistance = 99999999;
                
                foreach ($locations as $loc) {
                    $distance = $this->calculateDistance(
                        $loc->latitude,
                        $loc->longitude,
                        $userLat,
                        $userLong
                    );

                    if ($distance <= $loc->radius_meter) {
                        $isWithinAnyLocation = true;
                        break; // Masuk salah satu lokasi, valid!
                    }
                    
                    if ($distance < $nearestDistance) {
                        $nearestDistance = $distance;
                    }
                }

                if (!$isWithinAnyLocation) {
                    // Cari lokasi terdekat untuk info debug
                    $nearestLocName = "Tidak diketahui";
                    $minDist = 999999999;
                    foreach ($locations as $loc) {
                        $d = $this->calculateDistance($loc->latitude, $loc->longitude, $userLat, $userLong);
                        if ($d < $minDist) {
                            $minDist = $d;
                            $nearestLocName = $loc->nama_lokasi ?? 'Unnamed';
                        }
                    }

                    return response()->json([
                        'success' => false,
                        'message' => "Anda berada di luar jangkauan lokasi sekolah '{$nearestLocName}'! \nJarak Anda: " . round($minDist) . " meter. \nLokasi Anda: {$userLat}, {$userLong}",
                        'debug' => [
                            'user_lat' => $userLat,
                            'user_long' => $userLong,
                            'nearest_distance' => $minDist
                        ]
                    ], 403);
                }
            }
            // ====================================

            // Cari detail absensi SPESIFIK berdasarkan ID Sesi & ID Siswa
            $detail = \App\Models\DetailAbsensi::where('id_siswa', $id_siswa)
                ->where('id_absensi', $id_absensi)
                ->first();

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak ada sesi absen aktif yang sesuai dengan waktu saat ini."
                ], 404);
            }

            // Cek apakah sudah absen sebelumnya
            if ($detail->status !== 'alpha') {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah tercatat ' . strtoupper($detail->status) . ' pada jam ' . ($detail->waktu_scan ? $detail->waktu_scan->format('H:i') : '-')
                ]);
            }

            // Gunakan Database Transaction agar data aman
            \DB::transaction(function () use ($detail, $sekarang, $request) {
                // 1. Update status di detail absensi utama
                $detail->update([
                    'status'     => 'hadir',
                    'waktu_scan' => $sekarang
                ]);

                // 2. Simpan Foto Selfie ke storage
                $fileName = null;
                if ($request->foto_selfie) {
                    $img = $request->foto_selfie;
                    $folderPath = "public/absensi/selfie/";
                    $image_parts = explode(";base64,", $img);
                    
                    if (count($image_parts) >= 2) {
                        $image_base64 = base64_decode($image_parts[1]);
                        $fileName = uniqid() . '.png';
                        $file = $folderPath . $fileName;
                        \Illuminate\Support\Facades\Storage::put($file, $image_base64);
                    }
                }

                // 3. Catat di tabel Kehadiran sebagai Log Wajah & Waktu Akurat
                \App\Models\Kehadiran::create([
                    'id_absensi'       => $detail->id_absensi,
                    'id_siswa'         => $detail->id_siswa,
                    'waktu_absen'      => $sekarang,
                    'status_kehadiran' => 'Hadir',
                    'lampiran_foto'    => $fileName,
                    'keterangan'       => 'Hadir melalui sistem QR & Face Recognition'
                ]);

                // 4. === HOOK: Catat EARN poin ke ledger ===
                $absensi = \App\Models\Absensi::find($detail->id_absensi);
                if ($absensi) {
                    try {
                        (new PointService())->earn($detail->id_siswa, $detail->id_absensi, $absensi->nama_absensi);
                    } catch (\Exception $e) {
                        \Log::warning("Point earn error siswa #{$detail->id_siswa}: " . $e->getMessage());
                    }
                }
                // ==========================================
            });

            return response()->json([
                'success' => true,
                'nama' => $detail->siswa->nama_siswa,
                'waktu' => $sekarang->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function scanProses(Request $request)
    {
        $id_siswa = $request->id_siswa;
        $qr_token = $request->qr_token;

        // Cari sesi absensi aktif
        $absensi = Absensi::where('qr_token', $qr_token)
            ->where('status', 'aktif')
            ->first();

        if (!$absensi) {
            return response()->json(['status' => 'error', 'message' => 'QR tidak valid atau sesi sudah ditutup.']);
        }

        // Cek apakah siswa ada di kelas ini
        $detail = DetailAbsensi::where('id_absensi', $absensi->id_absensi)
            ->where('id_siswa', $id_siswa)
            ->first();

        if (!$detail) {
            // Periksa apakah siswa sebenarnya ada di kelas ini
            $siswa = \App\Models\Siswa::find($id_siswa);
            if ($siswa && $siswa->id_kelas == $absensi->id_kelas) {
                $detail = DetailAbsensi::create([
                    'id_absensi' => $absensi->id_absensi,
                    'id_siswa' => $id_siswa,
                    'status' => 'alpha'
                ]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Anda tidak terdaftar di sesi ini.']);
            }
        }

        // JIKA QR DISCAN LAGI PADAHAL SUDAH HADIR
        if ($detail->status === 'hadir') {
            return response()->json(['status' => 'warning', 'message' => 'Anda sudah hadir sebelumnya.']);
        }

        // SUKSES: Hanya arahkan ke halaman verifikasi wajah tanpa merubah status database
        return response()->json([
            'status' => 'success',
            'message' => 'QR Valid! Lanjutkan verifikasi wajah.',
            'redirect' => route('absensi.verifikasi', ['id_absensi' => $absensi->id_absensi, 'id_siswa' => $id_siswa])
        ]);
    }
    
    // === FITUR MANUAL INPUT (SAKIT, IZIN, DISPEN) ===
    public function updateStatus(Request $request, $id_detail)
    {
        $request->validate([
            'status' => 'required|in:hadir,alpha,izin,sakit,dispen',
            'keterangan' => 'nullable|string|max:255'
        ]);

        $detail = DetailAbsensi::findOrFail($id_detail);
        
        $updateData = [
            'status' => $request->status,
            'keterangan' => $request->keterangan
        ];

        // Jika diubah jadi hadir manual, set waktu scan sekarang jika belum ada
        if ($request->status == 'hadir' && !$detail->waktu_scan) {
            $updateData['waktu_scan'] = now();
        } 
        // Jika status selain hadir, reset waktu scan (opsional, tapi biasanya S/I/D tidak punya waktu scan)
        else if ($request->status != 'hadir') {
            $updateData['waktu_scan'] = null;
        }

        $detail->update($updateData);

        return redirect()->back()->with('success', 'Status kehadiran siswa berhasil diperbarui.');
    }



    // === FUNGSI HAVERSINE: HITUNG JARAK ANTARA 2 TITIK GPS (dalam METER) ===
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c; // Jarak dalam meter

        return $distance;
    }
}