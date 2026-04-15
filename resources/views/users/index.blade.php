@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-white">Manajemen Pengguna</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUser">
                <i class="bx bx-plus"></i> Tambah User
            </button>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('users.index') }}" method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari username..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
            </form>

            <ul class="nav nav-tabs mt-3" role="tablist">
                @php $tabs = ['Admin' => $admins, 'Guru' => $gurus, 'Siswa' => $siswas]; @endphp
                @foreach($tabs as $roleName => $roleUsers)
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-{{ strtolower($roleName) }}" aria-controls="navs-{{ strtolower($roleName) }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $roleName }}</button>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content p-0 pt-3 bg-transparent border-0 shadow-none">
                @foreach($tabs as $roleName => $roleUsers)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="navs-{{ strtolower($roleName) }}" role="tabpanel">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover table-bordered bg-white">
                                <thead class="table-light">
                                    <tr>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roleUsers as $u)
                                    <tr>
                                        <td class="align-middle fw-semibold">{{ $u->username }}</td>
                                        <td class="align-middle">
                                            <span class="badge bg-label-primary text-dark">
                                                {{ $u->role->nama_role ?? 'No Role' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm text-white edit-btn"
                                                data-id="{{ $u->id_user }}"
                                                data-username="{{ $u->username }}"
                                                data-role="{{ $u->id_role }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditUser">
                                                <i class="bx bx-edit"></i> Edit
                                            </button>
                                            <form action="{{ route('users.destroy', $u->id_user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i> Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">Belum ada user untuk role {{ $roleName }}.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="id_role" id="id_role" class="form-select" required onchange="toggleMapelDropdown()">
                            <option value="" selected disabled>Pilih Role</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id_role }}" data-role-name="{{ strtolower($r->nama_role) }}">{{ $r->nama_role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="mapel_container" style="display: none;">
                        <label class="form-label">Pilih Mata Pelajaran (Khusus Guru)</label>
                        <select name="id_mapel" id="id_mapel" class="form-select">
                            <option value="" selected disabled>-- Pilih Mata Pelajaran --</option>
                            @foreach($mapels as $m)
                                <option value="{{ $m->id_mapel }}">{{ $m->nama_mapel }} ({{ $m->kode_mapel }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="add_password" name="password" class="form-control" placeholder="********" required>
                            <span class="input-group-text cursor-pointer" onclick="togglePasswordVisibility('add_password', this)"><i class="bx bx-hide"></i></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Daftarkan User</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="modalEditUser" tabindex="-1">
    <div class="modal-dialog">
        <form id="formEditUser" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="id_role" id="edit_id_role" class="form-select" required>
                            <option value="" disabled>Pilih Role</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id_role }}">{{ $r->nama_role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password (Biarkan kosong jika tidak diubah)</label>
                        <div class="input-group input-group-merge">
                            <input type="password" id="edit_password" name="password" class="form-control" placeholder="********">
                            <span class="input-group-text cursor-pointer" onclick="togglePasswordVisibility('edit_password', this)"><i class="bx bx-hide"></i></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, iconElement) {
        const input = document.getElementById(inputId);
        const icon = iconElement.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bx-hide');
            icon.classList.add('bx-show');
        } else {
            input.type = 'password';
            icon.classList.remove('bx-show');
            icon.classList.add('bx-hide');
        }
    }

    function toggleMapelDropdown() {
        const roleSelect = document.getElementById('id_role');
        const mapelContainer = document.getElementById('mapel_container');
        const mapelSelect = document.getElementById('id_mapel');
        
        // Get the selected option's data-role-name attribute
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedOption.getAttribute('data-role-name');
        
        if (roleName === 'guru') {
            mapelContainer.style.display = 'block';
            mapelSelect.setAttribute('required', 'required');
        } else {
            mapelContainer.style.display = 'none';
            mapelSelect.removeAttribute('required');
            mapelSelect.value = ''; // reset selection
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const editBtns = document.querySelectorAll('.edit-btn');
        const formEdit = document.getElementById('formEditUser');
        const editUsername = document.getElementById('edit_username');
        const editIdRole = document.getElementById('edit_id_role');

        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const username = this.dataset.username;
                const role = this.dataset.role;

                // Set value input
                editUsername.value = username;
                editIdRole.value = role;

                // Update action URL form
                // Asumsi route resource: /users/{id}
                formEdit.action = `/users/${id}`;
            });
        });
    });
</script>
@endsection