<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use App\Models\Role;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');
        $gender = $request->input('gender');
        $id_mapel = $request->input('id_mapel');

        $gurus = Guru::with(['mataPelajaran', 'user'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->when($gender, function ($query, $gender) {
                return $query->where('jenis_kelamin', $gender);
            })
            ->when($id_mapel, function ($query, $id_mapel) {
                return $query->where('id_mapel', $id_mapel);
            })
            ->latest()
            ->get();
            
        $mapels = MataPelajaran::all();
        
        return view('guru.indexAdmin', compact('gurus', 'mapels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:guru,nip|unique:users,username',
            'nama' => 'required|string|max:150',
            'email' => 'nullable|email|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'id_mapel' => 'required|exists:mata_pelajarans,id_mapel',
            'photo' => 'nullable|image|max:2048'
        ]);

        $roleGuru = Role::where('nama_role', 'guru')->first();
        if (!$roleGuru) {
            return redirect()->back()->with('error', 'Role Guru tidak ditemukan di database.');
        }

        try {
            DB::transaction(function () use ($request, $roleGuru) {
                // Proses Foto
                $fileName = null;
                if ($request->hasFile('photo')) {
                    $file = $request->file('photo');
                    $fileName = $request->nip . '_' . time() . '.' . $file->getClientOriginalExtension();
                    
                    if (!Storage::disk('public')->exists('guru')) {
                        Storage::disk('public')->makeDirectory('guru');
                    }
                    $file->storeAs('guru', $fileName, 'public');
                }

                // Buat User
                $user = User::create([
                    'name' => $request->nama,
                    'username' => $request->nip, // NIP jadi Username
                    'password' => Hash::make($request->nip), // Default password = NIP
                    'id_role' => $roleGuru->id_role,
                ]);

                // Buat Guru
                Guru::create([
                    'id_user' => $user->id_user,
                    'nip' => $request->nip,
                    'nama' => $request->nama,
                    'email' => $request->email,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'id_mapel' => $request->id_mapel,
                    'photo' => $fileName,
                    'status_aktif' => true,
                ]);
            });

            return redirect()->route('guru.index')->with('success', 'Data Guru dan Akun berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan Guru: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::findOrFail($id);

        $request->validate([
            'nip' => 'required|unique:guru,nip,' . $id . ',id_guru',
            'nama' => 'required|string|max:150',
            'email' => 'nullable|email|max:100',
            'jenis_kelamin' => 'required|in:L,P',
            'id_mapel' => 'required|exists:mata_pelajarans,id_mapel',
            'photo' => 'nullable|image|max:2048'
        ]);

        // Validate username uniqueness (excluding the current user's ID)
        if ($guru->id_user) {
            $request->validate([
                'nip' => 'unique:users,username,' . $guru->id_user . ',id_user'
            ]);
        }

        try {
            DB::transaction(function () use ($request, $guru) {
                // Proses Foto
                $fileName = $guru->photo;
                if ($request->hasFile('photo')) {
                    // Hapus foto lama
                    if ($fileName && Storage::disk('public')->exists('guru/' . $fileName)) {
                        Storage::disk('public')->delete('guru/' . $fileName);
                    }

                    $file = $request->file('photo');
                    $fileName = $request->nip . '_' . time() . '.' . $file->getClientOriginalExtension();
                    if (!Storage::disk('public')->exists('guru')) {
                        Storage::disk('public')->makeDirectory('guru');
                    }
                    $file->storeAs('guru', $fileName, 'public');
                }

                // Update Guru
                $guru->update([
                    'nip' => $request->nip,
                    'nama' => $request->nama,
                    'email' => $request->email,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'id_mapel' => $request->id_mapel,
                    'photo' => $fileName,
                ]);

                // Update User
                if ($guru->user) {
                    $guru->user->update([
                        'name' => $request->nama,
                        'username' => $request->nip,
                    ]);
                }
            });

            return redirect()->route('guru.index')->with('success', 'Data Guru berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui Guru: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $guru = Guru::findOrFail($id);
            
            DB::transaction(function () use ($guru) {
                // Hapus foto jika ada
                if ($guru->photo && Storage::disk('public')->exists('guru/' . $guru->photo)) {
                    Storage::disk('public')->delete('guru/' . $guru->photo);
                }

                $user = $guru->user;
                
                // Jika ingin menghapus Guru, user juga harus dihapus
                $guru->delete();
                if ($user) {
                    $user->delete();
                }
            });

            return redirect()->route('guru.index')->with('success', 'Data Guru dan Akun berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
