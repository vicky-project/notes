// Page.js - Render setiap halaman (lengkap dengan image & voice)
(function(window) {
  'use strict';

  if (!window.Core) return;

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

      const getSummary = (note) => {
        if (note.type === 'image') return '📷 Gambar';
        if (note.type === 'voice') return '🎤 Suara';
        if (note.type === 'checklist') {
          try {
            const items = JSON.parse(note.content);
            if (Array.isArray(items)) {
              const total = items.length;
              const done = items.filter(i => i.done).length;
              return `Checklist: ${done}/${total} selesai`;
            }
          } catch (e) {}
        }
        const plain = helpers.stripHtml(note.content || '');
        return plain ? helpers.escapeHtml(plain.substring(0, 60)) + '...': '';
      };

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
        ${note.content ? `<p class="card-text text-muted small">${getSummary(note)}</p>`: ''}
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
      const aiButton = state.aiEnabled ? `
      <button id="ai-search-btn" class="btn btn-outline-warning" title="Cari dengan AI">
      <i class="bi bi-stars"></i>
      </button>`: '';

      return `
      <div class="mb-4">
      <div class="input-group">
      <input type="text" id="search-notes" class="form-control glass-input" placeholder="Cari catatan...">
      ${aiButton}
      </div>
      ${state.aiEnabled ? '<small class="text-muted">Coba tanya: "catatan tentang proyek minggu lalu"</small>': ''}
      </div>
      <div id="notes-list-container">
      ${this.renderNotesGrid(state.notes)}
      </div>
      <div id="pagination-container" class="mt-3"></div>
      `;
    },

    renderNotesGrid(notes) {
      if (!notes || notes.length === 0) {
        return `
        <div class="empty-state">
        <i class="bi bi-inbox"></i>
        <p class="mb-2">Tidak ada catatan ditemukan.</p>
        <a href="#/notes/create" class="btn btn-warning btn-sm">Buat Baru</a>
        </div>
        `;
      }
      return `
      <div class="row g-2">
      ${notes.map(note => `
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
        `).join('')}
      </div>
      `;
    },

    noteDetail() {
      const note = state.currentNote;
      if (!note) return `<div class="empty-state">Catatan tidak ditemukan.</div>`;

      let contentHtml = '';
      if (note.type === 'image') {
        contentHtml = `<img src="${helpers.escapeHtml(note.content)}" class="img-fluid rounded" alt="${helpers.escapeHtml(note.title)}" onerror="this.onerror=null;this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22><rect fill=%22%23333%22 width=%22100%22 height=%22100%22/><text x=%2250%22 y=%2255%22 text-anchor=%22middle%22 fill=%22%23888%22 font-size=%2210%22>Gagal memuat</text></svg>';">`;
      } else if (note.type === 'voice') {
        contentHtml = `
        <audio controls class="w-100">
        <source src="${helpers.escapeHtml(note.content)}" type="audio/mpeg">
        Browser Anda tidak mendukung pemutar audio.
        </audio>`;
      } else if (note.type === 'checklist') {
        try {
          const items = JSON.parse(note.content);
          if (Array.isArray(items)) {
            contentHtml = `
            <div class="checklist-readonly">
            ${items.map((item, index) => {
              const text = item.text || item;
              const done = item.done || false;
              return `
              <div class="d-flex align-items-center mb-2 checklist-item-row" data-index="${index}">
              <i class="bi ${done ? 'bi-check-square-fill text-success': 'bi-square'} me-2 checklist-toggle" style="cursor:pointer; font-size: 1.2rem;"></i>
              <span class="${done ? 'text-decoration-line-through text-muted': ''}">${helpers.escapeHtml(text)}</span>
              </div>
              `;
            }).join('')}
            </div>
            `;
          }
        } catch (e) {
          contentHtml = `<p>${helpers.escapeHtml(note.content)}</p>`;
        }
      } else {
        contentHtml = note.content || '';
      }

      const summarizeBtn = state.aiEnabled && note.type === 'text' && note.content?.length > 100 ? `
      <button id="summarize-btn" class="btn btn-outline-warning btn-sm"><i class="bi bi-stars me-1"></i> Ringkas AI</button>
      `: '';

      return `
      <div class="card glass-card text-white border-0">
      <div class="card-body">
      <h5 class="card-title">${helpers.escapeHtml(note.title)}</h5>
      <div class="card-text">${contentHtml}</div>
      <div id="ai-summary" class="mt-3"></div>
      ${note.tags?.length ? `
      <div class="d-flex flex-wrap gap-1 mt-3">
      ${note.tags.map(tag => `<span class="badge bg-secondary">${helpers.escapeHtml(tag.name)}</span>`).join('')}
      </div>
      `: ''}
      <div class="d-flex gap-2 mt-3">
      <a href="#/notes/${note.id}/edit" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
      <button data-delete-note="${note.id}" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
      ${summarizeBtn}
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
      const reminderValue = reminderAt ? new Date(reminderAt).toISOString().slice(0, 16): '';
      const type = note.type || 'text';

      return `
      <form id="note-form" class="card glass-card text-white border-0 p-3">
      <div class="mb-3">
      <label class="form-label">Judul</label>
      <input type="text" name="title" value="${helpers.escapeHtml(note.title || '')}" class="form-control glass-input" required>
      </div>
      <div class="mb-3">
      <label class="form-label">Tipe Catatan</label>
      <select name="type" id="note-type-select" class="form-select glass-input">
      <option value="text" ${type === 'text' ? 'selected': ''}>📝 Teks</option>
      <option value="checklist" ${type === 'checklist' ? 'selected': ''}>✅ Checklist</option>
      <option value="image" ${type === 'image' ? 'selected': ''}>🖼️ Image</option>
      <option value="voice" ${type === 'voice' ? 'selected': ''}>🎤 Voice</option>
      </select>
      </div>

      <!-- Text Editor -->
      <div id="quill-wrapper" class="mb-3" style="${type === 'text' ? '': 'display:none;'}">
      <div class="d-flex justify-content-between align-items-center mb-1">
      <label class="form-label mb-0">Isi</label>
      </div>
      <div id="editor-container" style="border-radius: 12px; overflow: hidden;"></div>
      </div>

      <!-- Checklist Builder -->
      <div id="checklist-wrapper" class="mb-3" style="${type === 'checklist' ? '': 'display:none;'}">
      <label class="form-label">Item Checklist</label>
      <div class="input-group mb-2">
      <input type="text" id="checklist-input" class="form-control glass-input" placeholder="Tambah item...">
      <button type="button" id="add-checklist-btn" class="btn btn-outline-warning"><i class="bi bi-plus"></i></button>
      </div>
      <div id="checklist-items" class="d-flex flex-column gap-1"></div>
      </div>

      <!-- Image Input -->
      <div id="image-wrapper" class="mb-3" style="${type === 'image' ? '': 'display:none;'}">
      <label class="form-label">URL Gambar</label>
      <input type="url" name="content" value="${helpers.escapeHtml(note.content || '')}" class="form-control glass-input" placeholder="https://...">
      </div>

      <!-- Voice Input -->
      <div id="voice-wrapper" class="mb-3" style="${type === 'voice' ? '': 'display:none;'}">
      <label class="form-label">URL Suara</label>
      <input type="url" name="content" value="${helpers.escapeHtml(note.content || '')}" class="form-control glass-input" placeholder="https://...">
      </div>

      <!-- Hidden input untuk text & checklist -->
      <input type="hidden" name="content" value="">

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
      // Hanya tampilkan pengingat yang belum selesai
      const activeReminders = state.reminders.filter(r => !r.is_completed);
      return `
      <h5 class="mb-3">Pengingat</h5>
      ${activeReminders.length ? activeReminders.map(r => `
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
      <div class="profile-avatar mx-auto">${avatar}</div>
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
      <a href="#/notes/trash" class="btn btn-outline-light mt-2 w-100"><i class="bi bi-trash"></i> Trash</a>
      <button id="logout-btn" class="btn btn-danger mt-2 w-100"><i class="bi bi-box-arrow-right"></i> Logout</button>
      `;
    },

    trash() {
      const trashedNotes = state.trashedNotes || [];
      return `
      <h5 class="mb-3">🗑️ Trash</h5>
      ${trashedNotes.length ? trashedNotes.map(note => `
        <div class="card note-card text-white mb-2 border-secondary">
        <div class="card-body d-flex justify-content-between align-items-center">
        <div>
        <h6 class="mb-0">${helpers.escapeHtml(note.title)}</h6>
        <small class="text-muted">Dihapus: ${helpers.formatDate(note.deleted_at)}</small>
        ${note.tags?.length ? `
        <div class="d-flex flex-wrap gap-1 mt-1">
        ${note.tags.map(tag => `<span class="badge bg-secondary">${helpers.escapeHtml(tag.name)}</span>`).join('')}
        </div>
        `: ''}
        </div>
        <div class="d-flex gap-2">
        <button data-restore-note="${note.id}" class="btn btn-sm btn-outline-success" title="Pulihkan">
        <i class="bi bi-arrow-counterclockwise"></i>
        </button>
        <button data-force-delete-note="${note.id}" class="btn btn-sm btn-outline-danger" title="Hapus Permanen">
        <i class="bi bi-trash-fill"></i>
        </button>
        </div>
        </div>
        </div>
        `).join(''): `
      <div class="empty-state">
      <i class="bi bi-trash"></i>
      <p class="mb-2">Trash kosong</p>
      </div>
      `}
      `;
    }
  };

  window.Page = Page;
})(window);