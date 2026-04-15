<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // Untuk transaksi database
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data request search
        $search = $request->input('q');
        $kelas_id = $request->input('kelas');
        $gender = $request->input('gender');

        // 2. Query Builder dengan Filter
        $siswas = Siswa::with('kelas', 'user')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nis', 'like', "%{$search}%")
                      ->orWhere('nama_siswa', 'like', "%{$search}%");
                });
            })
            ->when($kelas_id, function ($query, $kelas_id) {
                return $query->where('id_kelas', $kelas_id);
            })
            ->when($gender, function ($query, $gender) {
                return $query->where('jenis_kelamin', $gender);
            })
            ->orderBy('nama_siswa', 'asc') // Opsional: urutkan nama
            ->get();

        // 3. Data Kelas untuk Dropdown Filter & Modal
        $list_kelas = \App\Models\Kelas::all();

        // 4. Kirim ke View
        return view('siswa.index', compact('siswas', 'list_kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|unique:siswa,nis|unique:users,username',
            'nama_siswa' => 'required',
            'id_kelas' => 'required',
            'jenis_kelamin' => 'required',
            'image_data' => 'required',
            'face_descriptor' => 'nullable', // Optional tapi sangat disarankan
        ]);

        $roleSiswa = DB::table('roles')->where('nama_role', 'siswa')->first();

        if (!$roleSiswa) {
            return redirect()->back()->with('error', 'Gagal: Role "siswa" tidak ditemukan.');
        }

        try {
            DB::transaction(function () use ($request, $roleSiswa) {

                // --- LOGIKA PROSES FOTO ---
                $img = $request->image_data;
                $image_parts = explode(";base64,", $img);
                $image_base64 = base64_decode($image_parts[1]);

                $fileName = $request->nis . '_' . time() . '.jpg';

                // Pastikan folder tujuan ada
                if (!Storage::disk('public')->exists('siswa')) {
                    Storage::disk('public')->makeDirectory('siswa');
                }

                // Simpan menggunakan disk 'public' agar langsung ke storage/app/public/siswa
                Storage::disk('public')->put('siswa/' . $fileName, $image_base64);

                // 1. Buat Akun User
                $user = User::create([
                    'name' => $request->nama_siswa,
                    'username' => $request->nis,
                    'password' => Hash::make($request->nis),
                    'id_role' => $roleSiswa->id_role,
                ]);

                // 2. Buat Data Siswa
                Siswa::create([
                    'id_user' => $user->id_user,
                    'nis' => $request->nis,
                    'nama_siswa' => $request->nama_siswa,
                    'id_kelas' => $request->id_kelas,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'foto' => $fileName,
                    'face_descriptor' => $request->face_descriptor, // Simpan descriptor wajah
                    'status_aktif' => true,
                ]);
            });

            return redirect()->back()->with('success', 'Siswa & Akun berhasil dibuat!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal simpan data: ' . $e->getMessage());
        }
    }
    public function update(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);

        $request->validate([
            'nis' => 'required|unique:siswa,nis,' . $id . ',id_siswa',
            'nama_siswa' => 'required',
            'id_kelas' => 'required',
        ]);

        $siswa->update([
            'nis' => $request->nis,
            'nama_siswa' => $request->nama_siswa,
            'id_kelas' => $request->id_kelas,
            'jenis_kelamin' => $request->has('jenis_kelamin') ? $request->jenis_kelamin : $siswa->jenis_kelamin,
        ]);

        // Update username di tabel users juga jika NIS berubah
        $siswa->user->update([
            'username' => $request->nis
        ]);

        return redirect()->back()->with('success', 'Data siswa berhasil diperbarui');
    }

    public function updateWajah(Request $request, $id)
    {
        $siswa = Siswa::findOrFail($id);
        
        $request->validate([
            'image_data' => 'required',
            'face_descriptor' => 'required',
        ]);
        
        try {
            DB::transaction(function () use ($request, $siswa) {
                // --- LOGIKA PROSES FOTO ---
                $img = $request->image_data;
                $image_parts = explode(";base64,", $img);
                $image_base64 = base64_decode($image_parts[1]);

                $fileName = $siswa->nis . '_face_' . time() . '.jpg';

                if (!Storage::disk('public')->exists('siswa')) {
                    Storage::disk('public')->makeDirectory('siswa');
                }
                
                // hapus foto lama jika ada
                if ($siswa->foto && Storage::disk('public')->exists('siswa/' . $siswa->foto)) {
                    Storage::disk('public')->delete('siswa/' . $siswa->foto);
                }

                Storage::disk('public')->put('siswa/' . $fileName, $image_base64);

                $siswa->update([
                    'foto' => $fileName,
                    'face_descriptor' => $request->face_descriptor,
                ]);
            });

            return redirect()->back()->with('success', 'Wajah siswa berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal simpan wajah: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $siswa = Siswa::findOrFail($id);

        // Hapus file foto dari storage
        if ($siswa->foto) {
            Storage::disk('public')->delete('siswa/' . $siswa->foto);
        }

        $user = User::findOrFail($siswa->id_user);
        $siswa->delete();
        $user->delete();

        return redirect()->back()->with('success', 'Data dan file foto berhasil dihapus');
    }
}