<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PointLedger;
use App\Models\UserToken;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $primaryKey = 'id_siswa';
    protected $fillable = [
        'id_user', 
        'nis', 
        'nama_siswa', 
        'id_kelas', 
        'jenis_kelamin', 
        'status_aktif',
        'foto', // <--- Tambahkan ini agar bisa menyimpan nama file gambar
        'face_descriptor'
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'face_descriptor' => 'array', // Cast JSON string ke Array PHP secara otomatis
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class, 'id_siswa', 'id_siswa');
    }

    public function pointLedgers()
    {
        return $this->hasMany(PointLedger::class, 'id_siswa', 'id_siswa');
    }

    public function userTokens()
    {
        return $this->hasMany(UserToken::class, 'id_siswa', 'id_siswa');
    }

    /**
     * Ambil saldo poin terkini dari ledger terakhir.
     * Return 0 jika belum ada transaksi.
     */
    public function getPointBalance(): int
    {
        $last = $this->pointLedgers()->latest()->first();
        return $last ? $last->current_balance : 0;
    }

    /**
     * Level integritas berdasarkan saldo poin
     */
    public function getIntegrityLevel(): array
    {
        $balance = $this->getPointBalance();
        if ($balance < 0) {
            return ['label' => 'Abai', 'color' => 'red', 'icon' => '💀', 'class' => 'level-abai'];
        } elseif ($balance <= 30) {
            return ['label' => 'Pemula', 'color' => 'green', 'icon' => '🌱', 'class' => 'level-pemula'];
        } elseif ($balance <= 80) {
            return ['label' => 'Disiplin', 'color' => 'blue', 'icon' => '⭐', 'class' => 'level-disiplin'];
        } else {
            return ['label' => 'Elite', 'color' => 'gold', 'icon' => '🏆', 'class' => 'level-elite'];
        }
    }
}