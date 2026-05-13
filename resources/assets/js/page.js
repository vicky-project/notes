// Page.js - Render setiap halaman
(function(window) {
  'use strict';

  const {
    state, helpers
  } = window.Core;

  const Page = {
    home() {
      const recent = state.notes.slice(0, 5);
      return `
      <div class="mb-4">
      <form id="quick-capture" class="input-group">
      <input type="text" name="title" class="form-control bg-dark text-white border-secondary" placeholder="Tulis ide cepat...">
      <button type="submit" class="btn btn-warning"><i class="bi bi-plus-lg"></i></button>
      </form>
      </div>
      <h6 class="text-uppercase text-muted small mb-2">🔍 Relevan untukmu</h6>
      <div class="row g-2 mb-4">
      ${recent.map(note => `
        <div class="col-6">
        <a href="#/notes/${note.id}" class="card bg-dark text-white text-decoration-none h-100 border-secondary">
        <div class="card-body p-2">
        <h6 class="card-title small">${helpers.escapeHtml(note.title)}</h6>
        ${note.content ? `<p class="card-text text-muted small">${helpers.escapeHtml(note.content.substring(0, 60))}...</p>`: ''}
        </div>
        </a>
        </div>
        `).join('')}
      </div>
      <h6 class="text-uppercase text-muted small mb-2">⏰ Hari Ini</h6>
      ${state.reminders.length ? state.reminders.map(r => `
        <div class="d-flex justify-content-between align-items-center bg-dark p-2 rounded border border-secondary mb-2">
        <span>${helpers.escapeHtml(r.note?.title || 'Tanpa Judul')}</span>
        <button data-complete-reminder="${r.id}" class="btn btn-sm btn-outline-success"><i class="bi bi-check"></i></button>
        </div>
        `).join(''): '<p class="text-muted small">Tidak ada pengingat.</p>'}
      `;
    },

    notesList() {
      return `
      <div class="mb-4">
      <input type="text" id="search-notes" class="form-control bg-dark text-white border-secondary" placeholder="Cari catatan...">
      </div>
      <div id="notes-container" class="row g-2">
      ${state.notes.map(note => `
        <div class="col-12">
        <a href="#/notes/${note.id}" class="card bg-dark text-white text-decoration-none border-secondary">
        <div class="card-body">
        <h6 class="card-title">${helpers.escapeHtml(note.title)}</h6>
        <div class="d-flex flex-wrap gap-1 mt-2">
        ${(note.tags || []).map(tag => `<span class="badge bg-secondary">${helpers.escapeHtml(tag.name)}</span>`).join('')}
        </div>
        </div>
        </a>
        </div>
        `).join('')}
      </div>
      <a href="#/notes/create" class="btn btn-warning rounded-circle position-fixed bottom-0 end-0 m-3 shadow-lg" style="width: 56px; height: 56px; z-index: 1030;">
      <i class="bi bi-plus-lg fs-4"></i>
      </a>
      `;
    },

    noteDetail() {
      const note = state.currentNote;
      if (!note) return `<p class="text-center text-muted">Catatan tidak ditemukan.</p>`;
      return `
      <div class="card bg-dark text-white border-secondary">
      <div class="card-body">
      <h5 class="card-title">${helpers.escapeHtml(note.title)}</h5>
      <p class="card-text">${helpers.escapeHtml(note.content || '')}</p>
      <div class="d-flex gap-2 mt-3">
      <a href="#/notes/${note.id}/edit" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
      <button data-delete-note="${note.id}" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
      </div>
      </div>
      </div>
      `;
    },

    noteForm(mode = 'create', note = {}) {
      const isEdit = mode === 'edit';
      return `
      <form id="note-form" class="card bg-dark text-white border-secondary p-3">
      <div class="mb-3">
      <label class="form-label">Judul</label>
      <input type="text" name="title" value="${helpers.escapeHtml(note.title || '')}" class="form-control bg-dark text-white border-secondary" required>
      </div>
      <div class="mb-3">
      <label class="form-label">Isi</label>
      <textarea name="content" rows="5" class="form-control bg-dark text-white border-secondary">${helpers.escapeHtml(note.content || '')}</textarea>
      </div>
      <div class="mb-3">
      <label class="form-label">Tag (pisahkan koma)</label>
      <input type="text" name="tags" value="${(note.tags || []).map(t => t.name).join(', ')}" class="form-control bg-dark text-white border-secondary">
      </div>
      <button type="submit" class="btn btn-warning">${isEdit ? 'Update': 'Simpan'}</button>
      </form>
      `;
    },

    reminders() {
      return `
      <h5 class="mb-3">Pengingat</h5>
      ${state.reminders.length ? state.reminders.map(r => `
        <div class="d-flex justify-content-between align-items-center bg-dark p-2 rounded border border-secondary mb-2">
        <div>
        <p class="mb-0">${helpers.escapeHtml(r.note?.title || 'Tanpa Judul')}</p>
        <small class="text-muted">${helpers.formatDate(r.remind_at)}</small>
        </div>
        <button data-complete-reminder="${r.id}" class="btn btn-sm btn-outline-success"><i class="bi bi-check"></i></button>
        </div>
        `).join(''): '<p class="text-muted">Tidak ada pengingat.</p>'}
      `;
    },

    profile() {
      return `
      <h5 class="mb-3">Profil</h5>
      <div class="card bg-dark text-white border-secondary">
      <div class="card-body">
      <p>ID: ${state.user?.id || '-'}</p>
      <p>Nama: ${state.user?.first_name || ''} ${state.user?.last_name || ''}</p>
      </div>
      </div>
      <button id="logout-btn" class="btn btn-danger mt-3 w-100"><i class="bi bi-box-arrow-right"></i> Logout</button>
      `;
    }
  };

  window.Page = Page;

})(window);