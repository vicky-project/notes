@extends('notes::layouts.web')
@section('title', 'Catatan Harian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="mb-0">Daily</h2>
  <a href="{{ route('notes.web.create', ['date' => $date]) }}" class="btn btn-warning">
    <i class="bi bi-plus-lg"></i> Catatan Baru
  </a>
</div>

<div class="row">
  <div class="col-lg-5 mb-4">
    <div id="daily-calendar" style="max-width: 100%;"></div>
  </div>
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body">
        <h5>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</h5>

        <!-- Quick Capture -->
        <form action="{{ route('notes.web.store') }}" method="POST" class="mb-3">
          @csrf
          <input type="hidden" name="note_date" value="{{ $date }}">
          <input type="hidden" name="type" value="text">
          <div class="input-group">
            <input type="text" name="title" class="form-control" placeholder="Tambah cepat...">
            <button type="submit" class="btn btn-warning"><i class="bi bi-plus-lg"></i></button>
          </div>
        </form>

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
          Belum ada catatan untuk hari ini.
        </p>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/styles/index.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro/index.js" defer></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('daily-calendar');
  if (!container || !window.VanillaCalendarPro) return;

  const { Calendar } = window.VanillaCalendarPro;
  const calendar = new Calendar(container, {
  type: 'default',
  selectedDate: '{{ $date }}',
  firstDayOfWeek: 1,
  settings: {
  visibility: { daysOutsideMonth: true },
  selection: { day: 'single' },
  },
  onClickDate(self, event) {
  const btn = event.target.closest('[data-vc-date-btn]');
  if (btn) {
  const dateDiv = btn.closest('[data-vc-date]');
  if (dateDiv && dateDiv.dataset.vcDate) {
  window.location.href = '{{ route('notes.web.daily') }}?date=' + dateDiv.dataset.vcDate;
  }
  }
  },
  });
  calendar.init();
  });
</script>
@endpush