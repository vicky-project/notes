<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Notes')</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Quill -->
  <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
  <style>
:root {
    --sidebar-width: 250px;
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
      z-index: 1000;
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
    .checklist-item {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem;
      border-radius: 8px;
      transition: background 0.2s;
    }
    .checklist-item:hover {
      background: #f1f3f5;
    }
    .reminder-card {
      border-left: 4px solid #ffc107;
    }
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }
      .main-content {
        margin-left: 0;
      }
    }
  </style>
  @stack('styles')
</head>
<body>
  <!-- Sidebar -->
  <nav class="sidebar">
    <h4 class="mb-4">📝 Notes</h4>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a href="{{ route('notes.web.home') }}" class="nav-link {{ request()->routeIs('notes.web.home') ? 'active' : '' }}">
          <i class="bi bi-house-door"></i> Beranda
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('notes.web.index') }}" class="nav-link {{ request()->routeIs('notes.web.*') && !request()->routeIs('notes.web.daily', 'notes.web.reminders', 'notes.web.profile', 'notes.web.trash', 'notes.web.home') ? 'active' : '' }}">
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
      <li class="nav-item">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent">
            <i class="bi bi-box-arrow-right"></i> Logout
          </button>
        </form>
      </li>
    </ul>
  </nav>

  <!-- Main Content -->
  <main class="main-content">
    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
  @stack('scripts')
</body>
</html>