<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SekolahSetting;
use App\Models\Location;

class SettingController extends Controller
{
    public function index()
    {
        $setting = SekolahSetting::first();
        if (!$setting) {
             $setting = SekolahSetting::create(['is_geofence_active' => true]);
        }
        
        $locations = Location::all();
        
        return view('settings.index', compact('setting', 'locations'));
    }

    public function toggleGeofence()
    {
        $setting = SekolahSetting::first();
        if ($setting) {
            $setting->update(['is_geofence_active' => !$setting->is_geofence_active]);
            $status = $setting->is_geofence_active ? 'AKTIF' : 'NONAKTIF';
            return redirect()->back()->with('success', "Geofencing berhasil di{$status}kan!");
        }
        return redirect()->back()->with('error', 'Pengaturan tidak ditemukan.');
    }

    public function storeLocation(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'required|integer|min:1',
        ]);

        Location::create($request->all());

        return redirect()->back()->with('success', 'Lokasi baru berhasil ditambahkan.');
    }

    public function updateLocation(Request $request, $id)
    {
        $request->validate([
            'nama_lokasi' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $location = Location::findOrFail($id);
        $location->update([
            'nama_lokasi' => $request->nama_lokasi,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meter' => $request->radius_meter,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->back()->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function deleteLocation($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return redirect()->back()->with('success', 'Lokasi berhasil dihapus.');
    }
}
