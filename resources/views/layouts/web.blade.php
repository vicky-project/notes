<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Notes')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
  <style>
:root {
    --sidebar-width: 260px;
  }
    body {
      background-color: #f8f9fa;
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: var(--sidebar-width);
      height: 100vh;
      background: #1a1a2e;
      color: #fff;
      padding: 1rem;
      overflow-y: auto;
      z-index: 1050;
      transition: transform 0.3s ease;
    }
    .sidebar .nav-link {
      color: rgba(255,255,255,0.7);
      border-radius: 8px;
      padding: 0.75rem 1rem;
      margin-bottom: 0.25rem;
      transition: all 0.2s;
    }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background: rgba(255,255,255,0.1);
      color: #ffc107;
    }
    .sidebar .nav-link i {
      margin-right: 0.5rem;
    }

    .main-content {
      margin-left: var(--sidebar-width);
      padding: 2rem;
      min-height: 100vh;
      transition: margin-left 0.3s ease;
    }

    .sidebar-toggle {
      display: none;
      position: fixed;
      top: 1rem;
      left: 1rem;
      z-index: 1060;
      background: #1a1a2e;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 0.5rem 0.75rem;
      font-size: 1.25rem;
      cursor: pointer;
    }
    .sidebar-toggle:hover {
      background: #2a2a4e;
    }

    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1040;
    }
    .sidebar-overlay.show {
      display: block;
    }

    .card-note {
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      transition: transform 0.15s, box-shadow 0.15s;
      cursor: pointer;
    }
    .card-note:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .tag-badge {
      background: #e9ecef;
      color: #495057;
      border-radius: 20px;
      padding: 0.25rem 0.75rem;
      font-size: 0.8rem;
    }
    .reminder-card {
      border-left: 4px solid #ffc107;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.open {
        transform: translateX(0);
      }
      .main-content {
        margin-left: 0 !important;
        padding-top: 4.5rem;
        /* ruang untuk tombol toggle + title */
      }
      .sidebar-toggle {
        display: block;
      }
    }
  </style>
  @stack('styles')
</head>
<body>
  <div class="sidebar-overlay" id="sidebarOverlay"></div>
  <button class="sidebar-toggle" id="sidebarToggle">
    <i class="bi bi-list"></i>
  </button>

  <nav class="sidebar" id="sidebar">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="mb-0">📝 Notes</h4>
      <button class="btn btn-sm btn-outline-light d-md-none" id="closeSidebarBtn">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a href="{{ route('notes.web.home') }}" class="nav-link {{ request()->routeIs('notes.web.home') ? 'active' : '' }}">
          <i class="bi bi-house-door"></i> Beranda
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('notes.web.index') }}" class="nav-link {{ request()->routeIs('notes.web.index') ? 'active' : '' }}">
          <i class="bi bi-journals"></i> Catatan
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('notes.web.daily') }}" class="nav-link {{ request()->routeIs('notes.web.daily') ? 'active' : '' }}">
          <i class="bi bi-calendar3"></i> Daily
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('notes.web.reminders') }}" class="nav-link {{ request()->routeIs('notes.web.reminders') ? 'active' : '' }}">
          <i class="bi bi-bell"></i> Pengingat
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('notes.web.trash') }}" class="nav-link {{ request()->routeIs('notes.web.trash') ? 'active' : '' }}">
          <i class="bi bi-trash"></i> Trash
        </a>
      </li>
      <li class="nav-item mt-4">
        <a href="{{ route('notes.web.profile') }}" class="nav-link {{ request()->routeIs('notes.web.profile') ? 'active' : '' }}">
          <i class="bi bi-person-circle"></i> Profil
        </a>
      </li>
      @php
      $backRoute = config('notes.back_home_route', 'notes.web.home');
      @endphp
      <li class="nav-item">
        <a href="{{ route($backRoute) }}" class="nav-link">
          <i class="bi bi-arrow-left"></i> Kembali
        </a>
      </li>
    </ul>
  </nav>

  <main class="main-content">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Title dan area tombol aksi disediakan oleh masing-masing view --}}
    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
  <script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const toggleBtn = document.getElementById('sidebarToggle');

    function openSidebar() {
      sidebar.classList.add('open');
      overlay.classList.add('show');
      toggleBtn.style.display = 'none';
    }
    function closeSidebar() {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
      toggleBtn.style.display = '';
    }

    toggleBtn.addEventListener('click', openSidebar);
    document.getElementById('closeSidebarBtn')?.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
    if (window.innerWidth <= 768) closeSidebar();
    });
    });
  </script>
  @stack('scripts')
</body>
</html>