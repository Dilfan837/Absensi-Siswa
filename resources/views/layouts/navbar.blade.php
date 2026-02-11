<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
  id="layout-navbar">
  <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
    <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
      <i class="bx bx-menu bx-sm"></i>
    </a>
  </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    <!-- Search -->
    <div class="navbar-nav align-items-center">
      <div class="nav-item d-flex align-items-center">
        <i class="bx bx-search fs-4 lh-0"></i>
        <input type="text" id="searchInput" class="form-control border-0 shadow-none"
          placeholder="Cari..." aria-label="Search..." />
      </div>
    </div>
    <!-- /Search -->

    <ul class="navbar-nav flex-row align-items-center ms-auto">

      <!-- User Dropdown -->
      <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <img src="{{ auth()->user()->foto_profile }}" alt="User" class="w-px-40 h-auto rounded-circle" />
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <a class="dropdown-item" href="#">
              <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                  <div class="avatar avatar-online">
                    <img src="{{ auth()->user()->foto_profile }}" alt class="w-px-40 h-auto rounded-circle" />
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

<script>
  document.getElementById('searchInput').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      // Implement Search Logic Here
      // For now, we will just log it or redirect if needed.
      // Since the request is "fungsikan gunakan js aja", we can redirect to a search param.
      // let query = this.value;
      // window.location.href = "{{ route('siswa.index') }}?search=" + query; 
      // But user said "js aja", maybe preventing form submission was the goal.
      // I'll leave the redirection commented out or active depending on interpretation.
      // Given the previous form went to siswa.index, I'll redirect there.
      let query = this.value;
      if(query) {
         window.location.href = "{{ route('siswa.index') }}?search=" + encodeURIComponent(query);
      }
    }
  });
</script>
</nav>