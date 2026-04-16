<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\ApiKelas;
use App\Models\Kelas;
use App\Models\Jurusan;

class ApiKelasSyncController extends Controller
{
    // Tampilkan halaman Index Daftar Draft Kelas
    public function index(Request $request)
    {
        $search = $request->input('search');
        $drafts = ApiKelas::where('is_activated', false)
            ->when($search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('jurusan_api', 'like', "%{$search}%")
                      ->orWhere('kelas_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama', 'asc')->get();
        return view('admin.kelas_api.index', compact('drafts'));
    }

    // Tarik data mentah dari API Pusat
    public function fetchApi()
    {
        try {
            $apiUrl = env('API_KELAS_URL', 'https://zieapi.zielabs.id/api/getkelas?tahun=2025');
            $response = Http::get($apiUrl);
            $json = $response->json();

            // API merespons dengan {"status": true, "data": [...]}
            if (!isset($json['status']) || $json['status'] !== true || !isset($json['data'])) {
                return redirect()->back()->with('error', 'Format data API Kelas tidak sesuai/gagal.');
            }

            $data = $json['data'];
            $countNew = 0;
            $countLink = 0;

            foreach ($data as $row) {
                if (!isset($row['id']) || empty($row['nama'])) {
                    continue;
                }

                // Cek apakah Kelas (Nama) ini SUDAH AKTIF ADA DI DATABASE MANUAL KITA?
                // Karena relasinya kelas_id dari API blm tentu ditambahkan sblmnya, nama_kelas jadi tumpuan paten
                $existingKelas = Kelas::where('nama_kelas', $row['nama'])->first();

                if ($existingKelas) {
                    $countLink++;
                } else {
                    // Jika benar-benar baru, simpan ke Staging Draft API !!!
                    $draft = ApiKelas::updateOrCreate(
                        ['kelas_id' => $row['id']],
                        [
                            'nama' => $row['nama'],
                            'jurusan_api' => $row['jurusan'] ?? '-'
                        ]
                    );

                    // Hanya hitung jika baru terbuat hari ini
                    if ($draft->wasRecentlyCreated) {
                        $countNew++;
                    }
                }
            }

            return redirect()->back()->with('success', "Berhasil menyinkronkan data API. Terdapat {$countNew} draf Kelas baru yang bisa difiksasi, dan {$countLink} Kelas sudah sinkron/terdapat di aplikasi lokal.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal terhubung ke API Kelas: ' . $e->getMessage());
        }
    }

    // Tampilkan View Form Pilihan Jurusan sebelum Fiksasi
    public function showAktivasiForm($id)
    {
        $draft = ApiKelas::findOrFail($id);
        if($draft->is_activated) {
            return redirect()->route('admin.api_kelas.index')->with('error', 'Kelas ini sudah pernah difiksasi sebelumnya.');
        }

        // Prediksi Tingkat berdasarkan Awalan Nama
        $prediksiTingkat = 10; // Default X
        $namaUpper = strtoupper(trim($draft->nama));
        if (str_starts_with($namaUpper, 'XII ')) {
            $prediksiTingkat = 12;
        } elseif (str_starts_with($namaUpper, 'XI ')) {
            $prediksiTingkat = 11;
        } elseif (str_starts_with($namaUpper, 'X ')) {
            $prediksiTingkat = 10;
        }

        // Ambil list Jurusan lokal
        $list_jurusan = Jurusan::orderBy('nama_jurusan', 'asc')->get();

        return view('admin.kelas_api.aktivasi', compact('draft', 'list_jurusan', 'prediksiTingkat'));
    }

    // Eksekusi Pindah Data Kelas
    public function aktivasiStore(Request $request, $id)
    {
        $draft = ApiKelas::findOrFail($id);
        
        $request->validate([
            'id_jurusan' => 'required|exists:jurusan,id_jurusan',
            'tingkat' => 'required|numeric|min:1|max:13'
        ]);

        // Cek Nama Kembar
        $cek = Kelas::where('nama_kelas', $draft->nama)->first();
        if($cek) {
             return redirect()->route('admin.api_kelas.index')->with('error', 'Hati-hati! Nama kelas ini sudah ada di Database utama Anda. Sistem membatalkan fiksasi ganda.');
        }

        // Eksekusi Database Transaction
        try {
            DB::transaction(function () use ($draft, $request) {
                // 1. Masukkan ke Tabel Kelas Utama
                $newKelas = Kelas::create([
                    'id_jurusan' => $request->id_jurusan,
                    'nama_kelas' => $draft->nama,
                    'tingkat' => $request->tingkat,
                ]);

                // 2. Update status staging
                $draft->update([
                    'is_activated' => true,
                    'id_kelas_lokal' => $newKelas->id_kelas
                ]);
            });

            return redirect()->route('admin.api_kelas.index')->with('success', "Sukses memfiksasi Kelas: {$draft->nama} masuk ke dalam list database Anda!");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memfiksasi Kelas: ' . $e->getMessage());
        }
    }
}
