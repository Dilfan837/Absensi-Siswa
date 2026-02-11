@extends('layouts.app')

@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Pengaturan Lokasi & Geofencing</h5>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddLocation">
            <i class="bx bx-plus"></i> Tambah Lokasi
        </button>
    </div>
    <div class="card-body">
        
        <!-- Section: Geofencing Status -->
        <div class="alert alert-secondary d-flex justify-content-between align-items-center mb-4" role="alert">
            <div>
                <h6 class="alert-heading fw-bold mb-1"><i class="bx bx-map-pin"></i> Status Geofencing (GPS Lock)</h6>
                <p class="mb-0 small text-muted">Aktifkan untuk membatasi absensi hanya di radius lokasi yang ditentukan.</p>
            </div>
            <form action="{{ route('settings.toggle-geofence') }}" method="POST">
                @csrf
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="geofenceSwitch" 
                           onchange="this.form.submit()" style="transform: scale(1.3); cursor: pointer;"
                           {{ $setting->is_geofence_active ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold ms-2 {{ $setting->is_geofence_active ? 'text-success' : 'text-danger' }}" for="geofenceSwitch">
                        {{ $setting->is_geofence_active ? 'ON' : 'OFF' }}
                    </label>
                </div>
            </form>
        </div>

        <!-- Section: Peta Lokasi -->
        <h6 class="fw-bold mb-3"><i class="bx bx-map"></i> Peta Lokasi Kampus</h6>
        <div id="map_all" style="height: 400px; width: 100%; border-radius: 8px; border: 1px solid #ddd;" class="mb-4"></div>

        <!-- Section: Daftar Lokasi -->
        <h6 class="fw-bold mb-3"><i class="bx bx-list-ul"></i> Daftar Lokasi Kampus</h6>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Lokasi</th>
                        <th>Koordinat (Lat, Lng)</th>
                        <th>Radius</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $loc)
                    <tr>
                        <td class="fw-bold">{{ $loc->nama_lokasi }}</td>
                        <td>
                            <span class="d-block text-muted" style="font-size: 0.85em;">{{ $loc->latitude }}, {{ $loc->longitude }}</span>
                        </td>
                        <td><span class="badge bg-info">{{ $loc->radius_meter }} Meter</span></td>
                        <td>
                            @if($loc->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm" 
                                    data-id="{{ $loc->id }}"
                                    data-name="{{ $loc->nama_lokasi }}"
                                    data-lat="{{ $loc->latitude }}"
                                    data-lng="{{ $loc->longitude }}"
                                    data-rad="{{ $loc->radius_meter }}"
                                    data-active="{{ $loc->is_active }}"
                                    data-bs-toggle="modal" data-bs-target="#modalEditLocation">
                                <i class="bx bx-edit"></i>
                            </button>
                            <form action="{{ route('settings.locations.delete', $loc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus lokasi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i class="bx bx-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bx bx-map-alt fs-1 mb-2"></i>
                            <p>Belum ada lokasi yang ditambahkan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Add Location -->
<div class="modal fade" id="modalAddLocation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('settings.locations.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Lokasi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lokasi</label>
                            <input type="text" name="nama_lokasi" class="form-control" placeholder="Contoh: Kampus Utama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Radius (Meter)</label>
                            <input type="number" name="radius_meter" id="add_radius" class="form-control" value="100" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Cari Lokasi</label>
                        <div class="input-group">
                            <input type="text" id="search_location" class="form-control" placeholder="Masukkan nama lokasi (misal: SMAN 1 Cianjur)">
                            <button class="btn btn-outline-primary" type="button" onclick="searchLocation()">Cari</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih Titik di Peta (Geser marker)</label>
                        <div id="map_add" style="height: 300px; border-radius: 8px; border: 1px solid #ddd;"></div>
                        <small class="text-muted d-block mt-1">*Klik peta atau geser marker untuk menentukan lokasi.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" id="add_lat" class="form-control bg-light" readonly required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" id="add_lng" class="form-control bg-light" readonly required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Location -->
<div class="modal fade" id="modalEditLocation" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formEdit" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title text-white">Edit Lokasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lokasi</label>
                            <input type="text" name="nama_lokasi" id="edit_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Radius (Meter)</label>
                            <input type="number" name="radius_meter" id="edit_radius" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_active" value="1">
                        <label class="form-check-label" for="edit_active">Lokasi Aktif</label>
                    </div>
                    
                    <div class="mb-3">
                        <div id="map_edit" style="height: 300px; border-radius: 8px; border: 1px solid #ddd;"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="text" name="latitude" id="edit_lat" class="form-control bg-light" readonly required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="text" name="longitude" id="edit_lng" class="form-control bg-light" readonly required>
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

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
     crossorigin=""/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
     crossorigin=""></script>

<script>
    // --- MAP INITIALIZATION ---
    // Default coords (Cianjur)
    const defaultLat = -6.8253015;
    const defaultLng = 107.1370937;

    // --- MAIN MAP DASHBOARD ---
    const mapAll = L.map('map_all').setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(mapAll);

    // Initial Marker group to fit bounds
    const locationGroup = new L.featureGroup();

    const existingLocations = @json($locations);
    existingLocations.forEach(loc => {
        if(loc.latitude && loc.longitude) {
            const marker = L.marker([loc.latitude, loc.longitude])
                .addTo(mapAll)
                .bindPopup(`<b>${loc.nama_lokasi}</b><br>Radius: ${loc.radius_meter}m`);
            
            locationGroup.addLayer(marker);

            L.circle([loc.latitude, loc.longitude], {
                radius: loc.radius_meter,
                color: loc.is_active ? 'green' : 'gray',
                fillColor: loc.is_active ? '#2ecc71' : '#95a5a6',
                fillOpacity: 0.2
            }).addTo(mapAll);
        }
    });

    if(existingLocations.length > 0) {
        mapAll.fitBounds(locationGroup.getBounds());
    }

    let mapAdd, markerAdd, circleAdd;
    let mapEdit, markerEdit, circleEdit;

    // --- ADD MODAL MAP ---
    const modalAdd = document.getElementById('modalAddLocation');
    modalAdd.addEventListener('shown.bs.modal', function () {
        if (!mapAdd) {
            mapAdd = L.map('map_add').setView([defaultLat, defaultLng], 15);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(mapAdd);

            markerAdd = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(mapAdd);
            circleAdd = L.circle([defaultLat, defaultLng], {radius: 100}).addTo(mapAdd);

            markerAdd.on('dragend', function(e) {
                const latlng = markerAdd.getLatLng();
                updateAddInputs(latlng.lat, latlng.lng);
                circleAdd.setLatLng(latlng);
            });

            mapAdd.on('click', function(e) {
                markerAdd.setLatLng(e.latlng);
                circleAdd.setLatLng(e.latlng);
                updateAddInputs(e.latlng.lat, e.latlng.lng);
            });
            
            updateAddInputs(defaultLat, defaultLng);
        } else {
            setTimeout(function(){ mapAdd.invalidateSize(); }, 10);
        }
    });

    document.getElementById('add_radius').addEventListener('input', function() {
        if(circleAdd) circleAdd.setRadius(this.value);
    });

    function updateAddInputs(lat, lng) {
        document.getElementById('add_lat').value = lat.toFixed(7);
        document.getElementById('add_lng').value = lng.toFixed(7);
    }

    // --- SEARCH LOCATION (NOMINATIM) ---
    function searchLocation() {
        const query = document.getElementById('search_location').value;
        if (!query) return;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lon = parseFloat(data[0].lon);
                    
                    if (mapAdd && markerAdd && circleAdd) {
                        const newLatLng = new L.LatLng(lat, lon);
                        mapAdd.setView(newLatLng, 16);
                        markerAdd.setLatLng(newLatLng);
                        circleAdd.setLatLng(newLatLng);
                        updateAddInputs(lat, lon);
                    }
                } else {
                    alert('Lokasi tidak ditemukan!');
                }
            })
            .catch(error => {
                console.error('Error fetching location:', error);
                alert('Gagal mencari lokasi. Coba lagi.');
            });
    }

    // --- EDIT MODAL MAP ---
    const modalEdit = document.getElementById('modalEditLocation');
    modalEdit.addEventListener('shown.bs.modal', function (event) {
        const button = event.relatedTarget; 
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const lat = parseFloat(button.getAttribute('data-lat'));
        const lng = parseFloat(button.getAttribute('data-lng'));
        const rad = parseInt(button.getAttribute('data-rad'));
        const active = button.getAttribute('data-active') == '1';

        document.getElementById('formEdit').action = "/settings/locations/" + id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_radius').value = rad;
        document.getElementById('edit_active').checked = active;
        document.getElementById('edit_lat').value = lat;
        document.getElementById('edit_lng').value = lng;

        if (!mapEdit) {
            mapEdit = L.map('map_edit').setView([lat, lng], 15);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(mapEdit);

            markerEdit = L.marker([lat, lng], {draggable: true}).addTo(mapEdit);
            circleEdit = L.circle([lat, lng], {radius: rad}).addTo(mapEdit);

            markerEdit.on('dragend', function(e) {
                const latlng = markerEdit.getLatLng();
                updateEditInputs(latlng.lat, latlng.lng);
                circleEdit.setLatLng(latlng);
            });
            
            mapEdit.on('click', function(e) {
                markerEdit.setLatLng(e.latlng);
                circleEdit.setLatLng(e.latlng);
                updateEditInputs(e.latlng.lat, e.latlng.lng);
            });
        } else {
            setTimeout(function(){ 
                mapEdit.invalidateSize(); 
                mapEdit.setView([lat, lng], 15);
                markerEdit.setLatLng([lat, lng]);
                circleEdit.setLatLng([lat, lng]);
                circleEdit.setRadius(rad);
            }, 100);
        }
    });

    document.getElementById('edit_radius').addEventListener('input', function() {
        if(circleEdit) circleEdit.setRadius(this.value);
    });

    function updateEditInputs(lat, lng) {
        document.getElementById('edit_lat').value = lat.toFixed(7);
        document.getElementById('edit_lng').value = lng.toFixed(7);
    }
</script>
@endpush
