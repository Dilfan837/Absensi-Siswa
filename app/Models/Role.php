<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id_role';
    public $timestamps = false; // Karena di migrasi biasanya role tidak butuh timestamps
    protected $fillable = ['nama_role'];

    public function users()
    {
        return $this->hasMany(User::class, 'id_role', 'id_role');
    }
}