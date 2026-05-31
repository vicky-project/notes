@extends('notes::layouts.web')
@section('title', 'Trash')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="mb-0"><i class="bi bi-trash me-2"></i>Trash</h2>
</div>

@forelse($notes as $note)
<div class="card note-card mb-2">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h6>{{ $note->title }}</h6>
        <small class="text-muted">Dihapus: {{ $note->deleted_at->translatedFormat('d M Y, H:i') }}</small>
        <div class="d-flex flex-wrap gap-1 mt-1">
          @foreach($note->tags as $tag)
          <span class="tag-badge">{{ $tag->name }}</span>
          @endforeach
        </div>
      </div>
      <div class="d-flex gap-2">
        <form action="{{ route('notes.web.trash.restore', $note->id) }}" method="POST">
          @csrf @method('PATCH')
          <button type="submit" class="btn btn-sm btn-outline-success" title="Pulihkan">
            <i class="bi bi-arrow-counterclockwise"></i>
          </button>
        </form>
        <form action="{{ route('notes.web.trash.force-delete', $note->id) }}" method="POST" onsubmit="return confirm('Hapus permanen?')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Permanen">
            <i class="bi bi-trash-fill"></i>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@empty
<div class="text-center py-5">
  <i class="bi bi-trash" style="font-size: 3rem; color: #ccc;"></i>
  <p class="text-muted mt-2">
    Trash kosong.
  </p>
</div>
@endforelse
@endsection