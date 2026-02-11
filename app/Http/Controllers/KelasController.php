<?php

namespace App\Http\Controllers;

use App\Models\Kelas; // Tambahkan ini
use App\Models\Jurusan; // Tambahkan ini agar bisa pilih jurusan saat input kelas
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Filter
        $search = $request->input('q');
        $tingkat = $request->input('tingkat');
        $jurusan_id = $request->input('id_jurusan');

        // 2. Query Data Kelas
        $kelas = Kelas::with('jurusan')
            ->when($search, function ($query, $search) {
                return $query->where('nama_kelas', 'like', "%{$search}%");
            })
            ->when($tingkat, function ($query, $tingkat) {
                return $query->where('tingkat', $tingkat);
            })
            ->when($jurusan_id, function ($query, $jurusan_id) {
                return $query->where('id_jurusan', $jurusan_id);
            })
            ->orderBy('tingkat', 'asc')
            ->orderBy('nama_kelas', 'asc')
            ->get();

        // 3. Data Jurusan untuk Dropdown Filter & Modal
        $jurusan = Jurusan::all();

        // 4. Kirim ke View
        return view('kelas.index', compact('kelas', 'jurusan'));
    }

    // Tambahan: Method Update
    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->update($request->all());
        return redirect()->back()->with('success', 'Data kelas berhasil diubah');
    }

    // Tambahan: Method Delete
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();
        return redirect()->back()->with('success', 'Kelas berhasil dihapus');
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'id_jurusan' => 'required',
            'tingkat'    => 'required',
            'nama_kelas' => 'required',
        ]);

        // 2. Simpan ke Database
        Kelas::create([
            'id_jurusan' => $request->id_jurusan,
            'tingkat'    => $request->tingkat,
            'nama_kelas' => $request->nama_kelas,
        ]);

        // 3. Balikkan ke halaman sebelumnya dengan pesan sukses
        return redirect()->back()->with('success', 'Kelas berhasil ditambahkan!');
    }
}