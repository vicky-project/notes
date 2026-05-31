@extends('notes::layouts.web')
@section('title', 'Pengingat')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="mb-0">Pengingat</h2>
</div>

@php
$now = now();
$sorted = $reminders->sortBy(function($r) use ($now) {
if ($r->is_completed) return 3;
if ($r->remind_at->gt($now) && !$r->notified_at) return 0;
if ($r->notified_at && !$r->is_completed) return 1;
return 2;
});
@endphp

@forelse($sorted as $reminder)
<div class="card reminder-card mb-2">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h6>
          {{ $reminder->note->title ?? 'Tanpa Judul' }}
          @if($reminder->is_completed)
          <span class="badge bg-success ms-2"><i class="bi bi-check-all"></i> Selesai</span>
          @elseif($reminder->notified_at)
          <span class="badge bg-info ms-2"><i class="bi bi-send-check"></i> Terkirim</span>
          @elseif($reminder->remind_at->lt(now()))
          <span class="badge bg-warning ms-2"><i class="bi bi-exclamation-triangle"></i> Terlewat</span>
          @endif
        </h6>
        <small class="text-muted">{{ $reminder->remind_at->translatedFormat('d M Y, H:i') }}</small>
      </div>
      <div class="d-flex gap-2">
        @if(!$reminder->is_completed)
        <form action="{{ route('notes.web.reminders.complete', $reminder->id) }}" method="POST">
          @csrf @method('PATCH')
          <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i></button>
        </form>
        @endif
        <form action="{{ route('notes.web.reminders.destroy', $reminder->id) }}" method="POST" onsubmit="return confirm('Hapus pengingat ini?')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
      </div>
    </div>
  </div>
</div>
@empty
<div class="text-center py-5">
  <i class="bi bi-bell-slash" style="font-size: 3rem; color: #ccc;"></i>
  <p class="text-muted mt-2">
    Tidak ada pengingat.
  </p>
</div>
@endforelse
@endsection