<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kehadiran Kelas</title>
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
        td:nth-child(5), td:nth-child(7) { text-align: left; }
        h2 { text-align: center; margin: 10px 0 5px; font-size: 18px; color: #333; }
        p { text-align: center; margin: 0 0 5px; color: #666; }
    </style>
</head>
<body>
    <h2>Laporan Kehadiran Kelas</h2>
    <p>Guru: {{ $guruName }}</p>
    <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Sesi</th>
                <th>Kelas</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Status Kehadiran</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detailAbsensis as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($detail->absensi->tanggal)->format('d-m-Y') }}</td>
                <td>{{ optional($detail->absensi->kelas)->nama_kelas ?? 'N/A' }}</td>
                <td>{{ optional($detail->siswa)->nis ?? 'N/A' }}</td>
                <td>{{ optional($detail->siswa)->nama_siswa ?? 'N/A' }}</td>
                <td>{{ ucfirst($detail->status) }}</td>
                <td>{{ $detail->keterangan ?? '-' }}</td>
            </tr>
            @endforeach
            @if($detailAbsensis->isEmpty())
            <tr>
                <td colspan="7" style="text-align: center;">Tidak ada data kehadiran siswa pada periode ini.</td>
            </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
