<?php

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- FIXING SISWA DATA ---\n";

// 1. Fix Jurusan
$jurusan = Jurusan::first();
if (!$jurusan) {
    echo "Creating Jurusan...\n";
    $jurusan = Jurusan::create([
        'nama_jurusan' => 'Rekayasa Perangkat Lunak',
        'kode_jurusan' => 'RPL'
    ]);
} else {
    echo "Jurusan exists: " . $jurusan->nama_jurusan . "\n";
}

// 2. Fix Kelas
$kelas = Kelas::first();
if (!$kelas) {
    echo "Creating Kelas...\n";
    $kelas = Kelas::create([
        'id_jurusan' => $jurusan->id_jurusan,
        'nama_kelas' => 'XII RPL 1',
        'tingkat' => '12'
    ]);
} else {
    echo "Kelas exists: " . $kelas->nama_kelas . "\n";
}

// 3. Fix Siswa for User 'siswa1'
$user = User::where('username', 'siswa1')->first();
if ($user) {
    echo "User siswa1 found (ID: " . $user->id_user . ")\n";
    
    $siswa = Siswa::where('id_user', $user->id_user)->first();
    if (!$siswa) {
        echo "Creating Siswa data for siswa1...\n";
        $siswa = Siswa::create([
            'id_user' => $user->id_user,
            'nis' => '123456789',
            'nama_siswa' => 'Siswa Demo',
            'id_kelas' => $kelas->id_kelas,
            'jenis_kelamin' => 'L',
            'status_aktif' => true,
        ]);
        echo "Siswa created successfully!\n";
    } else {
        echo "Siswa data already exists for siswa1.\n";
    }
} else {
    echo "User 'siswa1' NOT FOUND! Please run seeder first.\n";
}

echo "--- DONE ---\n";
