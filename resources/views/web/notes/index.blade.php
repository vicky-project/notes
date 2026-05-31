@extends('notes::layouts.web')
@section('title', 'Daftar Catatan')

@section('content')
<div class="row mb-4">
  <div class="col-md-4 text-end">
    <a href="{{ route('notes.web.create') }}" class="btn btn-warning">
      <i class="bi bi-plus-lg"></i> Catatan Baru
    </a>
  </div>
</div>

<!-- Search & Filter -->
<div class="card mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('notes.web.index') }}">
      <div class="row g-2">
        <div class="col-md-6">
          <input type="text" name="search" class="form-control" placeholder="Cari catatan..." value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
          <select name="tag" class="form-select">
            <option value="">Semua Tag</option>
            @foreach($allTags as $tag)
            <option value="{{ $tag->name }}" {{ request('tag') == $tag->name ? 'selected' : '' }}>{{ $tag->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Notes List -->
@forelse($notes as $note)
<div class="card card-note mb-2" onclick="window.location='{{ route('notes.web.show', $note->id) }}'">
  <div class="card-body">
    <div class="d-flex justify-content-between">
      <h6>{{ $note->title }}</h6>
      <small class="text-muted">{{ $note->created_at->translatedFormat('d M Y') }}</small>
    </div>
    @if($note->content)
    <p class="text-muted small mb-2">
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
<div class="text-center py-5">
  <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
  <p class="text-muted mt-2">
    Tidak ada catatan ditemukan.
  </p>
</div>
@endforelse

<div class="mt-4">
  {{ $notes->links() }}
</div>
@endsection