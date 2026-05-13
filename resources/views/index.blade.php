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
  <div class="d-flex justify-content-around py-2">
    <a href="#/notes/home" class="nav-link text-decoration-none text-center" data-route="/notes/home">
      <i class="bi bi-house-door fs-5 d-block"></i>
      <small>Beranda</small>
    </a>
    <a href="#/notes/all" class="nav-link text-decoration-none text-center" data-route="/notes/all">
      <i class="bi bi-journals fs-5 d-block"></i>
      <small>Catatan</small>
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
  eruda.init(); // Ikon Eruda akan muncul
</script>
<script>
  const BASE_URL = '{{ rtrim(config("app.url"), "/") }}';

  {!! file_get_contents(module_path('notes', 'resources/assets/js/core.js')); !!}
  {!! file_get_contents(module_path('notes', 'resources/assets/js/page.js')); !!}
  {!! file_get_contents(module_path('notes', 'resources/assets/js/main.js')); !!}
</script>
@endpush