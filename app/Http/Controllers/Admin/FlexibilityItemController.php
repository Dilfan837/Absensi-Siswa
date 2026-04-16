<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlexibilityItem;
use Illuminate\Http\Request;

class FlexibilityItemController extends Controller
{
    public function index()
    {
        $items = FlexibilityItem::latest()->get();
        return view('admin.marketplace.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'item_type' => 'required|in:BEBAS_ALPHA,WFH,IZIN_MENDADAK,TOLERANSI_TELAT,CUSTOM',
            'point_cost' => 'required|integer|min:0',
            'stock_limit' => 'nullable|integer|min:1',
        ]);

        FlexibilityItem::create([
            'item_name' => $request->item_name,
            'description' => $request->description,
            'item_type' => $request->item_type,
            'requires_active_session' => $request->item_type === 'BEBAS_ALPHA', // Set auto true if BEBAS_ALPHA
            'point_cost' => $request->point_cost,
            'stock_limit' => $request->stock_limit,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Item kelonggaran berhasil ditambahkan ke Marketplace.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'point_cost' => 'required|integer|min:0',
            'stock_limit' => 'nullable|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        $item = FlexibilityItem::findOrFail($id);
        $item->update([
            'item_name' => $request->item_name,
            'description' => $request->description,
            'point_cost' => $request->point_cost,
            'stock_limit' => $request->stock_limit,
            'is_active' => $request->is_active,
        ]);

        return redirect()->back()->with('success', 'Item marketplace berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = FlexibilityItem::findOrFail($id);
        $item->delete();

        return redirect()->back()->with('success', 'Item marketplace berhasil dihapus.');
    }
}
