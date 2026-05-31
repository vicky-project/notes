@extends('notes::layouts.web')
@section('title', $note->title)

@section('content')
<div class="mb-3">
  <a href="{{ route('notes.web.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> Kembali
  </a>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <h3>{{ $note->title }}</h3>
      <div class="d-flex gap-2">
        <a href="{{ route('notes.web.edit', $note->id) }}" class="btn btn-primary btn-sm">
          <i class="bi bi-pencil"></i> Edit
        </a>
        <form action="{{ route('notes.web.destroy', $note->id) }}" method="POST" onsubmit="return confirm('Hapus catatan ini?')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
        </form>
      </div>
    </div>

    @if($note->type === 'checklist')
    @php $items = json_decode($note->content, true); @endphp
    @if(is_array($items))
    <div class="mb-3">
      @foreach($items as $index => $item)
      <div class="checklist-item">
        <i class="bi {{ ($item['done'] ?? false) ? 'bi-check-square-fill text-success' : 'bi-square' }}"></i>
        <span class="{{ ($item['done'] ?? false) ? 'text-decoration-line-through text-muted' : '' }}">
          {{ $item['text'] ?? $item }}
        </span>
      </div>
      @endforeach
    </div>
    @endif
    @elseif($note->type === 'image')
    <img src="{{ $note->content }}" class="img-fluid rounded mb-3" alt="{{ $note->title }}">
    @elseif($note->type === 'voice')
    <audio controls class="w-100 mb-3">
      <source src="{{ $note->content }}" type="audio/mpeg">
    </audio>
    @else
    <div class="mb-3">
      {!! $note->content !!}
    </div>
    @endif

    <div class="d-flex flex-wrap gap-1 mb-3">
      @foreach($note->tags as $tag)
      <span class="tag-badge">{{ $tag->name }}</span>
      @endforeach
    </div>

    <small class="text-muted">
      Dibuat: {{ $note->created_at->translatedFormat('d M Y, H:i') }}
      @if($note->updated_at->ne($note->created_at))
      | Diperbarui: {{ $note->updated_at->translatedFormat('d M Y, H:i') }}
      @endif
    </small>
  </div>
</div>
@endsection