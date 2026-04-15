<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Jurusan;

echo "Mengecek dan memperbaiki data Siswa yang hilang...\n";

// Pastikan ada Jurusan & Kelas
$jurusan = Jurusan::firstOrCreate(
    ['kode_jurusan' => 'RPL'], 
    ['nama_jurusan' => 'Rekayasa Perangkat Lunak']
);

$kelas = Kelas::firstOrCreate(
    ['nama_kelas' => 'XII RPL 1'], 
    ['id_jurusan' => $jurusan->id_jurusan, 'tingkat' => '12']
);

$users = User::whereHas('role', function($q) { 
    $q->where('nama_role', 'siswa'); 
})->get();

$fixed = 0;
foreach($users as $user) {
    $siswa = Siswa::where('id_user', $user->id_user)->first();
    if (!$siswa) {
        Siswa::create([
            'id_user' => $user->id_user,
            'nis' => '1000' . $user->id_user . rand(10, 99),
            'nama_siswa' => 'Siswa ' . $user->username,
            'id_kelas' => $kelas->id_kelas,
            'jenis_kelamin' => 'L',
            'status_aktif' => true,
        ]);
        echo "Data Siswa dibuat untuk user: {$user->username} (ID: {$user->id_user})\n";
        $fixed++;
    }
}

if ($fixed == 0) {
    echo "Semua user siswa sudah memiliki data Siswa.\n";
} else {
    echo "Berhasil memperbaiki $fixed data siswa.\n";
}
