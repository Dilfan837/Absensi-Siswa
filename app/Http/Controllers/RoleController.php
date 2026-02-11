<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        // Validasi agar tidak ada nama role yang ganda
        $request->validate([
            'nama_role' => 'required|unique:roles,nama_role'
        ]);

        Role::create($request->all());
        return redirect()->back()->with('success', 'Role berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $request->validate([
            'nama_role' => 'required|unique:roles,nama_role,' . $id . ',id_role'
        ]);

        $role->update($request->all());
        return redirect()->back()->with('success', 'Nama role berhasil diubah');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // Proteksi: Jangan hapus role jika masih ada user yang menggunakannya
        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', 'Role tidak bisa dihapus karena masih digunakan oleh user');
        }

        $role->delete();
        return redirect()->back()->with('success', 'Role berhasil dihapus');
    }
}