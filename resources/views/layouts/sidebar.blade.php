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

      <li class="menu-item {{ Request::is('siswa*') ? 'active' : '' }}">
        <a href="{{ route('siswa.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-user-badge"></i>
          <div data-i18n="Siswa">Data Siswa</div>
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

      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">Pengaturan</span>
      </li>

      <li class="menu-item {{ Request::is('settings*') ? 'active' : '' }}">
        <a href="{{ route('settings.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-cog"></i>
          <div data-i18n="Settings">Settings</div>
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
        <span class="menu-header-text">Manajemen Absensi</span>
      </li>

      <li class="menu-item {{ Request::is('absensi*') ? 'active' : '' }}">
        <a href="{{ route('absensi.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
          <div data-i18n="Absensi">Data Absensi</div>
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
        <span class="menu-header-text">Absensi</span>
      </li>

      <li class="menu-item {{ Request::is('scan') ? 'active' : '' }}">
        <a href="{{ route('scan.index') }}" class="menu-link">
          <i class="menu-icon tf-icons bx bx-qr-scan"></i>
          <div data-i18n="Scan">Scanner Absensi</div>
        </a>
      </li>
    @endif
  </ul>
</aside>