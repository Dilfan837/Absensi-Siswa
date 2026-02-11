<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Guru::query();

        // Search
        if ($request->has('q') && $request->q != '') {
            $q = $request->q;
            $query->where(function($builder) use ($q) {
                $builder->where('nama', 'like', "%{$q}%")
                        ->orWhere('nip', 'like', "%{$q}%")
                        ->orWhere('nuptk', 'like', "%{$q}%");
            });
        }

        $gurus = $query->paginate(10);

        return view('guru.index', compact('gurus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Optional: Implement if manual addition is needed
        return view('guru.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation and storage logic
    }

    /**
     * Display the specified resource.
     */
    public function show(Guru $guru)
    {
        return view('guru.show', compact('guru'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guru $guru)
    {
        return view('guru.edit', compact('guru'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Guru $guru)
    {
        // Validation and update logic
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guru $guru)
    {
        // Optional: Delete related user first or rely on cascade
        if ($guru->user) {
            $guru->user->delete();
        }
        $guru->delete();

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil dihapus');
    }
}
