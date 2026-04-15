<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->has('search')) {
            $query->where('username', 'like', '%' . $request->search . '%');
        }

        $users = $query->get();
        
        $admins = $users->filter(fn($u) => optional($u->role)->nama_role === 'admin');
        $gurus  = $users->filter(fn($u) => optional($u->role)->nama_role === 'guru');
        $siswas = $users->filter(fn($u) => optional($u->role)->nama_role === 'siswa');

        $roles = Role::all();
        $mapels = \App\Models\MataPelajaran::all();
        return view('users.index', compact('admins', 'gurus', 'siswas', 'roles', 'mapels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'id_role' => 'required'
        ]);

        $role = Role::find($request->id_role);
        
        if ($role && $role->nama_role === 'guru') {
            $request->validate([
                'id_mapel' => 'required|exists:mata_pelajarans,id_mapel'
            ], [
                'id_mapel.required' => 'Mata pelajaran wajib diisi untuk peran Guru.',
                'id_mapel.exists' => 'Mata pelajaran yang dipilih tidak valid.'
            ]);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'id_role' => $request->id_role
        ]);

        if ($role && $role->nama_role === 'guru') {
            \App\Models\Guru::create([
                'id_user' => $user->id_user,
                'nama' => $user->username,
                'jenis_kelamin' => 'L', // default, bisa diupdate guru nanti
                'id_mapel' => $request->id_mapel
            ]);
        }

        return redirect()->back()->with('success', 'User berhasil dibuat');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'username' => 'required|unique:users,username,'.$id.',id_user',
            'id_role' => 'required'
        ]);

        $data = [
            'username' => $request->username,
            'id_role' => $request->id_role,
        ];

        // Password hanya diupdate jika user mengisi form password
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        return redirect()->back()->with('success', 'User berhasil diupdate');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'User berhasil dihapus');
    }
}