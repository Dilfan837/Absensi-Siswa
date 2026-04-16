<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\FlexibilityItem;
use App\Models\UserToken;
use App\Models\DetailAbsensi;
use App\Services\PointService;
use Illuminate\Http\Request;

class SiswaWalletController extends Controller
{
    /**
     * Dashboard dompet (3 Tab: Mutasi, Marketplace, My Inventory)
     */
    public function index()
    {
        $siswa = auth()->user()->siswa;
        
        $pointBalance = $siswa->getPointBalance();
        $levelInfo = $siswa->getIntegrityLevel();
        
        $mutations = $siswa->pointLedgers()->with(['absensi', 'guru'])->latest()->get();
        
        // Marketplace items
        $items = FlexibilityItem::active()->get()->map(function($item) use ($siswa) {
            $item->available_for_me = $item->isAvailableForSiswa($siswa->id_siswa);
            return $item;
        });

        // Inventory Tokens
        $inventory = $siswa->userTokens()->with(['item', 'absensiUsed'])->latest()->get();

        // Cari sesi absensi yg masih aktif dan siswa status-nya "alpha", untuk token BEBAS_ALPHA
        $activeAlphaSessions = DetailAbsensi::with('absensi')
            ->where('id_siswa', $siswa->id_siswa)
            ->where('status', 'alpha')
            ->whereHas('absensi', function($q) {
                $q->where('status', 'aktif');
            })->get();

        return view('siswa.wallet.index', compact('pointBalance', 'levelInfo', 'mutations', 'items', 'inventory', 'activeAlphaSessions'));
    }

    /**
     * Beli token dari marketplace
     */
    public function buyItem(Request $request, $id_item)
    {
        try {
            $item = FlexibilityItem::findOrFail($id_item);
            (new PointService())->spend(auth()->user()->siswa->id_siswa, $item);

            return redirect()->back()->with('success', "Berhasil menukar poin dengan token {$item->item_name}!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Gunakan token (bisa Bebas Alpha atau General)
     */
    public function useToken(Request $request, $id_token)
    {
        $request->validate([
            'id_absensi' => 'nullable|exists:absensi,id_absensi',
        ]);

        try {
            $token = UserToken::with('item')->where('id_siswa', auth()->user()->siswa->id_siswa)->findOrFail($id_token);
            $service = new PointService();

            if ($token->item->item_type === 'BEBAS_ALPHA') {
                if (!$request->id_absensi) {
                    return redirect()->back()->with('error', 'Silakan pilih sesi absensi untuk menggunakan token Bebas Alpha.');
                }
                $service->useBebasAlphaToken($id_token, auth()->user()->siswa->id_siswa, $request->id_absensi);
                $msg = "Token Bebas Alpha berhasil digunakan! Status kamu di sesi ini telah diubah menjadi Hadir (Izin Token).";
            } else {
                $service->useGeneralToken($id_token, auth()->user()->siswa->id_siswa);
                $msg = "Token {$token->item->item_name} berhasil digunakan. Jangan lupa laporkan ke gurumu!";
            }

            return redirect()->back()->with('success', $msg);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
