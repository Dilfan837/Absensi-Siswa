<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\DetailAbsensi;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================
        // 1. JURUSAN
        // =============================================
        $jurusanData = [
            ['nama_jurusan' => 'Teknik Komputer dan Jaringan', 'kode_jurusan' => 'TKJ'],
            ['nama_jurusan' => 'Rekayasa Perangkat Lunak', 'kode_jurusan' => 'RPL'],
            ['nama_jurusan' => 'Multimedia', 'kode_jurusan' => 'MM'],
        ];
        $jurusans = [];
        foreach ($jurusanData as $j) {
            $jurusans[] = Jurusan::firstOrCreate(
                ['nama_jurusan' => $j['nama_jurusan']],
                $j
            );
        }

        // =============================================
        // 2. MATA PELAJARAN
        // =============================================
        $mapelData = [
            ['nama_mapel' => 'Matematika', 'kode_mapel' => 'MTK'],
            ['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'BIND'],
            ['nama_mapel' => 'Bahasa Inggris', 'kode_mapel' => 'BING'],
            ['nama_mapel' => 'Pemrograman Web', 'kode_mapel' => 'PW'],
            ['nama_mapel' => 'Basis Data', 'kode_mapel' => 'BD'],
            ['nama_mapel' => 'Desain Grafis', 'kode_mapel' => 'DG'],
            ['nama_mapel' => 'Jaringan Komputer', 'kode_mapel' => 'JK'],
            ['nama_mapel' => 'Pendidikan Agama Islam', 'kode_mapel' => 'PAI'],
            ['nama_mapel' => 'Pendidikan Pancasila', 'kode_mapel' => 'PPKN'],
            ['nama_mapel' => 'Pemrograman Berorientasi Objek', 'kode_mapel' => 'PBO'],
        ];
        $mapels = [];
        foreach ($mapelData as $m) {
            $mapels[] = MataPelajaran::firstOrCreate(
                ['kode_mapel' => $m['kode_mapel']],
                $m
            );
        }

        // =============================================
        // 3. KELAS (3 Tingkat × 3 Jurusan = 9 Kelas)
        // =============================================
        $kelasData = [];
        $tingkatList = [10, 11, 12];
        foreach ($jurusans as $jurusan) {
            foreach ($tingkatList as $tingkat) {
                $shortCode = '';
                if (str_contains($jurusan->nama_jurusan, 'Teknik Komputer')) $shortCode = 'TKJ';
                elseif (str_contains($jurusan->nama_jurusan, 'Perangkat Lunak')) $shortCode = 'RPL';
                elseif (str_contains($jurusan->nama_jurusan, 'Multimedia')) $shortCode = 'MM';

                $kelasData[] = Kelas::firstOrCreate(
                    ['nama_kelas' => $tingkat . ' ' . $shortCode],
                    [
                        'id_jurusan' => $jurusan->id_jurusan,
                        'nama_kelas' => $tingkat . ' ' . $shortCode,
                        'tingkat' => $tingkat,
                    ]
                );
            }
        }

        // =============================================
        // 4. GURU (6 guru dengan akun user)
        // =============================================
        $guruList = [
            ['nama' => 'Ahmad Fauzi, S.Pd', 'nip' => '198501012010011001', 'email' => 'ahmad.fauzi@guru.smk.id', 'jk' => 'L', 'mapel_idx' => 0],
            ['nama' => 'Siti Nurhaliza, M.Pd', 'nip' => '198703152011022001', 'email' => 'siti.nurhaliza@guru.smk.id', 'jk' => 'P', 'mapel_idx' => 1],
            ['nama' => 'Budi Santoso, S.Kom', 'nip' => '199002202012011003', 'email' => 'budi.santoso@guru.smk.id', 'jk' => 'L', 'mapel_idx' => 3],
            ['nama' => 'Dewi Anggraeni, S.Pd', 'nip' => '198806102013022002', 'email' => 'dewi.anggraeni@guru.smk.id', 'jk' => 'P', 'mapel_idx' => 2],
            ['nama' => 'Rizky Ramadhan, S.Kom', 'nip' => '199105252014011004', 'email' => 'rizky.ramadhan@guru.smk.id', 'jk' => 'L', 'mapel_idx' => 4],
            ['nama' => 'Indah Permatasari, S.Pd', 'nip' => '199204302015022003', 'email' => 'indah.permata@guru.smk.id', 'jk' => 'P', 'mapel_idx' => 5],
        ];

        $guruModels = [];
        foreach ($guruList as $idx => $g) {
            $username = 'guru' . ($idx + 1);
            $user = User::firstOrCreate(
                ['username' => $username],
                [
                    'id_role' => 3, // guru
                    'username' => $username,
                    'password' => Hash::make('guru123'),
                ]
            );

            $guruModels[] = Guru::firstOrCreate(
                ['id_user' => $user->id_user],
                [
                    'id_user' => $user->id_user,
                    'nip' => $g['nip'],
                    'nama' => $g['nama'],
                    'email' => $g['email'],
                    'jenis_kelamin' => $g['jk'],
                    'id_mapel' => $mapels[$g['mapel_idx']]->id_mapel,
                    'status_aktif' => true,
                ]
            );
        }

        // =============================================
        // 5. SISWA (30 siswa, 3-4 per kelas)
        // =============================================
        $namaLaki = [
            'Andi Prasetyo', 'Dimas Saputra', 'Fajar Nugroho', 'Gilang Ramadhan',
            'Hendra Wijaya', 'Irfan Maulana', 'Kevin Pratama', 'Muhammad Rizki',
            'Naufal Hakim', 'Oscar Firmansyah', 'Reza Mahendra', 'Taufik Hidayat',
            'Yoga Pratama', 'Zaki Alamsyah', 'Bayu Setiawan',
        ];
        $namaPerempuan = [
            'Anisa Rahma', 'Bella Oktavia', 'Citra Dewi', 'Diana Putri',
            'Eka Safitri', 'Fitri Handayani', 'Gita Nuraini', 'Hani Susanti',
            'Intan Permata', 'Julia Kartika', 'Kartini Wulandari', 'Lestari Ningrum',
            'Mega Sari', 'Nita Anggraeni', 'Olivia Rahayu',
        ];

        $siswaModels = [];
        $siswaIndex = 0;
        foreach ($kelasData as $kelas) {
            // 3-4 siswa per kelas
            $perKelas = ($siswaIndex < 18) ? 4 : 3;
            for ($i = 0; $i < $perKelas && $siswaIndex < 30; $i++) {
                $isLaki = ($siswaIndex % 2 === 0);
                $namaPool = $isLaki ? $namaLaki : $namaPerempuan;
                $namaIdx = intdiv($siswaIndex, 2) % count($namaPool);
                $nama = $namaPool[$namaIdx];
                $jk = $isLaki ? 'L' : 'P';
                $nis = '2026' . str_pad($siswaIndex + 1, 4, '0', STR_PAD_LEFT);

                $username = 'siswa' . ($siswaIndex + 1);
                $userSiswa = User::firstOrCreate(
                    ['username' => $username],
                    [
                        'id_role' => 2, // siswa
                        'username' => $username,
                        'password' => Hash::make('siswa123'),
                    ]
                );

                $siswaModels[] = Siswa::firstOrCreate(
                    ['nis' => $nis],
                    [
                        'id_user' => $userSiswa->id_user,
                        'nis' => $nis,
                        'nama_siswa' => $nama,
                        'id_kelas' => $kelas->id_kelas,
                        'jenis_kelamin' => $jk,
                        'status_aktif' => true,
                    ]
                );

                $siswaIndex++;
            }
        }

        // =============================================
        // 6. SESI ABSENSI (12 sesi dalam 2 minggu terakhir)
        // =============================================
        $absensiModels = [];
        $today = Carbon::today();

        $sesiData = [
            // Guru 0 (Ahmad Fauzi - MTK) mengajar kelas 10 TKJ & 11 RPL
            ['guru_idx' => 0, 'kelas_idx' => 0, 'days_ago' => 1, 'jam' => '07:30', 'nama' => 'Matematika - Pertemuan 1'],
            ['guru_idx' => 0, 'kelas_idx' => 4, 'days_ago' => 2, 'jam' => '09:00', 'nama' => 'Matematika - Pertemuan 5'],
            // Guru 1 (Siti Nurhaliza - BIND) mengajar kelas 10 RPL & 12 MM
            ['guru_idx' => 1, 'kelas_idx' => 3, 'days_ago' => 1, 'jam' => '10:30', 'nama' => 'Bahasa Indonesia - Pertemuan 3'],
            ['guru_idx' => 1, 'kelas_idx' => 8, 'days_ago' => 3, 'jam' => '07:30', 'nama' => 'Bahasa Indonesia - Pertemuan 7'],
            // Guru 2 (Budi Santoso - PW) mengajar kelas 11 TKJ & 10 RPL
            ['guru_idx' => 2, 'kelas_idx' => 1, 'days_ago' => 0, 'jam' => '07:30', 'nama' => 'Pemrograman Web - Pertemuan 2'],
            ['guru_idx' => 2, 'kelas_idx' => 3, 'days_ago' => 4, 'jam' => '13:00', 'nama' => 'Pemrograman Web - Pertemuan 4'],
            // Guru 3 (Dewi Anggraeni - BING) mengajar kelas 12 TKJ & 11 MM
            ['guru_idx' => 3, 'kelas_idx' => 2, 'days_ago' => 0, 'jam' => '09:00', 'nama' => 'Bahasa Inggris - Pertemuan 6'],
            ['guru_idx' => 3, 'kelas_idx' => 7, 'days_ago' => 5, 'jam' => '10:30', 'nama' => 'Bahasa Inggris - Pertemuan 8'],
            // Guru 4 (Rizky Ramadhan - BD) mengajar kelas 11 RPL & 12 RPL
            ['guru_idx' => 4, 'kelas_idx' => 4, 'days_ago' => 1, 'jam' => '13:00', 'nama' => 'Basis Data - Pertemuan 9'],
            ['guru_idx' => 4, 'kelas_idx' => 5, 'days_ago' => 7, 'jam' => '07:30', 'nama' => 'Basis Data - Pertemuan 10'],
            // Guru 5 (Indah Permatasari - DG) mengajar kelas 10 MM & 11 MM
            ['guru_idx' => 5, 'kelas_idx' => 6, 'days_ago' => 2, 'jam' => '09:00', 'nama' => 'Desain Grafis - Pertemuan 11'],
            ['guru_idx' => 5, 'kelas_idx' => 7, 'days_ago' => 0, 'jam' => '13:00', 'nama' => 'Desain Grafis - Pertemuan 12'],
        ];

        foreach ($sesiData as $sesi) {
            $tanggal = $today->copy()->subDays($sesi['days_ago'])->format('Y-m-d');
            $jamMulaiParts = explode(':', $sesi['jam']);
            $jamMulai = Carbon::parse($tanggal)->setTime((int)$jamMulaiParts[0], (int)$jamMulaiParts[1]);
            $jamSelesai = $jamMulai->copy()->addMinutes(90);

            $guruUser = $guruModels[$sesi['guru_idx']];

            $absensi = Absensi::firstOrCreate(
                [
                    'nama_absensi' => $sesi['nama'],
                    'tanggal' => $tanggal,
                ],
                [
                    'id_kelas' => $kelasData[$sesi['kelas_idx']]->id_kelas,
                    'dibuat_oleh' => $guruUser->id_user,
                    'nama_absensi' => $sesi['nama'],
                    'tanggal' => $tanggal,
                    'jam_mulai' => $jamMulai->format('H:i:s'),
                    'jam_selesai' => $jamSelesai->format('H:i:s'),
                    'qr_token' => Str::random(32),
                    'status' => 'selesai',
                ]
            );

            $absensiModels[] = $absensi;
        }

        // =============================================
        // 7. DETAIL ABSENSI (Kehadiran siswa per sesi)
        // =============================================
        $statusOptions = ['hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'alpha', 'izin', 'sakit'];
        // Probabilitas: 70% hadir, 10% alpha, 10% izin, 10% sakit

        foreach ($absensiModels as $absensi) {
            // Cari siswa di kelas ini
            $siswaKelas = Siswa::where('id_kelas', $absensi->id_kelas)->get();

            foreach ($siswaKelas as $siswa) {
                $status = $statusOptions[array_rand($statusOptions)];
                $waktuScan = null;
                $keterangan = null;

                if ($status === 'hadir') {
                    $waktuScan = Carbon::parse($absensi->tanggal . ' ' . $absensi->jam_mulai)
                        ->addMinutes(rand(0, 15));
                } elseif ($status === 'izin') {
                    $keterangan = collect(['Urusan keluarga', 'Kegiatan OSIS', 'Izin pribadi'])->random();
                } elseif ($status === 'sakit') {
                    $keterangan = collect(['Demam', 'Flu', 'Sakit perut', 'Kontrol ke dokter'])->random();
                }

                DetailAbsensi::firstOrCreate(
                    [
                        'id_absensi' => $absensi->id_absensi,
                        'id_siswa' => $siswa->id_siswa,
                    ],
                    [
                        'id_absensi' => $absensi->id_absensi,
                        'id_siswa' => $siswa->id_siswa,
                        'status' => $status,
                        'waktu_scan' => $waktuScan,
                        'keterangan' => $keterangan,
                    ]
                );
            }
        }

        $this->command->info('✅ Dummy data berhasil dibuat!');
        $this->command->info('   - ' . count($jurusans) . ' Jurusan');
        $this->command->info('   - ' . count($mapels) . ' Mata Pelajaran');
        $this->command->info('   - ' . count($kelasData) . ' Kelas');
        $this->command->info('   - ' . count($guruModels) . ' Guru (password: guru123)');
        $this->command->info('   - ' . $siswaIndex . ' Siswa (password: siswa123)');
        $this->command->info('   - ' . count($absensiModels) . ' Sesi Absensi');
        $this->command->info('   - Detail kehadiran untuk setiap sesi');
    }
}
