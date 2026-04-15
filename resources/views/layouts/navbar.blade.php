<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="bx bx-menu bx-sm"></i>
    </a>
  </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    <div class="navbar-nav align-items-center" style="position: relative; flex: 1; max-width: 400px;">
      <div class="nav-item d-flex align-items-center w-100">
        <i class="bx bx-search fs-4 lh-0"></i>
        <input type="text" id="searchInput" class="form-control border-0 shadow-none"
          placeholder="Cari siswa, guru, kelas..." aria-label="Search..." autocomplete="off" />
      </div>

      <!-- Search Results Dropdown -->
      <div id="searchResults" style="
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 0 0 8px 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        max-height: 380px;
        overflow-y: auto;
        z-index: 9999;
      ">
        <!-- Results will be injected here -->
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">

      <!-- User Dropdown -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <img src="{{ auth()->user()->foto_profile }}" alt="User" class="w-px-40 rounded-circle" style="height: 40px; object-fit: cover;" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="#">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online">
                    <img src="{{ auth()->user()->foto_profile }}" alt class="w-px-40 rounded-circle" style="height: 40px; object-fit: cover;" />
                  </div>
                </div>
                <div class="flex-grow-1">
                  <span class="fw-semibold d-block">{{ auth()->user()->username }}</span>
                  <small class="text-muted">{{ ucfirst(auth()->user()->role->nama_role) }}</small>
                </div>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li>
            <a class="dropdown-item" href="{{ route('profile.index') }}">
              <i class="bx bx-user me-2"></i>
              <span class="align-middle">My Profile</span>
            </a>
          </li>
          <li>
            <div class="dropdown-divider"></div>
          </li>
          <li>
            <!-- Logout Button -->
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="dropdown-item" style="cursor: pointer; border: none; background: none; text-align: left; width: 100%;">
                <i class="bx bx-power-off me-2"></i>
                <span class="align-middle">Logout</span>
              </button>
            </form>
          </li>
        </ul>
      </li>
      <!--/ User -->
    </ul>
  </div>

<style>
  #searchResults .search-category {
    padding: 6px 14px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #697a8d;
    background: #f5f5f9;
    border-bottom: 1px solid #eee;
  }
  #searchResults .search-item {
    display: flex;
    align-items: center;
    padding: 10px 14px;
    text-decoration: none;
    color: #333;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.15s ease;
    cursor: pointer;
  }
  #searchResults .search-item:hover {
    background: #f0f4ff;
  }
  #searchResults .search-item .search-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 18px;
    flex-shrink: 0;
  }
  #searchResults .search-item .search-icon.siswa { background: #e8f5e9; color: #2e7d32; }
  #searchResults .search-item .search-icon.guru { background: #e3f2fd; color: #1565c0; }
  #searchResults .search-item .search-icon.kelas { background: #fff3e0; color: #e65100; }
  #searchResults .search-item .search-icon.mapel { background: #f3e5f5; color: #7b1fa2; }
  #searchResults .search-item .search-icon.absensi { background: #fce4ec; color: #c62828; }
  #searchResults .search-item .search-icon.riwayat { background: #e0f7fa; color: #00695c; }
  #searchResults .search-item .search-text { flex: 1; min-width: 0; }
  #searchResults .search-item .search-title {
    font-weight: 600; font-size: 13px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  #searchResults .search-item .search-subtitle {
    font-size: 11px; color: #999;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  }
  #searchResults .search-empty {
    padding: 24px 14px;
    text-align: center;
    color: #999;
    font-size: 13px;
  }
  #searchResults .search-empty i { font-size: 32px; display: block; margin-bottom: 8px; color: #ccc; }
  #searchResults .search-loading {
    padding: 20px 14px;
    text-align: center;
    color: #aaa;
    font-size: 13px;
  }
</style>

<script>
(function() {
  const input = document.getElementById('searchInput');
  const dropdown = document.getElementById('searchResults');
  let debounceTimer = null;

  const categoryIconClass = {
    'Siswa': 'siswa',
    'Guru': 'guru',
    'Kelas': 'kelas',
    'Mata Pelajaran': 'mapel',
    'Sesi Absensi': 'absensi',
    'Riwayat Absensi': 'riwayat',
  };

  input.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const q = this.value.trim();

    if (q.length < 2) {
      dropdown.style.display = 'none';
      return;
    }

    dropdown.innerHTML = '<div class="search-loading"><i class="bx bx-loader-alt bx-spin"></i> Mencari...</div>';
    dropdown.style.display = 'block';

    debounceTimer = setTimeout(() => {
      fetch("{{ route('search') }}?q=" + encodeURIComponent(q), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(res => res.json())
      .then(data => {
        if (data.length === 0) {
          dropdown.innerHTML = '<div class="search-empty"><i class="bx bx-search-alt"></i>Tidak ditemukan hasil untuk "<strong>' + q + '</strong>"</div>';
          return;
        }

        // Group by category
        let grouped = {};
        data.forEach(item => {
          if (!grouped[item.category]) grouped[item.category] = [];
          grouped[item.category].push(item);
        });

        let html = '';
        for (let cat in grouped) {
          html += '<div class="search-category">' + cat + '</div>';
          grouped[cat].forEach(item => {
            const iconClass = categoryIconClass[item.category] || 'siswa';
            html += '<a href="' + item.url + '" class="search-item">';
            html += '  <div class="search-icon ' + iconClass + '"><i class="bx ' + item.icon + '"></i></div>';
            html += '  <div class="search-text">';
            html += '    <div class="search-title">' + item.title + '</div>';
            html += '    <div class="search-subtitle">' + item.subtitle + '</div>';
            html += '  </div>';
            html += '</a>';
          });
        }

        dropdown.innerHTML = html;
      })
      .catch(() => {
        dropdown.innerHTML = '<div class="search-empty"><i class="bx bx-error"></i>Gagal memuat hasil pencarian</div>';
      });
    }, 300);
  });

  // Hide dropdown on click outside
  document.addEventListener('click', function(e) {
    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.style.display = 'none';
    }
  });

  // Show dropdown again on focus if has content
  input.addEventListener('focus', function() {
    if (this.value.trim().length >= 2 && dropdown.innerHTML.trim() !== '') {
      dropdown.style.display = 'block';
    }
  });

  // Keyboard nav: Escape to close
  input.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      dropdown.style.display = 'none';
      this.blur();
    }
  });
})();
</script>
</nav>