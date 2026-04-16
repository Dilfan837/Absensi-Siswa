<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\ApiSiswa;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;

class ApiSyncController extends Controller
{
    /**
     * Halaman Gudang Draft (API Siswa)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $drafts = ApiSiswa::where('is_activated', false)
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('no_induk', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama')
            ->get();
            
        return view('admin.siswa_api.index', compact('drafts'));
    }

    /**
     * Menarik Data dari API PUSAT (Fase 1: Staging)
     */
    public function fetchApi()
    {
        try {
            $apiUrl = env('API_SISWA_URL', 'https://zieapi.zielabs.id/api/getsiswa?tahun=2025');
            $response = Http::get($apiUrl);
            $data = $response->json();

            if ($data['status'] !== true || !isset($data['data'])) {
                return redirect()->back()->with('error', 'Format respons API tidak sesuai.');
            }

            $countNew = 0;
            $countUpdated = 0;

            foreach ($data['data'] as $item) {
                // Gunakan updateOrCreate untuk menghindari duplikasi draft
                // Prioritas berdasar peserta_didik_id (UUID API)
                $apiSiswa = ApiSiswa::where('peserta_didik_id', $item['peserta_didik_id'])->first();
                
                if (!$apiSiswa && $item['nisn']) {
                    $apiSiswa = ApiSiswa::where('nisn', $item['nisn'])->first();
                }

                if (!$apiSiswa && $item['no_induk']) {
                    $apiSiswa = ApiSiswa::where('no_induk', $item['no_induk'])->first();
                }

                if ($apiSiswa) {
                    $countUpdated++;
                    $apiSiswa->update([
                        'sekolah_id' => $item['sekolah_id'],
                        'nama' => $item['nama'],
                        'no_induk' => $item['no_induk'],
                        'nisn' => $item['nisn'],
                        'nik' => $item['nik'],
                        'jenis_kelamin' => $item['jenis_kelamin'],
                        'tempat_lahir' => $item['tempat_lahir'],
                        'tanggal_lahir' => $item['tanggal_lahir'] === '0000-00-00' ? null : $item['tanggal_lahir'],
                        'agama_id' => $item['agama_id'],
                        'anak_ke' => $item['anak_ke'],
                        'alamat' => $item['alamat'],
                        'rt' => $item['rt'],
                        'rw' => $item['rw'],
                        'desa_kelurahan' => $item['desa_kelurahan'],
                        'kecamatan' => $item['kecamatan'],
                        'kode_pos' => $item['kode_pos'],
                        'no_telp' => $item['no_telp'],
                        'sekolah_asal' => $item['sekolah_asal'],
                        'diterima_kelas' => $item['diterima_kelas'],
                        'diterima_kelas_smk' => $item['diterima_kelas_smk'],
                        'rombel_id' => $item['rombel_id'],
                        'nama_rombel' => $item['nama_rombel'],
                        'nama_ayah' => $item['nama_ayah'],
                        'nama_ibu' => $item['nama_ibu'],
                        'nama_wali' => $item['nama_wali'],
                    ]);
                } else {
                    $countNew++;
                    ApiSiswa::create([
                        'peserta_didik_id' => $item['peserta_didik_id'],
                        'sekolah_id' => $item['sekolah_id'],
                        'nama' => $item['nama'],
                        'no_induk' => $item['no_induk'],
                        'nisn' => $item['nisn'],
                        'nik' => $item['nik'],
                        'jenis_kelamin' => $item['jenis_kelamin'],
                        'tempat_lahir' => $item['tempat_lahir'],
                        'tanggal_lahir' => $item['tanggal_lahir'] === '0000-00-00' ? null : $item['tanggal_lahir'],
                        'agama_id' => $item['agama_id'],
                        'anak_ke' => $item['anak_ke'],
                        'alamat' => $item['alamat'],
                        'rt' => $item['rt'],
                        'rw' => $item['rw'],
                        'desa_kelurahan' => $item['desa_kelurahan'],
                        'kecamatan' => $item['kecamatan'],
                        'kode_pos' => $item['kode_pos'],
                        'no_telp' => $item['no_telp'],
                        'sekolah_asal' => $item['sekolah_asal'],
                        'diterima_kelas' => $item['diterima_kelas'],
                        'diterima_kelas_smk' => $item['diterima_kelas_smk'],
                        'rombel_id' => $item['rombel_id'],
                        'nama_rombel' => $item['nama_rombel'],
                        'nama_ayah' => $item['nama_ayah'],
                        'nama_ibu' => $item['nama_ibu'],
                        'nama_wali' => $item['nama_wali'],
                        'is_activated' => false
                    ]);
                }
            }

            return redirect()->back()->with('success', "Berhasil menarik data API. Draft Baru: $countNew, Draft Diupdate: $countUpdated.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menarik API: ' . $e->getMessage());
        }
    }

    /**
     * Halaman Fiksasi Data / Pendaftaran Wajah
     */
    public function aktivasiForm($id)
    {
        $draft = ApiSiswa::findOrFail($id);
        
        // Cek apakah siswa ini duluan di-input manual di db `siswa`
        $existingSiswa = null;
        if($draft->no_induk) {
            $existingSiswa = Siswa::where('nis', $draft->no_induk)->first();
        }

        if ($existingSiswa && $existingSiswa->face_descriptor != null) {
            // Sudah fiksasi (Punya data & Wajah)
            $draft->update(['is_activated' => true]);
            return redirect()->route('admin.api_siswa.index')->with('success', 'Siswa ' . $draft->nama . ' rupanya sudah aktif dan memiliki wajah.');
        }

        $list_kelas = Kelas::all();
        return view('admin.siswa_api.aktivasi', compact('draft', 'list_kelas', 'existingSiswa'));
    }

    /**
     * Memproses aktivitas fiksasi dan menyalin ke tabel Siswa & User Resmi
     */
    public function aktivasiStore(Request $request, $id)
    {
        $draft = ApiSiswa::findOrFail($id);

        $request->validate([
            'id_kelas' => 'required',
            'image_data' => 'required',
            'face_descriptor' => 'required',
        ]);

        try {
            DB::transaction(function () use ($request, $draft) {
                // Proses Gambar Wajah
                $img = $request->image_data;
                $image_parts = explode(";base64,", $img);
                $image_base64 = base64_decode($image_parts[1]);
                $nisSiswa = $draft->no_induk ?? time(); // Fallback jika NIS kosong
                $fileName = $nisSiswa . '_face_' . time() . '.jpg';

                if (!Storage::disk('public')->exists('siswa')) {
                    Storage::disk('public')->makeDirectory('siswa');
                }
                Storage::disk('public')->put('siswa/' . $fileName, $image_base64);

                // Cek apakah Siswa ini sudah pernah diinput manual tapi belum direkam wajah
                $siswa = Siswa::where('nis', $draft->no_induk)->first();

                if ($siswa) {
                    // Update data manual yang ada menjadi lengkap via API
                    if ($siswa->foto && Storage::disk('public')->exists('siswa/' . $siswa->foto)) {
                        Storage::disk('public')->delete('siswa/' . $siswa->foto);
                    }
                    $siswa->update([
                        'id_kelas' => $request->id_kelas,
                        'foto' => $fileName,
                        'face_descriptor' => $request->face_descriptor,
                        // Extended API data mapping
                        'peserta_didik_id' => $draft->peserta_didik_id,
                        'nisn' => $draft->nisn,
                        'nik' => $draft->nik,
                        'no_induk' => $draft->no_induk,
                        'tempat_lahir' => $draft->tempat_lahir,
                        'tanggal_lahir' => $draft->tanggal_lahir,
                        'agama_id' => $draft->agama_id,
                        'anak_ke' => $draft->anak_ke,
                        'email' => $draft->no_telp . '@dummy.com', // Opsional, bisa diubah
                        'no_telp' => $draft->no_telp,
                        'alamat' => $draft->alamat,
                        'rt' => $draft->rt,
                        'rw' => $draft->rw,
                        'desa_kelurahan' => $draft->desa_kelurahan,
                        'kecamatan' => $draft->kecamatan,
                        'kode_pos' => $draft->kode_pos,
                        'sekolah_asal' => $draft->sekolah_asal,
                        'diterima_kelas_smk' => $draft->diterima_kelas_smk,
                        'nama_rombel' => $draft->nama_rombel,
                        'rombel_id' => $draft->rombel_id,
                        'nama_ayah' => $draft->nama_ayah,
                        'nama_ibu' => $draft->nama_ibu,
                        'nama_wali' => $draft->nama_wali,
                        'last_synced_at' => now()
                    ]);
                } else {
                    // BUAT AKUN USER BARU & SISWA BARU
                    $roleSiswa = DB::table('roles')->where('nama_role', 'siswa')->first();
                    $user = User::create([
                        'name' => $draft->nama,
                        'username' => $draft->no_induk,
                        'password' => Hash::make($draft->no_induk),
                        'id_role' => $roleSiswa->id_role,
                    ]);

                    Siswa::create([
                        'id_user' => $user->id_user,
                        'nis' => $draft->no_induk,
                        'nama_siswa' => $draft->nama,
                        'id_kelas' => $request->id_kelas,
                        'jenis_kelamin' => $draft->jenis_kelamin,
                        'status_aktif' => true,
                        'foto' => $fileName,
                        'face_descriptor' => $request->face_descriptor,
                        
                        // Extended API data mapping
                        'peserta_didik_id' => $draft->peserta_didik_id,
                        'nisn' => $draft->nisn,
                        'nik' => $draft->nik,
                        'no_induk' => $draft->no_induk,
                        'tempat_lahir' => $draft->tempat_lahir,
                        'tanggal_lahir' => $draft->tanggal_lahir,
                        'agama_id' => $draft->agama_id,
                        'anak_ke' => $draft->anak_ke,
                        'no_telp' => $draft->no_telp,
                        'alamat' => $draft->alamat,
                        'rt' => $draft->rt,
                        'rw' => $draft->rw,
                        'desa_kelurahan' => $draft->desa_kelurahan,
                        'kecamatan' => $draft->kecamatan,
                        'kode_pos' => $draft->kode_pos,
                        'sekolah_asal' => $draft->sekolah_asal,
                        'diterima_kelas_smk' => $draft->diterima_kelas_smk,
                        'nama_rombel' => $draft->nama_rombel,
                        'rombel_id' => $draft->rombel_id,
                        'nama_ayah' => $draft->nama_ayah,
                        'nama_ibu' => $draft->nama_ibu,
                        'nama_wali' => $draft->nama_wali,
                        'last_synced_at' => now()
                    ]);
                }

                // Tandai Draf sudah diaktivasi (sistem history / labelling)
                $draft->update([
                    'is_activated' => true
                ]);
            });

            return redirect()->route('admin.api_siswa.index')->with('success', 'Wajah berhasil didaftarkan! Data siswa "'.$draft->nama.'" resmi ditambahkan ke Data Asli.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat fiksasi: ' . $e->getMessage());
        }
    }
}
