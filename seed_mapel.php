<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Guru;
use Illuminate\Support\Str;

echo "Menyiapkan Data Mata Pelajaran Guru...\n";

// Bikin record guru kalau belum ada buat testing
$usersGuru = User::whereHas('role', function($q){ $q->where('nama_role', 'guru'); })->get();

$mapels = ['Matematika', 'Bahasa Inggris', 'Biologi', 'Fisika', 'Kimia'];

foreach($usersGuru as $idx => $user) {
    $guru = Guru::where('id_user', $user->id_user)->first();
    $pelajaran = $mapels[$idx % count($mapels)];
    
    if (!$guru) {
        Guru::create([
            'id_user' => $user->id_user,
            'guru_id' => Str::uuid(),
            'nama' => $user->username,
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => $pelajaran
        ]);
        echo "Created Guru {$user->username} dengan Mapel: {$pelajaran}\n";
    } else {
        $guru->update(['mata_pelajaran' => $pelajaran]);
        echo "Updated Guru {$user->username} dengan Mapel: {$pelajaran}\n";
    }
}
echo "Selesai!\n";
