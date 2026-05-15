// Main.js - Inisialisasi, routing, event delegation, Quill, Checklist (final fix menumpuk)
(function(window) {
  'use strict';

  if (!window.Core || !window.Page) {
    console.error('Core atau Page tidak ditemukan. Pastikan urutan pemuatan: Core.js -> Page.js -> Main.js');
    return;
  }

  const {
    state,
    api,
    helpers,
    tgApp
  } = window.Core;
  const Page = window.Page;

  // ========== Quill Instance ==========
  let quill = null;

  // ========== Utility Functions ==========
  function extractData(response) {
    return response?.data || response;
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

  // ========== Quill Management (PERBAIKAN TUMPUK) ==========
  function destroyQuill() {
    if (quill) {
      // Hapus toolbar Quill yang mungkin masih tersisa
      const toolbar = document.querySelector('.ql-toolbar');
      if (toolbar) toolbar.remove();
      // Kosongkan container editor
      const container = document.getElementById('editor-container');
      if (container) {
        container.innerHTML = '';
      }
      quill = null;
    }
  }

  function initQuill(initialContent = '') {
    // Pastikan tidak ada instance sebelumnya
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
            'header': [1, 2, false]
          }],
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

  // ========== Checklist Builder ==========
  function getChecklistItems() {
    const container = document.getElementById('checklist-items');
    if (!container) return [];
    const items = [];
    container.querySelectorAll('.checklist-item-text').forEach(el => items.push(el.textContent.trim()));
    return items;
  }

  function addChecklistItem(text) {
    const container = document.getElementById('checklist-items');
    if (!container || !text) return;
    const itemDiv = document.createElement('div');
    itemDiv.className = 'd-flex align-items-center glass-card p-2 rounded mb-2';
    itemDiv.innerHTML = `
    <span class="checklist-item-text flex-grow-1">${helpers.escapeHtml(text)}</span>
    <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-checklist-item">&times;</button>
    `;
    container.appendChild(itemDiv);
    updateChecklistHidden();
  }

  function removeChecklistItem(button) {
    button.closest('.d-flex').remove();
    updateChecklistHidden();
  }

  function updateChecklistHidden() {
    const hidden = document.querySelector('input[name="content"][type="hidden"]');
    if (hidden) {
      hidden.value = JSON.stringify(getChecklistItems());
    }
  }

  // ========== Tag Chips ==========
  function getTagsFromHidden() {
    const hidden = document.querySelector('input[name="tags"][type="hidden"]');
    if (!hidden) return [];
    try {
      return JSON.parse(hidden.value) || [];
    } catch {
      return [];
    }
  }

  function setTagsToHidden(tags) {
    const hidden = document.querySelector('input[name="tags"][type="hidden"]');
    if (hidden) hidden.value = JSON.stringify(tags);
  }

  function renderTagChips() {
    const chips = document.getElementById('tag-chips');
    if (!chips) return;
    const tags = getTagsFromHidden();
    chips.innerHTML = tags.map(name => `
      <span class="badge bg-secondary d-flex align-items-center">
      ${helpers.escapeHtml(name)}
      <button type="button" class="btn-close btn-close-white ms-1" style="font-size: 0.5rem;" data-remove-tag="${helpers.escapeHtml(name)}"></button>
      </span>
      `).join('');
  }

  function addTag(name) {
    const tags = getTagsFromHidden();
    if (!tags.includes(name)) {
      tags.push(name);
      setTagsToHidden(tags);
      renderTagChips();
    }
  }

  function removeTag(name) {
    let tags = getTagsFromHidden();
    tags = tags.filter(t => t !== name);
    setTagsToHidden(tags);
    renderTagChips();
  }

  function commitPendingTag() {
    const input = document.getElementById('tag-input');
    if (!input) return;
    const value = input.value.trim().replace(/,/g, '').trim();
    if (value) {
      addTag(value);
      input.value = '';
    }
  }

  // ========== Form Type Switcher ==========
  function initFormByType(type) {
    const quillWrapper = document.getElementById('quill-wrapper');
    const checklistWrapper = document.getElementById('checklist-wrapper');
    if (!quillWrapper || !checklistWrapper) return;

    if (type === 'checklist') {
      quillWrapper.style.display = 'none';
      checklistWrapper.style.display = '';
      destroyQuill(); // Hancurkan Quill saat pindah ke checklist

      let items = [];
      if (state.currentNote?.content) {
        try {
          items = JSON.parse(state.currentNote.content);
          if (!Array.isArray(items)) items = [];
        } catch (e) {}
      }
      const container = document.getElementById('checklist-items');
      if (container) {
        container.innerHTML = '';
        items.forEach(item => addChecklistItem(typeof item === 'string' ? item: (item.text || '')));
      }
      updateChecklistHidden();
    } else {
      quillWrapper.style.display = '';
      checklistWrapper.style.display = 'none';
      // Pindah ke teks: initQuill akan memanggil destroyQuill() dulu, lalu inisialisasi baru
      initQuill(state.currentNote?.content || '');
    }
  }

  // ========== Routing & Render ==========
  async function renderRoute(parsed) {
    const {
      full,
      parts,
      isEdit
    } = parsed;
    let html = '';

    if (full === '/notes/home') {
      if (state.notes.length === 0) {
        const data = await api.getNotes({
          per_page: 5
        });
        state.setState('notes', extractData(data));
      }
      if (state.reminders.length === 0) {
        const remData = await api.getReminders();
        state.setState('reminders', extractData(remData));
      }
      html = Page.home();
    } else if (full === '/notes/all') {
      const keyword = state.searchKeyword || '';
      const data = await api.getNotes({
        page: 1,
        search: keyword
      });
      state.setState('notes', extractData(data));
      state.setState('pagination', {
        currentPage: data.current_page || 1,
        lastPage: data.last_page || 1
      });
      html = Page.notesList();
    } else if (full === '/notes/create') {
      state.setState('currentNote', null);
      html = Page.noteForm();
    } else if (full === '/notes/reminders') {
      const remData = await api.getReminders();
      state.setState('reminders', extractData(remData));
      html = Page.reminders();
    } else if (full === '/notes/profile') {
      if (!state.user || !state.user.id) {
        try {
          const profile = await api.getProfile();
          state.setState('user', profile);
        } catch (err) {
          const tgUser = window.Telegram?.WebApp?.initDataUnsafe?.user;
          if (tgUser) state.setState('user', tgUser);
        }
      }
      html = Page.profile();
    } else if (isEdit && parts.length >= 2) {
      const id = parts[1];
      const data = await api.getNote(id);
      state.setState('currentNote', extractData(data));
      html = Page.noteForm('edit', state.currentNote);
    } else if (parts.length >= 2) {
      const id = parts[1];
      const data = await api.getNote(id);
      state.setState('currentNote', extractData(data));
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

        // Inisialisasi form jika di halaman create/edit
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
    } catch (error) {
      tgApp.showToast(error.message,
        'danger');
    } finally {
      tgApp.hideLoading();
    }
  }

  async function loadNotesPage(page) {
    tgApp.showLoading('Memuat...');
    try {
      const data = await api.getNotes({
        page,
        search: state.searchKeyword || ''
      });
      state.setState('notes',
        extractData(data));
      state.setState('pagination',
        {
          currentPage: data.current_page || page,
          lastPage: data.last_page || 1
        });
      // Update daftar catatan
      const listContainer = document.getElementById('notes-list-container');
      if (listContainer) {
        listContainer.innerHTML = Page.renderNotesGrid(state.notes);
      }
      // Update pagination
      renderNotesPagination();
    } catch (error) {
      tgApp.showToast(error.message, 'danger');
    } finally {
      tgApp.hideLoading();
    }
  }

  function renderNotesPagination() {
    const {
      currentPage,
      lastPage
    } = state.pagination;
    // Gunakan fungsi dari TelegramApp
    tgApp.renderPagination(
      'pagination-container',
      // container ID
      currentPage,
      lastPage,
      (page) => loadNotesPage(page),
      // callback
      true // scrollToTop
    );
  }

  function updateActiveNav(path) {
    document.querySelectorAll('.nav-link').forEach(link => {
      const route = link.dataset.route;
      if (path.startsWith(route)) {
        link.classList.add('active-link', 'text-warning');
      } else {
        link.classList.remove('active-link', 'text-warning');
      }
    });
  }

  // ========== Global Event Delegation ==========
  function setupGlobalEvents() {
    document.body.addEventListener('click',
      async (e) => {
        // Navigasi hash
        const anchor = e.target.closest('a');
        if (anchor && anchor.getAttribute('href')?.startsWith('#')) {
          e.preventDefault();
          window.location.hash = anchor.getAttribute('href');
          return;
        }

        // Hapus catatan
        const deleteBtn = e.target.closest('[data-delete-note]');
        if (deleteBtn) {
          const id = deleteBtn.dataset.deleteNote;
          if (confirm('Hapus catatan ini?')) {
            try {
              await api.deleteNote(id);
              tgApp.showToast('Catatan dihapus', 'success');
              window.location.hash = '#/notes/all';
            } catch (err) {
              tgApp.showToast(err.message, 'danger');
            }
          }
        }

        // Selesaikan pengingat
        const completeBtn = e.target.closest('[data-complete-reminder]');
        if (completeBtn) {
          const id = completeBtn.dataset.completeReminder;
          try {
            await api.completeReminder(id);
            tgApp.showToast('Pengingat selesai', 'success');
            navigateTo(window.location.hash);
          } catch (err) {
            tgApp.showToast(err.message, 'danger');
          }
        }

        // Hapus tag
        const removeBtn = e.target.closest('[data-remove-tag]');
        if (removeBtn) {
          e.preventDefault();
          removeTag(removeBtn.dataset.removeTag);
        }

        // Tambah checklist item
        if (e.target.id === 'add-checklist-btn') {
          e.preventDefault();
          const input = document.getElementById('checklist-input');
          if (input) {
            const text = input.value.trim();
            if (text) {
              addChecklistItem(text);
              input.value = '';
              input.focus();
            }
          }
        }

        // Hapus checklist item
        if (e.target.classList.contains('remove-checklist-item')) {
          e.preventDefault();
          removeChecklistItem(e.target);
        }

        // Toggle checklist di detail
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
              state.setState('currentNote', {
                ...note, content: JSON.stringify(items)
              });

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
                content: JSON.stringify(items), type: 'checklist'
              })
              .catch(err => tgApp.showToast('Gagal menyimpan: ' + err.message, 'danger'));
            }
          } catch (err) {
            tgApp.showToast('Data checklist rusak', 'danger');
          }
        }

        // Logout
        if (e.target.id === 'logout-btn') {
          localStorage.removeItem('telegram_token');
          tgApp.showToast('Logout berhasil', 'success');
          if (window.Telegram?.WebApp) {
            window.Telegram.WebApp.close();
          }
        }
      });

    document.body.addEventListener('change',
      (e) => {
        if (e.target.id === 'note-type-select') {
          initFormByType(e.target.value);
        }
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
            const noteData = extractData(newNote);
            state.setState('notes', [noteData, ...state.notes]);
            form.reset();
            tgApp.showToast('Catatan tersimpan', 'success');
            navigateTo('#/notes/home');
          } catch (err) {
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

          let contentValue = '';
          if (quill) {
            contentValue = quill.root.innerHTML;
          } else {
            contentValue = JSON.stringify(getChecklistItems());
          }

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
          } catch (err) {
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
          page: 1,
          search: keyword
        });
        state.setState('notes', extractData(data));
        state.setState('pagination', {
          currentPage: data.current_page || 1,
          lastPage: data.last_page || 1
        });
        const listContainer = document.getElementById('notes-list-container');
        if (listContainer) {
          listContainer.innerHTML = Page.renderNotesGrid(state.notes);
        }
        renderNotesPagination();
      } catch (err) {
        tgApp.showToast(err.message, 'danger');
      }
    },
      300);

    document.body.addEventListener('input',
      (e) => {
        if (e.target.id === 'search-notes') {
          debouncedSearch(e.target.value);
        }
      });

    document.body.addEventListener('keydown',
      (e) => {
        if (e.target.id === 'tag-input') {
          if (e.key === ',' || e.key === 'Enter') {
            e.preventDefault();
            commitPendingTag();
          }
        }
      });
  }

  // ========== Inisialisasi Aplikasi ==========
  async function init() {
    try {
      const profile = await api.getProfile();
      state.setState('user',
        profile);
    } catch (err) {
      const tgUser = window.Telegram?.WebApp?.initDataUnsafe?.user;
      if (tgUser) {
        state.setState('user', {
          id: tgUser.id,
          first_name: tgUser.first_name,
          last_name: tgUser.last_name,
          username: tgUser.username || null,
          photo_url: tgUser.photo_url || null
        });
      }
    }

    window.addEventListener('hashchange', () => navigateTo(window.location.hash));

    if (!window.location.hash) {
      window.location.hash = '#/notes/home';
    } else {
      await navigateTo(window.location.hash);
    }

    setupGlobalEvents();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})(window);