<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Absensi;
use App\Models\MataPelajaran;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->input('q', ''));
        $role = auth()->user()->role->nama_role;

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = [];

        if ($role === 'admin') {
            // Admin: cari Siswa, Guru, Kelas, Sesi Absensi, Mapel
            $siswa = Siswa::with('kelas')
                ->where('nama_siswa', 'like', "%{$q}%")
                ->orWhere('nis', 'like', "%{$q}%")
                ->limit(5)->get();

            foreach ($siswa as $s) {
                $results[] = [
                    'category' => 'Siswa',
                    'icon' => 'bx-user',
                    'title' => $s->nama_siswa,
                    'subtitle' => 'NIS: ' . $s->nis . ' — ' . ($s->kelas->nama_kelas ?? ''),
                    'url' => route('siswa.index', ['search' => $s->nama_siswa]),
                ];
            }

            $guru = Guru::where('nama', 'like', "%{$q}%")
                ->orWhere('nip', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->limit(5)->get();

            foreach ($guru as $g) {
                $results[] = [
                    'category' => 'Guru',
                    'icon' => 'bx-chalkboard',
                    'title' => $g->nama,
                    'subtitle' => 'NIP: ' . ($g->nip ?? '-') . ' — ' . ($g->email ?? ''),
                    'url' => route('guru.index', ['search' => $g->nama]),
                ];
            }

            $kelas = Kelas::with('jurusan')
                ->where('nama_kelas', 'like', "%{$q}%")
                ->limit(5)->get();

            foreach ($kelas as $k) {
                $results[] = [
                    'category' => 'Kelas',
                    'icon' => 'bx-buildings',
                    'title' => $k->nama_kelas,
                    'subtitle' => $k->jurusan->nama_jurusan ?? '',
                    'url' => route('kelas.index', ['search' => $k->nama_kelas]),
                ];
            }

            $mapel = MataPelajaran::where('nama_mapel', 'like', "%{$q}%")
                ->orWhere('kode_mapel', 'like', "%{$q}%")
                ->limit(5)->get();

            foreach ($mapel as $m) {
                $results[] = [
                    'category' => 'Mata Pelajaran',
                    'icon' => 'bx-book-open',
                    'title' => $m->nama_mapel,
                    'subtitle' => 'Kode: ' . ($m->kode_mapel ?? '-'),
                    'url' => route('mata-pelajaran.index', ['search' => $m->nama_mapel]),
                ];
            }

        } elseif ($role === 'guru') {
            // Guru: cari siswa & sesi absensi miliknya
            $userId = auth()->id();

            $absensi = Absensi::with('kelas')
                ->where('dibuat_oleh', $userId)
                ->where('nama_absensi', 'like', "%{$q}%")
                ->limit(5)->get();

            foreach ($absensi as $a) {
                $results[] = [
                    'category' => 'Sesi Absensi',
                    'icon' => 'bx-calendar-check',
                    'title' => $a->nama_absensi,
                    'subtitle' => ($a->kelas->nama_kelas ?? '') . ' — ' . \Carbon\Carbon::parse($a->tanggal)->format('d M Y'),
                    'url' => route('absensi.show', $a->id_absensi),
                ];
            }

            // Siswa dari kelas yang pernah diajar
            $kelasIds = Absensi::where('dibuat_oleh', $userId)->pluck('id_kelas')->unique();
            $siswa = Siswa::with('kelas')
                ->whereIn('id_kelas', $kelasIds)
                ->where(function($query) use ($q) {
                    $query->where('nama_siswa', 'like', "%{$q}%")
                          ->orWhere('nis', 'like', "%{$q}%");
                })
                ->limit(5)->get();

            foreach ($siswa as $s) {
                $results[] = [
                    'category' => 'Siswa',
                    'icon' => 'bx-user',
                    'title' => $s->nama_siswa,
                    'subtitle' => 'NIS: ' . $s->nis . ' — ' . ($s->kelas->nama_kelas ?? ''),
                    'url' => '#',
                ];
            }

        } elseif ($role === 'siswa') {
            // Siswa: cari sesi/riwayat absensinya
            $siswaModel = auth()->user()->siswa;
            if ($siswaModel) {
                $absensi = Absensi::with('kelas')
                    ->where('id_kelas', $siswaModel->id_kelas)
                    ->where('nama_absensi', 'like', "%{$q}%")
                    ->limit(5)->get();

                foreach ($absensi as $a) {
                    $results[] = [
                        'category' => 'Riwayat Absensi',
                        'icon' => 'bx-history',
                        'title' => $a->nama_absensi,
                        'subtitle' => \Carbon\Carbon::parse($a->tanggal)->format('d M Y') . ' — ' . ucfirst($a->status),
                        'url' => '#',
                    ];
                }
            }
        }

        return response()->json($results);
    }
}
