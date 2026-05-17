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
        <div>
        <span>${helpers.escapeHtml(r.note?.title || 'Tanpa Judul')}</span>
        <small class="d-block text-muted">${helpers.formatDateTime(r.remind_at)}</small>
        </div>
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
      if (note.type === 'checklist') {
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
              <span class="${done ? 'text-decoration-line-through text-muted': ''} checklist-toggle">${helpers.escapeHtml(text)}</span>
              </div>
              `;
            }).join('')}
            </div>
            `;
          }
        } catch (e) {
          contentHtml = `<p>${helpers.escapeHtml(note.content)}</p>`;
        }
      } else if (note.type === 'image') {
        contentHtml = `<img src="${helpers.escapeHtml(note.content)}" class="img-fluid rounded" alt="${helpers.escapeHtml(note.title)}">`;
      } else if (note.type === 'voice') {
        contentHtml = `<audio controls class="w-100"><source src="${helpers.escapeHtml(note.content)}" type="audio/mpeg"></audio>`;
      } else {
        contentHtml = note.content || '';
      }

      return `
      <div class="card glass-card text-white border-0">
      <div class="card-body">
      <h5 class="card-title">${helpers.escapeHtml(note.title)}</h5>
      <div class="card-text">${contentHtml}</div>
      ${note.tags?.length ? `
      <div class="d-flex flex-wrap gap-1 mt-3">
      ${note.tags.map(tag => `<span class="badge bg-secondary">${helpers.escapeHtml(tag.name)}</span>`).join('')}
      </div>
      `: ''}
      <div id="ai-summary" class="mt-3"></div>
      <div class="d-flex gap-2 mt-3">
      <a href="#/notes/${note.id}/edit" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Edit</a>
      <button data-delete-note="${note.id}" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Hapus</button>
      ${state.aiEnabled && note.content?.length > 100 ? `<button id="summarize-btn" class="btn btn-outline-warning btn-sm"><i class="bi bi-stars me-1"></i> Ringkas AI</button>`: ''}
      </div>
      <!-- Quick Reminder -->
      <div class="mt-3 pt-3 border-top border-secondary">
      <div class="d-flex align-items-center gap-2">
      <i class="bi bi-bell"></i>
      <small>Pengingat: ${note.reminder ? helpers.formatDateTime(note.reminder.remind_at): 'Belum diatur'}</small>
      <button id="quick-reminder-btn" class="btn btn-sm btn-outline-light ms-auto">${note.reminder ? 'Ubah': 'Tambah'}</button>
      </div>
      <div id="quick-reminder-form" class="mt-2" style="display:none;">
      <input type="datetime-local" id="quick-reminder-input" class="form-control glass-input mb-2" value="${helpers.toLocalInputValue(note.reminder?.remind_at)}">
      <button id="quick-reminder-save" class="btn btn-sm btn-warning">Simpan</button>
      <button id="quick-reminder-cancel" class="btn btn-sm btn-outline-secondary">Batal</button>
      </div>
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

      <!-- Tag yang Sudah Ada -->
      ${state.allTags.length > 0 ? `
      <div class="mt-2">
      <small class="text-muted">Tag yang sudah ada (klik untuk menambah):</small>
      <div class="d-flex flex-wrap gap-1 mt-1">
      ${state.allTags.filter(t => !tagNames.includes(t.name)).map(tag => `
        <span class="badge bg-dark border border-secondary add-existing-tag" data-tag-name="${helpers.escapeHtml(tag.name)}" style="cursor:pointer;">
        ${helpers.escapeHtml(tag.name)}
        </span>
        `).join('')}
      </div>
      </div>
      `: ''}
      </div>

      <div class="mb-3">
      <label class="form-label">Pengingat</label>
      <input type="datetime-local" name="reminder_at" value="${helpers.toLocalInputValue(reminderAt)}" class="form-control glass-input">
      </div>

      <button type="submit" class="btn btn-warning w-100">${isEdit ? 'Update': 'Simpan'}</button>
      </form>
      `;
    },

    reminders() {
      const allReminders = state.reminders || [];
      const now = new Date();

      // Fungsi untuk menentukan prioritas pengurutan
      const getPriority = (r) => {
        if (r.is_completed) return 3; // selesai -> paling bawah
        const remindDate = new Date(r.remind_at);
        if (remindDate > now && !r.notified_at) return 0; // akan datang & belum dinotifikasi -> paling atas
        if (r.notified_at && !r.is_completed) return 1; // sudah dinotifikasi, belum selesai -> tengah
        return 2; // lewat waktu tapi belum selesai
      };

      const sorted = [...allReminders].sort((a, b) => {
        const pA = getPriority(a);
        const pB = getPriority(b);
        if (pA !== pB) return pA - pB;
        return new Date(a.remind_at) - new Date(b.remind_at);
      });

      const renderReminderCard = (r) => {
        const isCompleted = r.is_completed;
        const isNotified = !!r.notified_at;
        const isPast = new Date(r.remind_at) < now;
        const title = helpers.escapeHtml(r.note?.title || 'Tanpa Judul');
        const dateStr = helpers.formatDateTime(r.remind_at);

        let statusBadge = '';
        if (isCompleted) {
          statusBadge = '<span class="badge bg-success ms-2"><i class="bi bi-check-all"></i> Selesai</span>';
        } else if (isNotified) {
          statusBadge = '<span class="badge bg-info ms-2"><i class="bi bi-send-check"></i> Terkirim</span>';
        } else if (isPast) {
          statusBadge = '<span class="badge bg-warning ms-2"><i class="bi bi-exclamation-triangle"></i> Terlewat</span>';
        }

        const completeBtn = !isCompleted ? `
        <button data-complete-reminder="${r.id}" class="btn btn-sm btn-outline-success me-1"><i class="bi bi-check-lg"></i></button>
        `: '';

        const deleteBtn = `
        <button data-delete-reminder="${r.id}" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
        `;

        return `
        <div class="d-flex justify-content-between align-items-center glass-card p-2 rounded mb-2">
        <div>
        <p class="mb-0">
        ${title}
        ${statusBadge}
        </p>
        <small class="text-muted">${dateStr}</small>
        </div>
        <div class="d-flex">
        ${completeBtn}
        ${deleteBtn}
        </div>
        </div>
        `;
      };

      return `
      <h5 class="mb-3">Pengingat</h5>
      ${sorted.length ? sorted.map(renderReminderCard).join(''): `
      <div class="empty-state">
      <i class="bi bi-bell-slash"></i>
      <p class="mb-2">Tidak ada pengingat.</p>
      </div>
      `}
      `;
    },

    daily(dateStr = null) {
      const today = dateStr || helpers.getToday();
      const selectedDate = new Date(today);

      const notesForSelected = state.notes.filter(n => n.note_date === today);

      return `
      <div id="daily-view">
      <div id="daily-calendar" style="max-width: 100%; margin-bottom: 1rem;"></div>
      <div class="mt-3">
      <form id="daily-quick-capture" class="input-group">
      <input type="text" name="title" class="form-control glass-input" placeholder="Tambah catatan untuk hari ini...">
      <button type="submit" class="btn btn-warning"><i class="bi bi-plus-lg"></i></button>
      </form>
      </div>
      <div class="mt-4">
      <h6>${helpers.escapeHtml(selectedDate.toLocaleDateString('id-ID', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
      }))}</h6>
      <div id="daily-notes-list">
      ${notesForSelected.length ? notesForSelected.map(n => `
        <div class="card note-card text-white mb-2">
        <div class="card-body">
        <h6>${helpers.escapeHtml(n.title)}</h6>
        ${n.content ? `<p class="small text-muted">${helpers.stripHtml(n.content).substring(0, 100)}...</p>`: ''}
        <a href="#/notes/${n.id}" class="btn btn-sm btn-outline-light">Buka</a>
        </div>
        </div>
        `).join(''): `<p class="text-muted">Belum ada catatan untuk hari ini.</p>`}
      </div>
      </div>
      </div>
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