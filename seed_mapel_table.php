<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Guru;
use App\Models\MataPelajaran;
use Illuminate\Support\Str;

echo "Menyiapkan Master Data Mata Pelajaran...\n";

$mapels = [
    ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MAT'],
    ['nama_mapel' => 'Bahasa Inggris', 'kode_mapel' => 'BING'],
    ['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'BIND'],
    ['nama_mapel' => 'Biologi', 'kode_mapel' => 'BIO'],
    ['nama_mapel' => 'Fisika', 'kode_mapel' => 'FIS'],
    ['nama_mapel' => 'Kimia', 'kode_mapel' => 'KIM'],
    ['nama_mapel' => 'Sejarah', 'kode_mapel' => 'SEJ'],
    ['nama_mapel' => 'Pendidikan Agama Islam', 'kode_mapel' => 'PAI'],
    ['nama_mapel' => 'Pendidikan Kewarganegaraan', 'kode_mapel' => 'PKN'],
    ['nama_mapel' => 'Seni Budaya', 'kode_mapel' => 'SBK'],
    ['nama_mapel' => 'Pendidikan Jasmani Olahraga dan Kesehatan', 'kode_mapel' => 'PJOK'],
    ['nama_mapel' => 'Rekayasa Perangkat Lunak', 'kode_mapel' => 'RPL'],
    ['nama_mapel' => 'Sistem Komputer', 'kode_mapel' => 'SK'],
    ['nama_mapel' => 'Jaringan Dasar', 'kode_mapel' => 'JD'],
    ['nama_mapel' => 'Pemrograman Dasar', 'kode_mapel' => 'PD'],
    ['nama_mapel' => 'Desain Grafis', 'kode_mapel' => 'DG']
];

foreach ($mapels as $m) {
    MataPelajaran::firstOrCreate(
        ['kode_mapel' => $m['kode_mapel']],
        ['nama_mapel' => $m['nama_mapel']]
    );
}
echo "Tabel Mata Pelajaran telah diisi dengan " . count($mapels) . " pelajaran.\n";

// Bikin record guru kalau belum ada buat testing
$usersGuru = User::whereHas('role', function($q){ $q->where('nama_role', 'guru'); })->get();
$allMapel = MataPelajaran::all();

foreach($usersGuru as $idx => $user) {
    $guru = Guru::where('id_user', $user->id_user)->first();
    $pelajaran = $allMapel[$idx % count($allMapel)];
    
    if (!$guru) {
        Guru::create([
            'id_user' => $user->id_user,
            'guru_id' => Str::uuid(),
            'nama' => $user->username,
            'jenis_kelamin' => 'L',
            'id_mapel' => $pelajaran->id_mapel
        ]);
        echo "Created Guru {$user->username} dengan Mapel: {$pelajaran->nama_mapel}\n";
    } else {
        $guru->update(['id_mapel' => $pelajaran->id_mapel]);
        echo "Updated Guru {$user->username} dengan Mapel: {$pelajaran->nama_mapel}\n";
    }
}
echo "Selesai mapping data guru!\n";
