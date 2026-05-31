@extends('notes::layouts.web')
@section('title', 'Buat Catatan')

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('notes.web.store') }}" method="POST">
          @csrf
          <input type="hidden" name="note_date" value="{{ $defaultDate ?? '' }}">

          <div class="mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="title" class="form-control" required autofocus>
          </div>

          <div class="mb-3">
            <label class="form-label">Tipe</label>
            <select name="type" id="type-select" class="form-select">
              <option value="text">📝 Teks</option>
              <option value="checklist">✅ Checklist</option>
              <option value="image">🖼️ Image</option>
              <option value="voice">🎤 Voice</option>
            </select>
          </div>

          <!-- Text Editor -->
          <div id="text-editor-container" class="mb-3">
            <label class="form-label">Isi</label>
            <div id="editor" style="height: 250px;"></div>
            <input type="hidden" name="content" id="content-input">
          </div>

          <!-- Checklist Builder (sembunyi default) -->
          <div id="checklist-builder" class="mb-3" style="display:none;">
            <label class="form-label">Item Checklist</label>
            <div class="input-group mb-2">
              <input type="text" id="checklist-input" class="form-control" placeholder="Tambah item...">
              <button type="button" id="add-checklist-btn" class="btn btn-outline-warning"><i class="bi bi-plus"></i></button>
            </div>
            <div id="checklist-items"></div>
          </div>

          <!-- Image Input -->
          <div id="image-input" class="mb-3" style="display:none;">
            <label class="form-label">URL Gambar</label>
            <input type="url" name="image_content" class="form-control" placeholder="https://...">
          </div>

          <!-- Voice Input -->
          <div id="voice-input" class="mb-3" style="display:none;">
            <label class="form-label">URL Suara</label>
            <input type="url" name="voice_content" class="form-control" placeholder="https://...">
          </div>

          <div class="mb-3">
            <label class="form-label">Tag</label>
            <div class="d-flex flex-wrap gap-1 mb-2" id="tag-chips"></div>
            <input type="text" id="tag-input" class="form-control" placeholder="Ketik tag lalu Enter atau koma">
            <input type="hidden" name="tags" id="tags-hidden" value="[]">
            <small class="text-muted">Tag yang sudah ada (klik untuk menambah):</small>
            <div class="d-flex flex-wrap gap-1 mt-1" id="available-tags">
              @foreach($allTags as $tag)
              <span class="badge bg-secondary tag-option" data-tag="{{ $tag->name }}" style="cursor:pointer;">{{ $tag->name }}</span>
              @endforeach
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Pengingat</label>
            <input type="datetime-local" name="reminder_at" class="form-control">
          </div>

          <button type="submit" class="btn btn-warning w-100">Simpan</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
  // Quill
  let quill = null;
  if (document.getElementById('editor')) {
  quill = new Quill('#editor', {
  theme: 'snow',
  placeholder: 'Tulis isi catatan...',
  modules: {
  toolbar: [
  ['bold', 'italic', 'underline', 'strike'],
  ['blockquote', 'code-block'],
  [{ 'list': 'ordered' }, { 'list': 'bullet' }],
  [{ 'header': [1, 2, false] }],
  ['link'],
  ['clean']
  ]
  }
  });
  quill.on('text-change', function() {
  document.getElementById('content-input').value = quill.root.innerHTML;
  });
  }

  // Type switcher
  const typeSelect = document.getElementById('type-select');
  const textEditor = document.getElementById('text-editor-container');
  const checklistBuilder = document.getElementById('checklist-builder');
  const imageInput = document.getElementById('image-input');
  const voiceInput = document.getElementById('voice-input');

  function switchType() {
  textEditor.style.display = 'none';
  checklistBuilder.style.display = 'none';
  imageInput.style.display = 'none';
  voiceInput.style.display = 'none';

  if (typeSelect.value === 'text') textEditor.style.display = '';
  else if (typeSelect.value === 'checklist') checklistBuilder.style.display = '';
  else if (typeSelect.value === 'image') imageInput.style.display = '';
  else if (typeSelect.value === 'voice') voiceInput.style.display = '';
  }
  typeSelect.addEventListener('change', switchType);

  // Checklist
  let checklistItems = [];
  document.getElementById('add-checklist-btn')?.addEventListener('click', function() {
  const input = document.getElementById('checklist-input');
  const text = input.value.trim();
  if (!text) return;
  checklistItems.push({ text, done: false });
  input.value = '';
  renderChecklist();
  });

  function renderChecklist() {
  const container = document.getElementById('checklist-items');
  container.innerHTML = checklistItems.map((item, i) => `
  <div class="checklist-item">
  <i class="bi bi-square"></i>
  <span>${item.text}</span>
  <button type="button" class="btn btn-sm btn-outline-danger ms-auto remove-checklist-item" data-index="${i}">&times;</button>
  </div>
  `).join('');

  document.getElementById('content-input').value = JSON.stringify(checklistItems);
  }

  document.getElementById('checklist-items')?.addEventListener('click', function(e) {
  if (e.target.classList.contains('remove-checklist-item')) {
  const index = parseInt(e.target.dataset.index);
  checklistItems.splice(index, 1);
  renderChecklist();
  }
  });

  // Tags
  let selectedTags = [];
  function renderTagChips() {
  const container = document.getElementById('tag-chips');
  container.innerHTML = selectedTags.map(t => `
  <span class="tag-badge d-flex align-items-center">
  ${t}
  <button type="button" class="btn-close ms-1 remove-tag" data-tag="${t}" style="font-size:0.5rem;"></button>
  </span>
  `).join('');
  document.getElementById('tags-hidden').value = JSON.stringify(selectedTags);
  }

  document.getElementById('tag-input')?.addEventListener('keydown', function(e) {
  if (e.key === ',' || e.key === 'Enter') {
  e.preventDefault();
  const value = this.value.trim().replace(/,/g, '').trim();
  if (value && !selectedTags.includes(value)) {
  selectedTags.push(value);
  renderTagChips();
  this.value = '';
  }
  }
  });

  document.getElementById('tag-chips')?.addEventListener('click', function(e) {
  if (e.target.classList.contains('remove-tag')) {
  const tag = e.target.dataset.tag;
  selectedTags = selectedTags.filter(t => t !== tag);
  renderTagChips();
  }
  });

  document.getElementById('available-tags')?.addEventListener('click', function(e) {
  if (e.target.classList.contains('tag-option')) {
  const tag = e.target.dataset.tag;
  if (!selectedTags.includes(tag)) {
  selectedTags.push(tag);
  renderTagChips();
  }
  }
  });
  });
</script>
@endpush