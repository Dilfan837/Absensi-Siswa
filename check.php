<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Siswa;

echo "Users list:\n";
foreach(User::with('role')->get() as $u) {
    echo "ID: {$u->id_user} | Username: {$u->username} | Role: " . ($u->role ? $u->role->nama_role : 'None') . "\n";
}

echo "\nSiswa list:\n";
foreach(Siswa::all() as $s) {
    echo "ID: {$s->id_siswa} | User_ID: {$s->id_user} | Nama: {$s->nama_siswa}\n";
}
