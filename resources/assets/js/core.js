// Core.js - State, API, Helper
(function(window) {
  'use strict';

  const tgApp = window.TelegramApp;
  if (!tgApp) {
    console.error('TelegramApp tidak ditemukan.');
    return;
  }

  const AppState = {
    user: null,
    notes: [],
    currentNote: null,
    tags: [],
    reminders: [],
    isLoading: false,
    activeRoute: '/notes/home',
    listeners: [],
    subscribe(fn) {
      this.listeners.push(fn);
    },
    setState(key, value) {
      this[key] = value; this.notify();
    },
    notify() {
      this.listeners.forEach(fn => fn(this));
    },
    pagination: {
      currentPage: 1,
      lastPage: 1
    },
    trashedNotes: [],
    aiEnabled: window.NotesConfig?.aiEnabled ?? false,
  };

  const api = {
    async getNotes(params = {}) {
      const query = new URLSearchParams(params).toString();
      return tgApp.fetchWithAuth(BASE_URL + `/api/notes?${query}`);
    },
    async getNote(id) {
      return tgApp.fetchWithAuth(BASE_URL + `/api/notes/${id}`);
    },
    async createNote(data) {
      return tgApp.fetchWithAuth(BASE_URL + '/api/notes', {
        method: 'POST',
        body: JSON.stringify(data)
      });
    },
    async updateNote(id, data) {
      return tgApp.fetchWithAuth(BASE_URL + `/api/notes/${id}`, {
        method: 'PUT',
        body: JSON.stringify(data)
      });
    },
    async deleteNote(id) {
      return tgApp.fetchWithAuth(BASE_URL + `/api/notes/${id}`, {
        method: 'DELETE'
      });
    },
    async getReminders() {
      return tgApp.fetchWithAuth(BASE_URL + '/api/notes/reminders');
    },
    async completeReminder(id) {
      return tgApp.fetchWithAuth(BASE_URL + `/api/notes/reminders/${id}/complete`, {
        method: 'PATCH'
      });
    },
    async deleteReminder(id) {
      return tgApp.fetchWithAuth(BASE_URL + `/api/notes/reminders/${id}`, {
        method: 'DELETE'
      });
    },
    async getProfile() {
      return tgApp.fetchWithAuth(BASE_URL + '/api/notes/profile');
    },
    async getTrashedNotes() {
      return tgApp.fetchWithAuth(BASE_URL + '/api/notes/trashed');
    },
    async restoreNote(id) {
      return tgApp.fetchWithAuth(BASE_URL + `/api/notes/${id}/restore`, {
        method: 'PATCH'
      });
    },
    async forceDeleteNote(id) {
      return tgApp.fetchWithAuth(BASE_URL + `/api/notes/${id}/force`, {
        method: 'DELETE'
      });
    },
    async aiSearch(query) {
      return tgApp.fetchWithAuth(BASE_URL + `/api/ai/search?query=${encodeURIComponent(query)}`);
    },
    async summarizeNote(id) {
      return tgApp.fetchWithAuth(BASE_URL + `/api/ai/note/${id}/summarize`, {
        method: 'POST'
      });
    }
  };

  const helpers = {
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
      });
    },
    escapeHtml(str) {
      return tgApp.escapeHtml(str);
    },
    stripHtml(html) {
      return (html || '').replace(/<[^>]*>/g, '');
    },
    debounce(func, delay) {
      let timeout;
      return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
      };
    },
    uid() {
      return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }
  };

  window.Core = {
    state: AppState,
    api,
    helpers,
    tgApp
  };
})(window);