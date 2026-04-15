@extends('layouts.app')

@section('title', 'sesi absensi')

@section('content') {{-- <--- WAJIB ADA INI --}}
<div class="card shadow border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Manajemen Sesi Absensi</h5>
        <div class="d-flex gap-2 align-items-center">
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahAbsensi">
                <i class="fas fa-plus"></i> Buat Sesi Baru
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Sesi</th>
                        <th>Kelas</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $row)
                    <tr>
                        <td><strong>{{ $row->nama_absensi }}</strong></td>
                        <td>{{ $row->kelas->nama_kelas }}</td>
                        <td>{{ $row->jam_mulai }} - {{ $row->jam_selesai }}</td>
                        <td>
                            @if($row->status == 'aktif')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Selesai</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('absensi.show', $row->id_absensi) }}" class="btn btn-info btn-sm text-white">
                                    <i class="fas fa-qrcode"></i> Tampilkan QR
                                </a>
                                
                                @if($row->status == 'aktif')
                                <form action="{{ route('absensi.tutup', $row->id_absensi) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tutup absen? Siswa yang tidak scan akan otomatis ALPHA.')">
                                        Tutup & Set Alpha
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahAbsensi" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('absensi.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5>Buat Sesi Absen</h5></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Pilih Kelas</label>
                        <select name="id_kelas" class="form-select" required>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id_kelas }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Nama Sesi (Mata Pelajaran)</label>
                        @if(isset($guruProfile) && $guruProfile->id_mapel && $guruProfile->mataPelajaran)
                            <input type="text" name="nama_absensi" class="form-control" value="{{ $guruProfile->mataPelajaran->nama_mapel }}" readonly required>
                            <small class="text-muted">Mata pelajaran otomatis terisi sesuai dengan profil Anda.</small>
                        @else
                            <input type="text" name="nama_absensi" class="form-control" placeholder="Contoh: Matematika - Logika" required>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Jam Mulai</label>
                            <input type="time" name="jam_mulai" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label>Jam Selesai</label>
                            <input type="time" name="jam_selesai" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Aktifkan QR & Sesi</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection