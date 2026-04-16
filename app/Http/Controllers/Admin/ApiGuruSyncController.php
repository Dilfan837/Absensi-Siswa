<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\ApiGuru;
use App\Models\Guru;
use App\Models\User;
use App\Models\Role;
use App\Models\MataPelajaran;

class ApiGuruSyncController extends Controller
{
    // Tampilkan halaman Index Daftar Draft Guru
    public function index(Request $request)
    {
        $search = $request->input('search');
        $drafts = ApiGuru::where('is_activated', false)
            ->when($search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%")
                      ->orWhere('nuptk', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama', 'asc')->get();
        return view('admin.guru_api.index', compact('drafts'));
    }

    // Tarik data mentah dari API Pusat
    public function fetchApi()
    {
        try {
            $apiUrl = env('API_GURU_URL', 'https://zieapi.zielabs.id/api/getguru?tahun=2025');
            $response = Http::get($apiUrl);
            $data = $response->json();

            if (!is_array($data)) {
                return redirect()->back()->with('error', 'Format data API tidak sesuai (Bukan Murni JSON Array).');
            }

            $countNew = 0;
            $countLink = 0;

            foreach ($data as $row) {
                // Skiping data kosong yg gak wajar
                if (!isset($row['guru_id']) || empty($row['nama'])) {
                    continue;
                }

                // Cek apakah Guru ini (UUID/NIP) SUDAH AKTIF ADA DI DATABASE MANUAL KITA?
                $existingGuru = Guru::where('guru_id', $row['guru_id'])
                                      ->orWhere(function($q) use ($row) {
                                          if(!empty($row['nip']) && trim($row['nip']) !== '') {
                                              $q->where('nip', $row['nip']);
                                          }
                                      })->first();

                if ($existingGuru) {
                    // Update Biodata agar sinkron API tanpa merusak relasi dan akun lokal
                    $existingGuru->update([
                        'nuptk' => trim($row['nuptk']) !== '' ? $row['nuptk'] : $existingGuru->nuptk,
                        'nik' => $row['nik'] ?? $existingGuru->nik,
                        'jenis_kelamin' => $row['jenis_kelamin'] ?? $existingGuru->jenis_kelamin,
                        'tempat_lahir' => $row['tempat_lahir'] ?? $existingGuru->tempat_lahir,
                        'tanggal_lahir' => ($row['tanggal_lahir'] && $row['tanggal_lahir'] != '1970-01-01') ? $row['tanggal_lahir'] : $existingGuru->tanggal_lahir,
                        'email' => $row['email'] ?? $existingGuru->email,
                    ]);
                    $countLink++;
                } else {
                    // Jika benar-benar baru, simpan ke Staging Draft API !!!
                    $draft = ApiGuru::updateOrCreate(
                        ['guru_id' => $row['guru_id']],
                        [
                            'nama' => $row['nama'],
                            'nuptk' => trim($row['nuptk']) !== '' ? trim($row['nuptk']) : null,
                            'nip' => trim($row['nip']) !== '' ? trim($row['nip']) : null,
                            'jenis_kelamin' => $row['jenis_kelamin'] ?? 'L',
                            'tempat_lahir' => cloneOrNull($row['tempat_lahir']),
                            'tanggal_lahir' => ($row['tanggal_lahir'] && $row['tanggal_lahir'] != '1970-01-01') ? $row['tanggal_lahir'] : null,
                            'nik' => cloneOrNull($row['nik']),
                            'email' => cloneOrNull($row['email']),
                            'no_hp' => cloneOrNull($row['no_hp'])
                        ]
                    );

                    // Hanya hitung jika baru terbuat hari ini
                    if ($draft->wasRecentlyCreated) {
                        $countNew++;
                    }
                }
            }

            return redirect()->back()->with('success', "Berhasil menyinkronkan data API. Terdapat {$countNew} draf Guru baru, dan {$countLink} data ter-update di database lokal aslinya.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal terhubung ke API: ' . $e->getMessage());
        }
    }

    // Tampilkan View Form Pilihan Mapel sebelum Fiksasi
    public function showAktivasiForm($id)
    {
        $draft = ApiGuru::findOrFail($id);
        if($draft->is_activated) {
            return redirect()->route('admin.api_guru.index')->with('error', 'Guru ini sudah pernah difiksasi sebelumnya.');
        }

        // Kita biarkan admin memilih satu mapel dari database saat ini
        $list_mapel = MataPelajaran::orderBy('nama_mapel', 'asc')->get();

        return view('admin.guru_api.aktivasi', compact('draft', 'list_mapel'));
    }

    // Eksekusi Pindah Data && Buat User Akun
    public function aktivasiStore(Request $request, $id)
    {
        $draft = ApiGuru::findOrFail($id);
        
        $request->validate([
            'id_mapel' => 'required|exists:mata_pelajarans,id_mapel',
            // Default input jika NIP di API kosongan
            'nip_manual' => 'nullable|string'
        ]);

        // Cek NIP
        $finalNIP = $draft->nip;
        if(empty($finalNIP)) {
            if(empty($request->nip_manual)) {
                 return redirect()->back()->with('error', 'Data API ini tidak memiliki NIP. Harap masukkan NIP secara manual untuk generate Username Akun.');
            }
            $finalNIP = $request->nip_manual;
        }

        // Cek kalau NIP sudah ada yg pakai di tabel user atau guru
        $cekNipUser = User::where('username', $finalNIP)->first();
        if($cekNipUser) {
            return redirect()->back()->with('error', 'NIP/Username ini sudah terdaftar di Tabel Users! Harap gunakan NIP pengganti jika bentrok.');
        }

        // Eksekusi Database Transaction
        try {
            DB::transaction(function () use ($draft, $request, $finalNIP) {
                // 1. Ambil Role Guru
                $roleGuru = Role::where('nama_role', 'guru')->firstOrFail();

                // 2. Buat Akun User
                $newUser = User::create([
                    'name' => $draft->nama,
                    'username' => $finalNIP, 
                    'password' => Hash::make($finalNIP), // password default = NIP
                    'id_role' => $roleGuru->id_role,
                ]);

                // 3. Masukkan ke Tabel Guru Utama
                $newGuru = Guru::create([
                    'id_user' => $newUser->id_user,
                    'guru_id' => $draft->guru_id,
                    'nip' => $finalNIP,
                    'nuptk' => $draft->nuptk,
                    'nik' => $draft->nik,
                    'nama' => $draft->nama,
                    'jenis_kelamin' => $draft->jenis_kelamin,
                    'tempat_lahir' => $draft->tempat_lahir,
                    'tanggal_lahir' => $draft->tanggal_lahir,
                    'email' => $draft->email,
                    'no_hp' => $draft->no_hp,
                    'id_mapel' => $request->id_mapel,
                    'status_aktif' => true,
                ]);

                // 4. Update status staging
                $draft->update([
                    'is_activated' => true,
                    'id_guru_lokal' => $newGuru->id_guru
                ]);
            });

            return redirect()->route('admin.api_guru.index')->with('success', "Sukses memfiksasi Guru: {$draft->nama}! Akun otomatis terbuat menggunakan NIP.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memfiksasi Guru: ' . $e->getMessage());
        }
    }
}

// Helper lokal mencegah undefined atau null crash
function cloneOrNull($value) {
    return ($value && trim($value) !== '') ? trim($value) : null;
}
