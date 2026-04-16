<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <span class="app-brand-logo demo">
        <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg">
          <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
            <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
              <g id="Icon" transform="translate(27.000000, 15.000000)">
                <g id="Mask" transform="translate(0.000000, 8.000000)">
                  <mask id="mask-2" fill="white"><use xlink:href="#path-1"></use></mask>
                  <use fill="#696cff" xlink:href="#path-1"></use>
                </g>
              </g>
            </g>
          </g>
        </svg>
      </span>
      <span class="app-brand-text demo menu-text fw-bolder ms-2">E-Absensi</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    @php
      $userRole = auth()->user()->role->nama_role;
    @endphp

    {{-- ADMIN MENU --}}
    @if($userRole === 'admin')
      <li class="menu-item {{ Request::is('dashboard') ? 'active' : '' }}">
        <a href="{{ route('dashboard') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-home-circle"></i>
          <div data-i18n="Dashboard">Dashboard Admin</div>
        </a>
      </li>

      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Data Master</span>
      </li>

      <li class="menu-item {{ Request::is('jurusan*') ? 'active' : '' }}">
        <a href="{{ route('jurusan.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-buildings"></i>
          <div data-i18n="Jurusan">Data Jurusan</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('kelas*') ? 'active' : '' }}">
        <a href="{{ route('kelas.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-door-open"></i>
          <div data-i18n="Kelas">Data Kelas</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('api-kelas*') ? 'active' : '' }}">
        <a href="{{ route('admin.api_kelas.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-cloud-download"></i>
          <div data-i18n="Sync API Kelas">Sync API Kelas</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('data-guru*') ? 'active' : '' }}">
        <a href="{{ route('guru.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-user-pin"></i>
          <div data-i18n="Guru">Data Guru</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('api-guru*') ? 'active' : '' }}">
        <a href="{{ route('admin.api_guru.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-cloud-download"></i>
          <div data-i18n="Sync API Guru">Sync API Guru</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('mata-pelajaran*') ? 'active' : '' }}">
        <a href="{{ route('mata-pelajaran.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
          <div data-i18n="Mata Pelajaran">Data Mata Pelajaran</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('siswa*') ? 'active' : '' }}">
        <a href="{{ route('siswa.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-user-badge"></i>
          <div data-i18n="Siswa">Data Siswa</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('api-siswa*') ? 'active' : '' }}">
        <a href="{{ route('admin.api_siswa.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-cloud-download"></i>
          <div data-i18n="Sync API Siswa">Sync API Siswa</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('assessment-categories*') ? 'active' : '' }}">
        <a href="{{ route('assessment-categories.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-list-check"></i>
          <div data-i18n="Kategori Penilaian">Kategori Penilaian</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('penilaian-guru*') ? 'active' : '' }}">
        <a href="{{ route('admin.assessments.guru') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-star"></i>
          <div data-i18n="Penilaian Guru">Penilaian Guru</div>
        </a>
      </li>

      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Manajemen User</span>
      </li>

      <li class="menu-item {{ Request::is('roles*') ? 'active' : '' }}">
        <a href="{{ route('roles.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
          <div data-i18n="Roles">Roles & Permission</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('users*') ? 'active' : '' }}">
        <a href="{{ route('users.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-group"></i>
          <div data-i18n="Users">Pengguna (Users)</div>
        </a>
      </li>

      <!-- Monitoring Data Dropdown (Admin) -->
      <li class="menu-item {{ Request::is('monitoring*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
          <div data-i18n="Monitoring Data">Monitoring Data</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ Request::is('monitoring/siswa') ? 'active' : '' }}">
            <a href="{{ route('admin.monitoring.siswa') }}" class="menu-link">
              <div data-i18n="Sikap Siswa">Sikap Siswa</div>
            </a>
          </li>
          <li class="menu-item {{ Request::is('monitoring/guru') ? 'active' : '' }}">
            <a href="{{ route('admin.monitoring.guru') }}" class="menu-link">
              <div data-i18n="Kinerja Guru">Kinerja Guru</div>
            </a>
          </li>
          <li class="menu-item {{ Request::is('monitoring/rekap') ? 'active' : '' }}">
            <a href="{{ route('admin.monitoring.recap') }}" class="menu-link">
              <div data-i18n="Rekapitulasi Cetak">Rekapitulasi Cetak</div>
            </a>
          </li>
        </ul>
      </li>

      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Sistem Gamifikasi & Pengaturan</span>
      </li>

      <li class="menu-item {{ Request::is('poin/settings*') ? 'active' : '' }}">
        <a href="{{ route('admin.poin.settings.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-slider"></i>
          <div data-i18n="Pengaturan Poin">Pengaturan Poin</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('poin/marketplace*') ? 'active' : '' }}">
        <a href="{{ route('admin.poin.marketplace.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-store"></i>
          <div data-i18n="Marketplace">Marketplace Token</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('poin/leaderboard*') ? 'active' : '' }}">
        <a href="{{ route('admin.poin.leaderboard.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-trophy"></i>
          <div data-i18n="Leaderboard">Leaderboard Sekolah</div>
        </a>
      </li>

      <li class="menu-item {{ Request::is('settings*') ? 'active' : '' }}">
        <a href="{{ route('settings.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-cog"></i>
          <div data-i18n="Pengaturan Sistem">Pengaturan Sistem</div>
        </a>
      </li>
    @endif

    {{-- GURU MENU --}}
    @if($userRole === 'guru')
      <li class="menu-item {{ Request::is('guru/dashboard') ? 'active' : '' }}">
        <a href="{{ route('guru.dashboard') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-home-circle"></i>
          <div data-i18n="Dashboard">Dashboard Guru</div>
        </a>
      </li>

      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Absensi & Evaluasi</span>
      </li>

      <li class="menu-item {{ Request::is('absensi*') ? 'active' : '' }}">
        <a href="{{ route('absensi.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
          <div data-i18n="Data Absensi">Data Absensi</div>
        </a>
      </li>
      
      <li class="menu-item {{ Request::is('kehadiran*') ? 'active' : '' }}">
        <a href="{{ route('kehadiran.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-list-check"></i>
          <div data-i18n="Data Kehadiran">Data Kehadiran</div>
        </a>
      </li>
      
      <li class="menu-item {{ Request::is('penilaian-siswa*') ? 'active' : '' }}">
        <a href="{{ route('guru.assessments.siswa') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-star"></i>
          <div data-i18n="Penilaian Siswa">Penilaian Siswa</div>
        </a>
      </li>
      
      <!-- Monitoring Data Dropdown (Guru) -->
      <li class="menu-item {{ Request::is('monitoring-kelas*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
          <i class="menu-icon tf-icons bx bx-pie-chart-alt"></i>
          <div data-i18n="Monitoring Siswa">Monitoring Siswa</div>
        </a>
        <ul class="menu-sub">
          <li class="menu-item {{ Request::is('monitoring-kelas') || Request::is('monitoring-kelas/siswa*') ? 'active' : '' }}">
            <a href="{{ route('guru.monitoring.siswa') }}" class="menu-link">
              <div data-i18n="Pantau Karakter Kelas">Pantau Karakter Kelas</div>
            </a>
          </li>
          <li class="menu-item {{ Request::is('monitoring-kelas/rekap') ? 'active' : '' }}">
            <a href="{{ route('guru.monitoring.recap') }}" class="menu-link">
              <div data-i18n="Rekapitulasi Cetak">Rekapitulasi Cetak</div>
            </a>
          </li>
        </ul>
      </li>
      
      <li class="menu-item {{ Request::is('guru/laporanku*') ? 'active' : '' }}">
        <a href="{{ route('guru.reports.my') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-radar"></i>
          <div data-i18n="Laporan Kinerjaku">Laporan Kinerjaku</div>
        </a>
      </li>
    @endif

    {{-- SISWA MENU --}}
    @if($userRole === 'siswa')
      <li class="menu-item {{ Request::is('siswa/dashboard') ? 'active' : '' }}">
        <a href="{{ route('siswa.dashboard') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-home-circle"></i>
          <div data-i18n="Dashboard">Dashboard Siswa</div>
        </a>
      </li>

      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Absensi & Evaluasi</span>
      </li>

      <li class="menu-item {{ Request::is('scan') ? 'active' : '' }}">
        <a href="{{ route('scan.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-qr-scan"></i>
          <div data-i18n="Scan">Scanner Absensi</div>
        </a>
      </li>
      
      <li class="menu-item {{ Request::is('siswa/laporanku*') ? 'active' : '' }}">
        <a href="{{ route('siswa.reports.my') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-radar"></i>
          <div data-i18n="Laporan Sikapku">Laporan Sikapku</div>
        </a>
      </li>

      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Integritas</span>
      </li>

      <li class="menu-item {{ Request::is('dompet*') ? 'active' : '' }}">
        <a href="{{ route('siswa.wallet.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-wallet"></i>
          <div data-i18n="Dompet Integritas">Dompet & Token</div>
        </a>
      </li>
    @endif
  </ul>
</aside>