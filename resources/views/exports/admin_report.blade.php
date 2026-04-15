<!DOCTYPE html>
<html>
<head>
    <title>Laporan Rekapitulasi Global</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #bfbfbf; padding: 10px 8px; text-align: center; }
        th { 
            color: #0070C0; 
            border-bottom: 2px solid #000; 
            background-color: #ffffff;
            font-weight: bold;
        }
        td:nth-child(3), td:nth-child(4) { text-align: left; }
        h2 { text-align: center; margin: 10px 0 5px; font-size: 18px; color: #333; }
        p { text-align: center; margin: 0 0 15px; color: #666; }
    </style>
</head>
<body>
    <h2>Laporan Rekapitulasi Absensi Global Sekolah</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Sesi</th>
                <th>Nama Guru</th>
                <th>Mata Pelajaran</th>
                <th>Kelas</th>
                <th>Siswa Hadir</th>
                <th>Siswa Izin/Sakit</th>
                <th>Siswa Alpa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($absensis as $index => $absensi)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($absensi->tanggal)->format('d-m-Y') }}</td>
                <td>{{ $absensi->guru->nama ?? 'N/A' }}</td>
                <td>{{ $absensi->mataPelajaran->nama_mapel ?? 'N/A' }}</td>
                <td>{{ $absensi->kelas->nama_kelas ?? 'N/A' }}</td>
                <td>{{ $absensi->detailAbsensis->where('status', 'hadir')->count() }}</td>
                <td>{{ $absensi->detailAbsensis->whereIn('status', ['izin', 'sakit'])->count() }}</td>
                <td>{{ $absensi->detailAbsensis->where('status', 'alpha')->count() }}</td>
            </tr>
            @endforeach
            @if($absensis->isEmpty())
            <tr>
                <td colspan="8" style="text-align: center;">Tidak ada sesi absensi pada periode ini.</td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
