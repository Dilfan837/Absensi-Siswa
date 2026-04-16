<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointSetting;
use Illuminate\Http\Request;

class PointSettingController extends Controller
{
    public function index()
    {
        $settings = PointSetting::all();
        return view('admin.point-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required|integer',
        ]);

        foreach ($request->settings as $key => $value) {
            $setting = PointSetting::where('key', $key)->first();
            if ($setting) {
                $setting->update([
                    'value' => $value,
                    'updated_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Pengaturan Poin Integritas berhasil diperbarui.');
    }
}
