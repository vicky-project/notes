// Main.js - Inisialisasi, routing, event delegation (diperbaiki)
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
      } else if (path === '/notes/reminders') {
        const remData = await api.getReminders();
        state.setState('reminders', remData.data || remData);
        html = Page.reminders();
      } else if (path === '/notes/profile') {
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
      } else {
        html = Page.home();
      }

      // Transisi
      const content = document.getElementById('app-content');
      content.classList.add('page-loading');
      setTimeout(() => {
        content.innerHTML = html;
        content.classList.remove('page-loading');
        updateActiveNav(path);
      }, 80);
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
        link.classList.add('active-link', 'text-warning');
      } else {
        link.classList.remove('active-link', 'text-warning');
      }
    });
  }

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
            } catch (err) {
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
          } catch (err) {
            tgApp.showToast(err.message, 'danger');
          }
        }

        if (e.target.id === 'logout-btn') {
          localStorage.removeItem('telegram_token');
          tgApp.showToast('Logout berhasil', 'success');
          if (window.Telegram?.WebApp) {
            window.Telegram.WebApp.close();
          }
        }
      });

    document.body.addEventListener('submit',
      async (e) => {
        e.preventDefault();
        const form = e.target;

        if (form.id === 'quick-capture') {
          const titleInput = form.querySelector('input[name="title"]');
          const title = titleInput.value.trim();
          if (!title) return;
          try {
            const newNote = await api.createNote({
              title
            });
            const noteData = newNote.data || newNote;
            state.setState('notes', [noteData, ...state.notes]);
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

    window.addEventListener('hashchange', () => {
      navigateTo(window.location.hash);
    });

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