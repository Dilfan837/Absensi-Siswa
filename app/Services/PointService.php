<?php

namespace App\Services;

use App\Models\PointLedger;
use App\Models\PointSetting;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\DetailAbsensi;
use App\Models\UserToken;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Catat poin EARN ketika siswa hadir (scan QR berhasil).
     */
    public function earn(int $id_siswa, int $id_absensi, string $absensiName): PointLedger
    {
        $amount = PointSetting::getValue('poin_hadir', 2);
        return $this->record($id_siswa, 'EARN', $amount, "Hadir: {$absensiName}", $id_absensi, null);
    }

    /**
     * Catat poin PENALTY ketika siswa alpha (sesi ditutup tanpa scan).
     */
    public function penalty(int $id_siswa, int $id_absensi, string $absensiName): PointLedger
    {
        $amount = PointSetting::getValue('poin_alpha', -1);
        return $this->record($id_siswa, 'PENALTY', $amount, "Alpha: {$absensiName}", $id_absensi, null);
    }

    /**
     * Catat poin REWARD/PENALTY dari guru secara manual.
     *
     * @throws \Exception jika melebihi batas max per sesi
     */
    public function manualByGuru(int $id_siswa, int $id_absensi, int $id_guru, int $amount, string $reason): PointLedger
    {
        $maxPoint = PointSetting::getValue('max_poin_guru', 5);

        // Cek total poin manual yang sudah diberikan guru ke siswa ini di sesi ini
        $alreadyGiven = PointLedger::where('id_siswa', $id_siswa)
            ->where('id_absensi', $id_absensi)
            ->where('id_guru', $id_guru)
            ->whereIn('transaction_type', ['REWARD', 'PENALTY'])
            ->sum(DB::raw('ABS(amount)'));

        if (($alreadyGiven + abs($amount)) > $maxPoint) {
            throw new \Exception("Batas maksimum poin manual ({$maxPoint}) untuk sesi ini sudah tercapai.");
        }

        $type = $amount >= 0 ? 'REWARD' : 'PENALTY';
        $absensi = Absensi::find($id_absensi);
        $namaAbsensi = $absensi ? $absensi->nama_absensi : "Sesi #{$id_absensi}";

        return $this->record($id_siswa, $type, $amount, "Penilaian Guru: {$reason} ({$namaAbsensi})", $id_absensi, $id_guru);
    }

    /**
     * Catat poin SPEND ketika siswa membeli token dari marketplace.
     *
     * @throws \Exception jika saldo tidak cukup atau stok habis
     */
    public function spend(int $id_siswa, \App\Models\FlexibilityItem $item): UserToken
    {
        $siswa = Siswa::findOrFail($id_siswa);
        $balance = $siswa->getPointBalance();

        if ($balance < $item->point_cost) {
            throw new \Exception("Saldo poin tidak mencukupi. Saldo kamu: {$balance}, harga item: {$item->point_cost}.");
        }

        if (!$item->isAvailableForSiswa($id_siswa)) {
            throw new \Exception("Kamu sudah mencapai batas pembelian item ini bulan ini ({$item->stock_limit}x).");
        }

        return DB::transaction(function () use ($id_siswa, $item) {
            // Catat pengeluaran poin
            $this->record(
                $id_siswa,
                'SPEND',
                -$item->point_cost,
                "Beli Token: {$item->item_name}",
                null,
                null
            );

            // Buat token di inventory siswa
            return UserToken::create([
                'id_siswa'     => $id_siswa,
                'id_item'      => $item->id,
                'status'       => 'AVAILABLE',
                'purchased_at' => now(),
            ]);
        });
    }

    /**
     * Gunakan token BEBAS_ALPHA untuk melindungi status absensi siswa.
     * Hanya berlaku saat sesi absensi masih AKTIF.
     *
     * @throws \Exception jika sesi sudah tutup atau token tidak valid
     */
    public function useBebasAlphaToken(int $id_token, int $id_siswa, int $id_absensi): DetailAbsensi
    {
        $token = UserToken::with('item')
            ->where('id', $id_token)
            ->where('id_siswa', $id_siswa)
            ->where('status', 'AVAILABLE')
            ->firstOrFail();

        if ($token->item->item_type !== 'BEBAS_ALPHA') {
            throw new \Exception("Token ini bukan tipe Bebas Alpha.");
        }

        // Validasi: sesi HARUS masih aktif (requires_active_session = true)
        $absensi = Absensi::findOrFail($id_absensi);
        if ($absensi->status !== 'aktif') {
            throw new \Exception("Sesi absensi sudah ditutup. Token Bebas Alpha hanya bisa dipakai saat sesi masih aktif.");
        }

        // Pastikan siswa memang alpha di sesi ini
        $detail = DetailAbsensi::where('id_absensi', $id_absensi)
            ->where('id_siswa', $id_siswa)
            ->firstOrFail();

        if ($detail->status !== 'alpha') {
            throw new \Exception("Status kamu di sesi ini bukan Alpha, tidak perlu memakai token.");
        }

        return DB::transaction(function () use ($token, $detail) {
            // Ubah status absensi menjadi izin_token
            $detail->update(['status' => 'izin_token', 'waktu_scan' => null]);

            // Tandai token sebagai USED
            $token->update([
                'status'          => 'USED',
                'id_absensi_used' => $detail->id_absensi,
                'used_at'         => now(),
            ]);

            return $detail;
        });
    }

    /**
     * Gunakan token NON-BEBAS_ALPHA (WFH, Izin, dll).
     * Tidak memerlukan sesi aktif.
     */
    public function useGeneralToken(int $id_token, int $id_siswa, string $keterangan = ''): UserToken
    {
        $token = UserToken::with('item')
            ->where('id', $id_token)
            ->where('id_siswa', $id_siswa)
            ->where('status', 'AVAILABLE')
            ->firstOrFail();

        if ($token->item->item_type === 'BEBAS_ALPHA') {
            throw new \Exception("Token Bebas Alpha harus digunakan melalui sesi absensi yang aktif.");
        }

        $token->update([
            'status'  => 'USED',
            'used_at' => now(),
        ]);

        return $token;
    }

    /**
     * Ambil saldo poin terkini dari tabel ledger.
     */
    public function getBalance(int $id_siswa): int
    {
        $last = PointLedger::where('id_siswa', $id_siswa)->latest()->first();
        return $last ? $last->current_balance : 0;
    }

    // =========================================================
    //  PRIVATE: Tulis baris baru ke ledger (core function)
    // =========================================================
    private function record(
        int $id_siswa,
        string $type,
        int $amount,
        string $description,
        ?int $id_absensi = null,
        ?int $id_guru = null
    ): PointLedger {
        return DB::transaction(function () use ($id_siswa, $type, $amount, $description, $id_absensi, $id_guru) {
            // Ambil saldo saat ini (row paling akhir)
            $currentBalance = $this->getBalance($id_siswa);
            $newBalance = $currentBalance + $amount;

            return PointLedger::create([
                'id_siswa'         => $id_siswa,
                'transaction_type' => $type,
                'amount'           => $amount,
                'current_balance'  => $newBalance,
                'description'      => $description,
                'id_absensi'       => $id_absensi,
                'id_guru'          => $id_guru,
            ]);
        });
    }
}
