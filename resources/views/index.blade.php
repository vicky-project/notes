@extends('telegram::layouts.mini-app')

@section('title', 'Notes')

@section('content')
<div id="app-content" class="p-3 pb-5">
  {{-- Placeholder loading awal --}}
  <div class="d-flex justify-content-center align-items-center" style="height: 60vh;">
    <div class="text-center">
      <div class="spinner-border text-secondary mb-2" role="status">
        <span class="visually-hidden">Memuat...</span>
      </div>
      <p class="text-muted">
        Memuat catatan...
      </p>
    </div>
  </div>
</div>

{{-- Bottom Navigation --}}
<nav class="fixed-bottom bg-dark bg-opacity-75 backdrop-blur border-top border-secondary">
  <div class="d-flex justify-content-around align-items-center py-2">
    <a href="#/notes/home" class="nav-link text-decoration-none text-center" data-route="/notes/home">
      <i class="bi bi-house-door fs-5 d-block"></i>
      <small>Beranda</small>
    </a>
    <a href="#/notes/all" class="nav-link text-decoration-none text-center" data-route="/notes/all">
      <i class="bi bi-journals fs-5 d-block"></i>
      <small>Catatan</small>
    </a>
    {{-- Tombol + besar di tengah --}}
    <a href="#/notes/create" class="btn btn-warning rounded-circle shadow-lg d-flex align-items-center justify-content-center"
      style="width: 60px; height: 60px; margin-top: -30px; z-index: 10; transition: transform 0.2s;">
      <i class="bi bi-plus-lg fs-2"></i>
    </a>
    <a href="#/notes/reminders" class="nav-link text-decoration-none text-center" data-route="/notes/reminders">
      <i class="bi bi-bell fs-5 d-block"></i>
      <small>Pengingat</small>
    </a>
    <a href="#/notes/profile" class="nav-link text-decoration-none text-center" data-route="/notes/profile">
      <i class="bi bi-person-circle fs-5 d-block"></i>
      <small>Profil</small>
    </a>
  </div>
</nav>
@endsection

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/eruda"></script>
<script>
  eruda.init();
</script>
<script>
  const BASE_URL = '{{ rtrim(config("app.url"), "/") }}';

  {!! file_get_contents(module_path('notes', 'resources/assets/js/core.js')); !!}
  {!! file_get_contents(module_path('notes', 'resources/assets/js/page.js')); !!}
  {!! file_get_contents(module_path('notes', 'resources/assets/js/main.js')); !!}
</script>
@endpush

@push('styles')
<style>
  /* ========== Global ========== */
  body {
    background-color: var(--tg-theme-bg-color, #1a1a2e) !important;
    color: var(--tg-theme-text-color, #e0e0e0) !important;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    #app-content {
    transition: opacity 0.15s ease-in-out;
    }
    #app-content.page-loading {
    opacity: 0.3;
    pointer-events: none;
    }

    /* ========== Glassmorphism ========== */
    .glass-card {
    background: rgba(255, 255, 255, 0.05) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 16px !important;
    }

    .glass-input {
    background: rgba(255, 255, 255, 0.08) !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    color: var(--tg-theme-text-color, #e0e0e0) !important;
    border-radius: 12px !important;
    padding: 0.75rem 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    }
    .glass-input:focus {
    border-color: var(--tg-theme-button-color, #ffc107) !important;
    box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2) !important;
    outline: none;
    }

    .btn-glass {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: inherit;
    transition: all 0.2s;
    }
    .btn-glass:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.3);
    }

    /* ========== Kartu Catatan ========== */
    .note-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
    overflow: hidden;
    }
    .note-card:hover, .note-card:active {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    border-color: rgba(255, 193, 7, 0.4);
    }

    /* ========== Bottom Nav ========== */
    .nav-link {
    color: var(--tg-theme-hint-color, #a0a0a0);
    transition: color 0.2s, transform 0.15s;
    }
    .nav-link.active-link, .nav-link.text-warning {
    color: var(--tg-theme-button-color, #ffc107) !important;
    transform: scale(1.05);
    }
    .nav-link:active {
    transform: scale(0.95);
    }

    /* Tombol tengah */
    .create-fab {
    background: linear-gradient(135deg, #ffc107, #ff9800);
    border: none;
    color: #1a1a2e;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
    transition: transform 0.2s, box-shadow 0.2s;
    }
    .create-fab:active {
    transform: scale(0.9);
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.5);
    }

    /* ========== Empty State ========== */
    .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    text-align: center;
    color: var(--tg-theme-hint-color, #a0a0a0);
    }
    .empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
    }

    /* ========== Profil ========== */
    .profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 2rem;
    border: 2px solid rgba(255,255,255,0.2);
    }
    </style>
    @endpush