// Main.js - Inisialisasi, routing, event delegation, Quill, Checklist, Pagination, Trash (fix sync)
(function(window) {
  'use strict';

  if (!window.Core || !window.Page) {
    console.error('Core atau Page tidak ditemukan.');
    return;
  }

  const {
    state,
    api,
    helpers,
    tgApp
  } = window.Core;
  const Page = window.Page;

  let quill = null;

  function extractData(r) {
    return r?.data || r;
  }
  function parsePath(hash) {
    const path = hash.replace('#', '') || '/notes/home';
    const parts = path.split('/').filter(Boolean);
    return {
      full: '/' + parts.join('/'),
      parts,
      isEdit: path.endsWith('/edit')
    };
  }

  // Quill
  function destroyQuill() {
    if (quill) {
      const toolbar = document.querySelector('.ql-toolbar');
      if (toolbar) toolbar.remove();
      const container = document.getElementById('editor-container');
      if (container) container.innerHTML = '';
      quill = null;
    }
  }
  function initQuill(initialContent = '') {
    destroyQuill();
    const container = document.getElementById('editor-container');
    if (!container) return;
    quill = new Quill('#editor-container', {
      theme: 'snow',
      placeholder: 'Tulis isi catatan di sini...',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline', 'strike'],
          ['blockquote', 'code-block'],
          [{
            'list': 'ordered'
          }, {
            'list': 'bullet'
          }],
          [{
            'header': [1, 2, false]}],
          ['link'],
          ['clean']
        ]
      }
    });
    if (initialContent) quill.root.innerHTML = initialContent;
    quill.on('text-change', () => {
      const hidden = document.querySelector('input[name="content"][type="hidden"]');
      if (hidden) hidden.value = quill.root.innerHTML;
    });
  }

  // Checklist
  function getChecklistItems() {
    const c = document.getElementById('checklist-items');
    return c ? [...c.querySelectorAll('.checklist-item-text')].map(e => e.textContent.trim()): [];
  }
  function addChecklistItem(text) {
    const c = document.getElementById('checklist-items');
    if (!c || !text) return;
    const d = document.createElement('div');
    d.className = 'd-flex align-items-center glass-card p-2 rounded mb-2';
    d.innerHTML = `<span class="checklist-item-text flex-grow-1">${helpers.escapeHtml(text)}</span>
    <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-checklist-item">&times;</button>`;
    c.appendChild(d);
    updateChecklistHidden();
  }
  function removeChecklistItem(btn) {
    btn.closest('.d-flex').remove(); updateChecklistHidden();
  }
  function updateChecklistHidden() {
    const h = document.querySelector('input[name="content"][type="hidden"]');
    if (h) h.value = JSON.stringify(getChecklistItems());
  }

  // Tag
  function getTagsFromHidden() {
    const h = document.querySelector('input[name="tags"][type="hidden"]');
    if (!h) return [];
    try {
      return JSON.parse(h.value) || [];
    } catch {
      return [];
    }
  }
  function setTagsToHidden(tags) {
    const h = document.querySelector('input[name="tags"][type="hidden"]'); if (h) h.value = JSON.stringify(tags);
  }
  function renderTagChips() {
    const chips = document.getElementById('tag-chips');
    if (!chips) return;
    const tags = getTagsFromHidden();
    chips.innerHTML = tags.map(name => `
      <span class="badge bg-secondary d-flex align-items-center">
      ${helpers.escapeHtml(name)}
      <button type="button" class="btn-close btn-close-white ms-1" style="font-size:0.5rem;" data-remove-tag="${helpers.escapeHtml(name)}"></button>
      </span>
      `).join('');
  }
  function addTag(name) {
    const tags = getTagsFromHidden();
    if (!tags.includes(name)) {
      tags.push(name); setTagsToHidden(tags); renderTagChips();
    }
  }
  function removeTag(name) {
    let tags = getTagsFromHidden().filter(t => t !== name);
    setTagsToHidden(tags); renderTagChips();
  }
  function commitPendingTag() {
    const input = document.getElementById('tag-input');
    if (!input) return;
    const value = input.value.trim().replace(/,/g, '').trim();
    if (value) {
      addTag(value); input.value = '';
    }
  }

  // Form type
  function initFormByType(type) {
    const qw = document.getElementById('quill-wrapper');
    const cw = document.getElementById('checklist-wrapper');
    if (!qw || !cw) return;
    if (type === 'checklist') {
      qw.style.display = 'none'; cw.style.display = '';
      destroyQuill();
      let items = [];
      if (state.currentNote?.content) {
        try {
          items = JSON.parse(state.currentNote.content); if (!Array.isArray(items)) items = [];
        } catch {}
      }
      const container = document.getElementById('checklist-items');
      if (container) {
        container.innerHTML = '';
        items.forEach(item => addChecklistItem(typeof item === 'string' ? item: (item.text || '')));
      }
      updateChecklistHidden();
    } else {
      qw.style.display = ''; cw.style.display = 'none';
      initQuill(state.currentNote?.content || '');
    }
  }

  // Pagination
  async function loadNotesPage(page) {
    tgApp.showLoading('Memuat...');
    try {
      const keyword = state.searchKeyword || '';
      const data = await api.getNotes({
        page, search: keyword
      });
      state.setState('notes', extractData(data));
      state.setState('pagination', {
        currentPage: data.current_page || page,
        lastPage: data.last_page || 1
      });
      const listContainer = document.getElementById('notes-list-container');
      if (listContainer) listContainer.innerHTML = Page.renderNotesGrid(state.notes);
      renderNotesPagination();
      window.scrollTo({
        top: 0, behavior: 'smooth'
      });
    } catch(err) {
      tgApp.showToast(err.message, 'danger');
    }
    finally {
      tgApp.hideLoading();
    }
  }
  function renderNotesPagination() {
    const {
      currentPage,
      lastPage
    } = state.pagination;
    tgApp.renderPagination('pagination-container', currentPage, lastPage, page => loadNotesPage(page), false);
  }

  // Routing
  async function renderRoute(p) {
    let html = '';
    if (p.full === '/notes/home') {
      if (state.notes.length === 0) {
        const d = await api.getNotes({
          per_page: 5
        });
        state.setState('notes', extractData(d));
      }
      if (state.reminders.length === 0) {
        const d = await api.getReminders();
        state.setState('reminders', extractData(d));
      }
      html = Page.home();
    } else if (p.full === '/notes/all') {
      const keyword = state.searchKeyword || '';
      const d = await api.getNotes({
        page: 1, search: keyword
      });
      state.setState('notes', extractData(d));
      state.setState('pagination', {
        currentPage: d.current_page || 1, lastPage: d.last_page || 1
      });
      html = Page.notesList();
    } else if (p.full === '/notes/create') {
      state.setState('currentNote', null);
      html = Page.noteForm();
    } else if (p.full === '/notes/reminders') {
      const d = await api.getReminders();
      state.setState('reminders', extractData(d));
      html = Page.reminders();
    } else if (p.full === '/notes/profile') {
      if (!state.user || !state.user.id) {
        try {
          const profile = await api.getProfile();
          state.setState('user', profile);
        } catch(err) {
          const tgUser = window.Telegram?.WebApp?.initDataUnsafe?.user;
          if (tgUser) state.setState('user', tgUser);
        }
      }
      html = Page.profile();
    }
  } else if (full === '/notes/trash') {
    const data = await api.getTrashedNotes();
    state.setState('trashedNotes', data.data || data);
    html = Page.trash();
  } else if (p.isEdit && p.parts.length >= 2) {
    const id = p.parts[1];
    const d = await api.getNote(id);
    state.setState('currentNote', extractData(d));
    html = Page.noteForm('edit', state.currentNote);
  } else if (p.parts.length >= 2) {
    const id = p.parts[1];
    const d = await api.getNote(id);
    state.setState('currentNote', extractData(d));
    html = Page.noteDetail();
  } else {
    html = Page.home();
  }
  return html;
}

  async function navigateTo(hash) {
    const parsed = parsePath(hash);
    state.setState('activeRoute', parsed.full);
    tgApp.showLoading('Memuat...');
    try {
      const html = await renderRoute(parsed);
      const content = document.getElementById('app-content');
      content.classList.add('page-loading');
      setTimeout(() => {
        content.innerHTML = html;
        content.classList.remove('page-loading');
        updateActiveNav(parsed.full);
        if (parsed.full === '/notes/create' || parsed.isEdit) {
          renderTagChips();
          const typeSelect = document.getElementById('note-type-select');
          const currentType = typeSelect?.value || 'text';
          initFormByType(currentType);
        } else if (parsed.full === '/notes/all') {
          renderNotesPagination();
        } else {
          destroyQuill();
        }
      },
        80);
    } catch(err) {
      tgApp.showToast(err.message,
        'danger');
    }
    finally {
      tgApp.hideLoading();
    }
  }

  function updateActiveNav(path) {
    document.querySelectorAll('.nav-link').forEach(link => {
      const route = link.dataset.route;
      if (path.startsWith(route)) link.classList.add('active-link', 'text-warning');
      else link.classList.remove('active-link', 'text-warning');
    });
  }

  // Events
  function setupGlobalEvents() {
    document.body.addEventListener('click',
      async (e) => {
        const anchor = e.target.closest('a');
        if (anchor && anchor.getAttribute('href')?.startsWith('#')) {
          e.preventDefault();
          window.location.hash = anchor.getAttribute('href');
          return;
        }
        const deleteBtn = e.target.closest('[data-delete-note]');
        if (deleteBtn) {
          const id = deleteBtn.dataset.deleteNote;
          if (confirm('Hapus catatan ini?')) {
            try {
              await api.deleteNote(id);
              tgApp.showToast('Catatan dihapus', 'success');
              window.location.hash = '#/notes/all';
            } catch(err) {
              tgApp.showToast(err.message, 'danger');
            }
          }
        }
        const completeBtn = e.target.closest('[data-complete-reminder]');
        if (completeBtn) {
          const id = completeBtn.dataset.completeReminder;
          try {
            await api.completeReminder(id);
            tgApp.showToast('Pengingat selesai', 'success');
            navigateTo(window.location.hash);
          } catch(err) {
            tgApp.showToast(err.message, 'danger');
          }
        }
        const removeBtn = e.target.closest('[data-remove-tag]');
        if (removeBtn) {
          e.preventDefault();
          removeTag(removeBtn.dataset.removeTag);
        }
        if (e.target.id === 'add-checklist-btn') {
          e.preventDefault();
          const input = document.getElementById('checklist-input');
          if (input) {
            const text = input.value.trim();
            if (text) {
              addChecklistItem(text); input.value = ''; input.focus();
            }
          }
        }
        if (e.target.classList.contains('remove-checklist-item')) {
          e.preventDefault();
          removeChecklistItem(e.target);
        }

        // Toggle checklist (FIXED)
        const toggleCheck = e.target.closest('.checklist-toggle');
        if (toggleCheck) {
          e.preventDefault();
          const row = toggleCheck.closest('.checklist-item-row');
          if (!row) return;
          const index = parseInt(row.dataset.index, 10);
          const note = state.currentNote;
          if (!note || note.type !== 'checklist') return;
          try {
            const items = JSON.parse(note.content);
            if (index >= 0 && index < items.length) {
              const item = items[index];
              const text = item.text || item;
              const newDone = !item.done;
              items[index] = {
                text,
                done: newDone
              };
              const updatedContent = JSON.stringify(items);

              // Update currentNote
              state.setState('currentNote', {
                ...note, content: updatedContent
              });

              // Update notes array (fix home tidak terupdate)
              const updatedNotes = state.notes.map(n => n.id === note.id ? {
                ...n, content: updatedContent
              }: n);
              state.setState('notes', updatedNotes);

              // DOM update
              const icon = row.querySelector('.checklist-toggle');
              const span = row.querySelector('span');
              if (newDone) {
                icon.classList.remove('bi-square');
                icon.classList.add('bi-check-square-fill', 'text-success');
                span.classList.add('text-decoration-line-through', 'text-muted');
              } else {
                icon.classList.remove('bi-check-square-fill', 'text-success');
                icon.classList.add('bi-square');
                span.classList.remove('text-decoration-line-through', 'text-muted');
              }

              api.updateNote(note.id, {
                content: updatedContent, type: 'checklist'
              })
              .catch(err => tgApp.showToast('Gagal menyimpan: ' + err.message, 'danger'));
            }
          } catch(err) {
            tgApp.showToast('Data checklist rusak', 'danger');
          }
        }

        // Trash
        const restoreBtn = e.target.closest('[data-restore-note]');
        if (restoreBtn) {
          e.preventDefault();
          const id = restoreBtn.dataset.restoreNote;
          try {
            await api.restoreNote(id);
            tgApp.showToast('Catatan dipulihkan', 'success');
            navigateTo(window.location.hash);
          } catch(err) {
            tgApp.showToast(err.message, 'danger');
          }
        }
        const forceDeleteBtn = e.target.closest('[data-force-delete-note]');
        if (forceDeleteBtn) {
          e.preventDefault();
          const id = forceDeleteBtn.dataset.forceDeleteNote;
          if (confirm('Hapus permanen? Tindakan ini tidak dapat dibatalkan.')) {
            try {
              await api.forceDeleteNote(id);
              tgApp.showToast('Catatan dihapus permanen', 'success');
              navigateTo(window.location.hash);
            } catch(err) {
              tgApp.showToast(err.message, 'danger');
            }
          }
        }

        if (e.target.id === 'logout-btn') {
          localStorage.removeItem('telegram_token');
          tgApp.showToast('Logout berhasil', 'success');
          if (window.Telegram?.WebApp) window.Telegram.WebApp.close();
        }
      });

    document.body.addEventListener('change',
      e => {
        if (e.target.id === 'note-type-select') initFormByType(e.target.value);
      });

    document.body.addEventListener('submit',
      async (e) => {
        e.preventDefault();
        const form = e.target;
        if (form.id === 'quick-capture') {
          const titleInput = form.querySelector('input[name="title"]');
          const title = titleInput?.value.trim();
          if (!title) return;
          try {
            const newNote = await api.createNote({
              title
            });
            state.setState('notes', [extractData(newNote), ...state.notes]);
            form.reset();
            tgApp.showToast('Catatan tersimpan', 'success');
            navigateTo('#/notes/home');
          } catch(err) {
            tgApp.showToast(err.message, 'danger');
          }
        }
        if (form.id === 'note-form') {
          const submitBtn = form.querySelector('button[type="submit"]');
          const originalText = submitBtn.innerHTML;
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
          tgApp.showLoading('Menyimpan catatan...');

          commitPendingTag();
          let contentValue = quill ? quill.root.innerHTML: JSON.stringify(getChecklistItems());
          const tagsArray = getTagsFromHidden();
          const data = {
            title: form.querySelector('input[name="title"]').value.trim(),
            type: form.querySelector('select[name="type"]').value,
            content: contentValue,
            tags: tagsArray,
            reminder_at: form.querySelector('input[name="reminder_at"]').value || null
          };

          try {
            if (state.currentNote?.id) {
              await api.updateNote(state.currentNote.id, data);
              tgApp.showToast('Catatan diperbarui', 'success');
            } else {
              await api.createNote(data);
              tgApp.showToast('Catatan dibuat', 'success');
            }
            window.location.hash = '#/notes/all';
          } catch(err) {
            tgApp.showToast(err.message, 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
          } finally {
            tgApp.hideLoading();
          }
        }
      });

    const debouncedSearch = helpers.debounce(async (keyword) => {
      state.setState('searchKeyword', keyword);
      try {
        const data = await api.getNotes({
          page: 1, search: keyword
        });
        state.setState('notes', extractData(data));
        state.setState('pagination', {
          currentPage: data.current_page || 1, lastPage: data.last_page || 1
        });
        const listContainer = document.getElementById('notes-list-container');
        if (listContainer) listContainer.innerHTML = Page.renderNotesGrid(state.notes);
        renderNotesPagination();
      } catch(err) {
        tgApp.showToast(err.message, 'danger');
      }
    },
      300);

    document.body.addEventListener('input',
      e => {
        if (e.target.id === 'search-notes') debouncedSearch(e.target.value);
      });

    document.body.addEventListener('keydown',
      e => {
        if (e.target.id === 'tag-input' && (e.key === ',' || e.key === 'Enter')) {
          e.preventDefault();
          commitPendingTag();
        }
      });
  }

  async function init() {
    try {
      const profile = await api.getProfile();
      state.setState('user',
        profile);
    } catch(err) {
      const tgUser = window.Telegram?.WebApp?.initDataUnsafe?.user;
      if (tgUser) state.setState('user', {
        id: tgUser.id, first_name: tgUser.first_name, last_name: tgUser.last_name,
        username: tgUser.username || null, photo_url: tgUser.photo_url || null
      });
    }

    window.addEventListener('hashchange', () => navigateTo(window.location.hash));
    if (!window.location.hash) window.location.hash = '#/notes/home';
    else await navigateTo(window.location.hash);
    setupGlobalEvents();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})(window);