<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    protected $fillable = ['id_role', 'username', 'password', 'foto'];

    protected $hidden = ['password'];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    // Relasi ke profil Siswa (One-to-One)
    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'id_user', 'id_user');
    }

    // Helper methods untuk cek role
    public function isAdmin()
    {
        return $this->role->nama_role === 'admin';
    }

    public function isGuru()
    {
        return $this->role->nama_role === 'guru';
    }

    public function isSiswa()
    {
        return $this->role->nama_role === 'siswa';
    }

    public function getFotoProfileAttribute()
    {
        if ($this->foto && file_exists(public_path('storage/photos/' . $this->foto))) {
            return asset('storage/photos/' . $this->foto);
        }
        // Fallback to default
        return asset('assets/img/avatars/1.png');
    }
}
