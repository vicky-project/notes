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
    --yellow: #FFC107;
    --yellow-dark: #E0A800;
    --yellow-light: #FFF3CD;
    --bg-warm: #FFF9E6;
    --sidebar-bg: #1A1A2E;
    --card-bg: #FFFFFF;
    --text-primary: #2D2D2D;
    --text-muted: #6C757D;
    --border-color: #E9ECEF;
  }

    body {
      background-color: var(--bg-warm);
      color: var(--text-primary);
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: var(--sidebar-width);
      height: 100vh;
      background: var(--sidebar-bg);
      color: #fff;
      padding: 1rem;
      overflow-y: auto;
      z-index: 1050;
      transition: transform 0.3s ease;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    .sidebar .nav-link {
      color: rgba(255,255,255,0.75);
      border-radius: 8px;
      padding: 0.75rem 1rem;
      margin-bottom: 0.25rem;
      transition: all 0.2s;
      font-weight: 500;
    }
    .sidebar .nav-link:hover {
      background: rgba(255,193,7,0.15);
      color: var(--yellow);
    }
    .sidebar .nav-link.active {
      background: rgba(255,193,7,0.25);
      color: var(--yellow);
      font-weight: 600;
    }
    .sidebar .nav-link i {
      margin-right: 0.5rem;
    }
    .sidebar .brand {
      font-weight: 700;
      font-size: 1.3rem;
      letter-spacing: -0.5px;
    }
    .sidebar .brand span {
      color: var(--yellow);
    }

    /* Main content */
    .main-content {
      margin-left: var(--sidebar-width);
      padding: 2rem;
      min-height: 100vh;
      transition: margin-left 0.3s ease;
      background: var(--bg-warm);
    }

    /* Hamburger button */
    .sidebar-toggle {
      display: none;
      position: fixed;
      top: 1rem;
      left: 1rem;
      z-index: 1060;
      background: var(--sidebar-bg);
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 0.5rem 0.75rem;
      font-size: 1.25rem;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .sidebar-toggle:hover {
      background: #2a2a4e;
    }

    /* Overlay */
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.4);
      z-index: 1040;
      backdrop-filter: blur(2px);
    }
    .sidebar-overlay.show {
      display: block;
    }

    /* Cards */
    .card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 14px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      transition: transform 0.15s, box-shadow 0.15s;
    }
    .card:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    .card-note {
      cursor: pointer;
      border-left: 4px solid transparent;
      transition: border-color 0.2s, transform 0.15s, box-shadow 0.15s;
    }
    .card-note:hover {
      border-left-color: var(--yellow);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    .card-note h6 {
      color: var(--text-primary);
      font-weight: 600;
    }

    /* Tags */
    .tag-badge {
      background: var(--yellow-light);
      color: var(--yellow-dark);
      border-radius: 20px;
      padding: 0.25rem 0.75rem;
      font-size: 0.78rem;
      font-weight: 500;
    }

    /* Reminders */
    .reminder-card {
      border-left: 4px solid var(--yellow) !important;
      background: #FFFEF5;
    }

    /* Buttons */
    .btn-warning {
      background: var(--yellow);
      border-color: var(--yellow);
      color: #1A1A2E;
      font-weight: 600;
      border-radius: 10px;
      padding: 0.5rem 1.25rem;
      transition: all 0.2s;
    }
    .btn-warning:hover {
      background: var(--yellow-dark);
      border-color: var(--yellow-dark);
      color: #1A1A2E;
      box-shadow: 0 4px 12px rgba(224,168,0,0.3);
    }

    /* Alerts */
    .alert-success {
      background: #D4EDDA;
      border-color: #C3E6CB;
      color: #155724;
      border-radius: 10px;
    }

    /* Form controls */
    .form-control, .form-select {
      border-radius: 10px;
      border-color: var(--border-color);
      padding: 0.6rem 1rem;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--yellow);
      box-shadow: 0 0 0 3px rgba(255,193,7,0.2);
    }

    /* Responsive */
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
      <h4 class="brand mb-0">📝 <span>Notes</span></h4>
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