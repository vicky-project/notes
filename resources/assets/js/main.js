// Main.js - Inisialisasi, routing, event global
(function(window) {
  'use strict';

  const {
    state, api, helpers, tgApp
  } = window.Core;
  const Page = window.Page;

  // ========== Navigasi ==========
  async function navigateTo(hash) {
    const path = hash.replace('#', '') || '/notes/home';
    state.setState('activeRoute', path);

    tgApp.showLoading('Memuat...');

    try {
      let html = '';

      if (path === '/notes/home') {
        if (state.notes.length === 0) {
          const data = await api.getNotes({
            per_page: 5
          });
          state.setState('notes', data.data || data);
        }
        if (state.reminders.length === 0) {
          const remData = await api.getReminders();
          state.setState('reminders', remData.data || remData);
        }
        html = Page.home();
      } else if (path === '/notes/all') {
        const data = await api.getNotes();
        state.setState('notes', data.data || data);
        html = Page.notesList();
      } else if (path === '/notes/create') {
        state.setState('currentNote', null);
        html = Page.noteForm();
      } else if (path.startsWith('/notes/') && path.endsWith('/edit')) {
        const id = path.split('/')[2];
        const data = await api.getNote(id);
        state.setState('currentNote', data.data || data);
        html = Page.noteForm('edit', state.currentNote);
      } else if (path.startsWith('/notes/')) {
        const id = path.split('/')[2];
        const data = await api.getNote(id);
        state.setState('currentNote', data.data || data);
        html = Page.noteDetail();
      } else if (path === '/notes/reminders') {
        const remData = await api.getReminders();
        state.setState('reminders', remData.data || remData);
        html = Page.reminders();
      } else if (path === '/notes/profile') {
        html = Page.profile();
      } else {
        html = Page.home();
      }

      document.getElementById('app-content').innerHTML = html;
      updateActiveNav(path);
    } catch (error) {
      tgApp.showToast(error.message, 'danger');
    } finally {
      tgApp.hideLoading();
    }
  }

  function updateActiveNav(path) {
    document.querySelectorAll('.nav-link').forEach(link => {
      const route = link.dataset.route;
      if (path.startsWith(route)) {
        link.classList.add('text-warning');
      } else {
        link.classList.remove('text-warning');
      }
    });
  }

  // ========== Event Delegation ==========
  function setupGlobalEvents() {
    document.body.addEventListener('click',
      async (e) => {
        // Navigasi internal (hash)
        const anchor = e.target.closest('a');
        if (anchor && anchor.getAttribute('href')?.startsWith('#')) {
          e.preventDefault();
          window.location.hash = anchor.getAttribute('href');
          return;
        }

        // Tombol hapus
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

        // Tombol complete reminder
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

        // Logout
        if (e.target.id === 'logout-btn') {
          localStorage.removeItem('telegram_token');
          tgApp.showToast('Logout berhasil', 'success');
          if (window.Telegram?.WebApp) {
            window.Telegram.WebApp.close();
          }
        }
      });

    // Submit form
    document.body.addEventListener('submit',
      async (e) => {
        e.preventDefault();
        const form = e.target;

        if (form.id === 'quick-capture') {
          const titleInput = form.querySelector('input[name="title"]');
          const title = titleInput.value.trim();
          if (!title) return;
          try {
            await api.createNote({
              title
            });
            form.reset();
            tgApp.showToast('Catatan tersimpan', 'success');
            navigateTo('#/notes/home');
          } catch (err) {
            tgApp.showToast(err.message, 'danger');
          }
        }

        if (form.id === 'note-form') {
          const formData = new FormData(form);
          const data = Object.fromEntries(formData.entries());
          data.tags = data.tags ? data.tags.split(',').map(t => t.trim()): [];

          try {
            if (state.currentNote && state.currentNote.id) {
              await api.updateNote(state.currentNote.id, data);
              tgApp.showToast('Catatan diperbarui', 'success');
            } else {
              await api.createNote(data);
              tgApp.showToast('Catatan dibuat', 'success');
            }
            window.location.hash = '#/notes/all';
          } catch (err) {
            tgApp.showToast(err.message, 'danger');
          }
        }
      });

    // Search dengan debounce
    const debouncedSearch = helpers.debounce(async (keyword) => {
      try {
        const data = await api.getNotes({
          search: keyword
        });
        state.setState('notes', data.data || data);
        document.getElementById('app-content').innerHTML = Page.notesList();
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
  }

  // ========== Inisialisasi Aplikasi ==========
  async function init() {
    // Ambil data user Telegram
    const tgUser = window.Telegram?.WebApp?.initDataUnsafe?.user;
    if (tgUser) {
      state.setState('user', tgUser);
    }

    // Hash change listener
    window.addEventListener('hashchange', () => {
      navigateTo(window.location.hash);
    });

    // Rute awal
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