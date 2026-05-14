// Page.js - Render setiap halaman menggunakan Core state & helpers
(function(window) {
  'use strict';

  if (!window.Core) {
    console.error('Core tidak ditemukan. Pastikan Core.js dimuat sebelum Page.js');
    return;
  }

  const {
    state,
    helpers
  } = window.Core;

  const Page = {
    home() {
      const recent = state.notes.slice(0, 5);
      const todayReminders = state.reminders.filter(r => {
        if (!r.remind_at) return false;
        const today = new Date();
        const remindDate = new Date(r.remind_at);
        return remindDate.toDateString() === today.toDateString() && !r.is_completed;
      });

      return `
      <div class="mb-4">
      <form id="quick-capture" class="input-group">
      <input type="text" name="title" class="form-control glass-input" placeholder="Tulis ide cepat...">
      <button type="submit" class="btn btn-warning" style="border-radius: 0 12px 12px 0;"><i class="bi bi-plus-lg"></i></button>
      </form>
      </div>

      ${recent.length > 0 ? `
      <h6 class="text-uppercase text-muted small mb-2">🔍 Relevan untukmu</h6>
      <div class="row g-2 mb-4">
      ${recent.map(note => `
        <div class="col-6">
        <a href="#/notes/${note.id}" class="card note-card text-white text-decoration-none h-100">
        <div class="card-body p-2">
        <h6 class="card-title small">${helpers.escapeHtml(note.title)}</h6>
        ${note.content ? `<p class="card-text text-muted small">${helpers.escapeHtml(note.content.substring(0, 60))}...</p>`: ''}
        </div>
        </a>
        </div>
        `).join('')}
      </div>
      `: `
      <div class="empty-state">
      <i class="bi bi-journal-plus"></i>
      <p class="mb-2">Belum ada catatan.</p>
      <a href="#/notes/create" class="btn btn-warning btn-sm">Buat Catatan Pertama</a>
      </div>
      `}

      <h6 class="text-uppercase text-muted small mb-2">⏰ Hari Ini</h6>
      ${todayReminders.length ? todayReminders.map(r => `
        <div class="d-flex justify-content-between align-items-center glass-card p-2 rounded mb-2">
        <span>${helpers.escapeHtml(r.note?.title || 'Tanpa Judul')}</span>
        <button data-complete-reminder="${r.id}" class="btn btn-sm btn-outline-success"><i class="bi bi-check"></i></button>
        </div>
        `).join(''): '<p class="text-muted small">Tidak ada pengingat hari ini.</p>'}
      `;
    },

    notesList() {
      return `
      <div class="mb-4">
      <input type="text" id="search-notes" class="form-control glass-input" placeholder="Cari catatan...">
      </div>
      <div id="notes-container" class="row g-2">
      ${state.notes.length ? state.notes.map(note => `
        <div class="col-12">
        <a href="#/notes/${note.id}" class="card note-card text-white text-decoration-none border-0">
        <div class="card-body">
        <h6 class="card-title">${helpers.escapeHtml(note.title)}</h6>
        <div class="d-flex flex-wrap gap-1 mt-2">
        ${(note.tags || []).map(tag => `<span class="badge bg-secondary">${helpers.escapeHtml(tag.name)}</span>`).join('')}
        </div>
        </div>
        </a>
        </div>
        `).join(''): `
      <div class="empty-state">
      <i class="bi bi-inbox"></i>
      <p class="mb-2">Tidak ada catatan ditemukan.</p>
      <a href="#/notes/create" class="btn btn-warning btn-sm">Buat Baru</a>
      </div>
      `}
      </div>
      `;
    },

    noteDetail() {
      const note = state.currentNote;
      if (!note) return `<div class="empty-state"><i class="bi bi-exclamation-circle"></i><p>Catatan tidak ditemukan.</p></div>`;
      return `
      <div class="card glass-card text-white border-0">
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
      const tags = note.tags || [];
      const tagNames = tags.map(t => t.name);
      const reminderAt = note.reminder ? note.reminder.remind_at: '';

      // Format datetime-local (YYYY-MM-DDTHH:MM)
      const reminderValue = reminderAt
      ? new Date(reminderAt).toISOString().slice(0, 16): '';

      return `
      <form id="note-form" class="card glass-card text-white border-0 p-3 mb-3">
      <div class="mb-3">
      <label class="form-label">Judul</label>
      <input type="text" name="title" value="${helpers.escapeHtml(note.title || '')}" class="form-control glass-input" required>
      </div>

      <div class="mb-3">
      <label class="form-label">Tipe Catatan</label>
      <select name="type" class="form-select glass-input">
      <option value="text" ${note.type === 'text' || !note.type ? 'selected': ''}>📝 Teks</option>
      <option value="checklist" ${note.type === 'checklist' ? 'selected': ''}>✅ Checklist</option>
      </select>
      </div>

      <div class="mb-3">
      <label class="form-label">Isi</label>
      <textarea name="content" rows="6" class="form-control glass-input" placeholder="Tulis isi catatan...">${helpers.escapeHtml(note.content || '')}</textarea>
      </div>

      <div class="mb-3">
      <label class="form-label">Tag</label>
      <div class="input-group">
      <input type="text" id="tag-input" class="form-control glass-input" placeholder="Ketik tag lalu koma atau Enter" autocomplete="off">
      </div>
      <div id="tag-chips" class="d-flex flex-wrap gap-1 mt-2">
      ${tagNames.map(name => `
        <span class="badge bg-secondary d-flex align-items-center" data-tag-name="${helpers.escapeHtml(name)}">
        ${helpers.escapeHtml(name)}
        <button type="button" class="btn-close btn-close-white ms-1" style="font-size: 0.5rem;" data-remove-tag="${helpers.escapeHtml(name)}"></button>
        </span>
        `).join('')}
      </div>
      <!-- Simpan tag sebagai JSON di hidden input -->
      <input type="hidden" name="tags" value='${JSON.stringify(tagNames)}'>
      </div>

      <div class="mb-3">
      <label class="form-label">Pengingat</label>
      <input type="datetime-local" name="reminder_at" value="${reminderValue}" class="form-control glass-input">
      </div>

      <button type="submit" class="btn btn-warning w-100">${isEdit ? 'Update': 'Simpan'}</button>
      </form>
      `;
    },

    reminders() {
      return `
      <h5 class="mb-3">Pengingat</h5>
      ${state.reminders.length ? state.reminders.map(r => `
        <div class="d-flex justify-content-between align-items-center glass-card p-2 rounded mb-2">
        <div>
        <p class="mb-0">${helpers.escapeHtml(r.note?.title || 'Tanpa Judul')}</p>
        <small class="text-muted">${helpers.formatDate(r.remind_at)}</small>
        </div>
        <button data-complete-reminder="${r.id}" class="btn btn-sm btn-outline-success"><i class="bi bi-check"></i></button>
        </div>
        `).join(''): `
      <div class="empty-state">
      <i class="bi bi-bell-slash"></i>
      <p class="mb-2">Tidak ada pengingat.</p>
      </div>
      `}
      `;
    },

    profile() {
      const user = state.user || {};
      const avatar = user.photo_url
      ? `<img src="${helpers.escapeHtml(user.photo_url)}" class="rounded-circle" width="80" height="80" style="object-fit: cover;">`: `<i class="bi bi-person-circle fs-1"></i>`;
      return `
      <div class="text-center mb-4">
      <div class="profile-avatar mx-auto">
      ${avatar}
      </div>
      <h5 class="mt-2">${helpers.escapeHtml(user.first_name || '')} ${helpers.escapeHtml(user.last_name || '')}</h5>
      ${user.username ? `<p class="text-muted">@${helpers.escapeHtml(user.username)}</p>`: ''}
      </div>
      <div class="glass-card p-3">
      <div class="d-flex justify-content-between align-items-center">
      <span>Total Catatan</span>
      <span class="badge bg-warning text-dark">${state.notes.length}</span>
      </div>
      <hr class="text-muted">
      <div class="d-flex justify-content-between align-items-center">
      <span>Pengingat Aktif</span>
      <span class="badge bg-warning text-dark">${state.reminders.filter(r => !r.is_completed).length}</span>
      </div>
      </div>
      <button id="logout-btn" class="btn btn-danger mt-4 w-100"><i class="bi bi-box-arrow-right"></i> Logout</button>
      `;
    }
  };

  window.Page = Page;
})(window);