<?php

namespace App\Http\Controllers;

use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MataPelajaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = MataPelajaran::all();
        return view('mata_pelajaran.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_mapel' => 'required|string|max:150',
            'kode_mapel' => 'nullable|string|max:50|unique:mata_pelajarans,kode_mapel',
        ]);

        MataPelajaran::create($request->all());

        return redirect()->route('mata-pelajaran.index')->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $mapel = MataPelajaran::findOrFail($id);

        $request->validate([
            'nama_mapel' => 'required|string|max:150',
            'kode_mapel' => 'nullable|string|max:50|unique:mata_pelajarans,kode_mapel,' . $id . ',id_mapel',
        ]);

        $mapel->update($request->all());

        return redirect()->route('mata-pelajaran.index')->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mapel = MataPelajaran::findOrFail($id);
        $mapel->delete();

        return redirect()->route('mata-pelajaran.index')->with('success', 'Mata Pelajaran berhasil dihapus.');
    }
}
