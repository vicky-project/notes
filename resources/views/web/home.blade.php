@extends('notes::layouts.web')
@section('title', 'Beranda - Notes')

@section('content')
<div class="row mb-4">
  <div class="col-md-8">
    <h2>Beranda</h2>
  </div>
  <div class="col-md-4 text-end">
    <a href="{{ route('notes.web.create') }}" class="btn btn-warning">
      <i class="bi bi-plus-lg"></i> Catatan Baru
    </a>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <!-- Quick Capture -->
    <div class="card mb-4">
      <div class="card-body">
        <form action="{{ route('notes.web.store') }}" method="POST">
          @csrf
          <input type="hidden" name="type" value="text">
          <div class="input-group">
            <input type="text" name="title" class="form-control" placeholder="Tulis ide cepat...">
            <button type="submit" class="btn btn-warning"><i class="bi bi-plus-lg"></i></button>
          </div>
        </form>
      </div>
    </div>

    <!-- Recent Notes -->
    <h5>📝 Catatan Terbaru</h5>
    @forelse($notes as $note)
    <div class="card card-note mb-2" onclick="window.location='{{ route('notes.web.show', $note->id) }}'">
      <div class="card-body">
        <h6>{{ $note->title }}</h6>
        @if($note->content)
        <p class="text-muted small">
          {{ \Illuminate\Support\Str::limit(strip_tags($note->content), 100) }}
        </p>
        @endif
        <div class="d-flex flex-wrap gap-1">
          @foreach($note->tags as $tag)
          <span class="tag-badge">{{ $tag->name }}</span>
          @endforeach
        </div>
      </div>
    </div>
    @empty
    <p class="text-muted">
      Belum ada catatan.
    </p>
    @endforelse
  </div>

  <div class="col-md-4">
    <!-- Today's Reminders -->
    <h5>⏰ Pengingat Hari Ini</h5>
    @php
    $todayReminders = $reminders->filter(function($r) {
    return $r->remind_at->isToday() && !$r->is_completed;
    });
    @endphp
    @forelse($todayReminders as $reminder)
    <div class="card reminder-card mb-2">
      <div class="card-body">
        <h6>{{ $reminder->note->title ?? 'Tanpa Judul' }}</h6>
        <small class="text-muted">{{ $reminder->remind_at->translatedFormat('d M Y, H:i') }}</small>
        <form action="{{ route('notes.web.reminders.complete', $reminder->id) }}" method="POST" class="mt-2">
          @csrf
          @method('PATCH')
          <button type="submit" class="btn btn-sm btn-outline-success">✓ Selesaikan</button>
        </form>
      </div>
    </div>
    @empty
    <p class="text-muted">
      Tidak ada pengingat hari ini.
    </p>
    @endforelse
  </div>
</div>
@endsection