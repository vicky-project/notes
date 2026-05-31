@extends('notes::layouts.web')
@section('title', 'Profil')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="mb-0">Profil</h2>
</div>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card">
      <div class="card-body text-center">
        <div style="width: 80px; height: 80px; border-radius: 50%; background: #e9ecef; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
          <i class="bi bi-person-circle"></i>
        </div>
        <h5>{{ auth()->user()->name ?? auth()->user()->first_name ?? 'User' }}</h5>
        <p class="text-muted">
          {{ auth()->user()->email ?? '' }}
        </p>

        <hr>

        <div class="d-flex justify-content-between mb-2">
          <span>Total Catatan</span>
          <span class="badge bg-warning text-dark">{{ $totalNotes }}</span>
        </div>
        <div class="d-flex justify-content-between mb-3">
          <span>Pengingat Aktif</span>
          <span class="badge bg-warning text-dark">{{ $activeReminders }}</span>
        </div>

        <a href="{{ route('notes.web.trash') }}" class="btn btn-outline-secondary w-100 mb-2">
          <i class="bi bi-trash"></i> Trash
        </a>

        <a href="/" class="btn btn-outline-primary w-100">
          <i class="bi bi-house"></i> Kembali ke Halaman Utama
        </a>
      </div>
    </div>
  </div>
</div>
@endsection