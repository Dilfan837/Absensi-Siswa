<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use Illuminate\Http\Request;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

        $jurusan = Jurusan::when($search, function ($query, $search) {
            return $query->where('nama_jurusan', 'like', "%{$search}%");
        })->orderBy('nama_jurusan', 'asc')->get();

        return view('jurusan.index', compact('jurusan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jurusan' => 'required|unique:jurusan,nama_jurusan'
        ]);

        Jurusan::create($request->all());
        return redirect()->back()->with('success', 'Jurusan berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $jurusan = Jurusan::findOrFail($id);

        $request->validate([
            'nama_jurusan' => 'required|unique:jurusan,nama_jurusan,' . $id . ',id_jurusan'
        ]);

        $jurusan->update($request->all());
        return redirect()->back()->with('success', 'Nama jurusan berhasil diubah');
    }

    public function destroy($id)
    {
        $jurusan = Jurusan::findOrFail($id);

        // Proteksi: Cek apakah jurusan masih memiliki kelas
        if ($jurusan->kelas()->count() > 0) {
            return redirect()->back()->with('error', 'Jurusan tidak bisa dihapus karena masih memiliki data Kelas!');
        }

        $jurusan->delete();
        return redirect()->back()->with('success', 'Jurusan berhasil dihapus');
    }
}