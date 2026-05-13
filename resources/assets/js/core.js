// Core.js - State, API, Helper
(function(window) {
  'use strict';

  const tgApp = window.TelegramApp;
  if (!tgApp) {
    console.error('TelegramApp tidak ditemukan.');
    return;
  }

  // ========== App State ==========
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
      this[key] = value;
      this.notify();
    },
    notify() {
      this.listeners.forEach(fn => fn(this));
    }
  };

  // ========== API Helper ==========
  const api = {
    async getNotes(params = {}) {
      const query = new URLSearchParams(params).toString();
      return tgApp.fetchWithAuth(BASE_URL+ `/api/notes?${query}`);
    },
    async getNote(id) {
      return tgApp.fetchWithAuth(BASE_URL+ `/api/notes/${id}`);
    },
    async createNote(data) {
      return tgApp.fetchWithAuth(BASE_URL+ '/api/notes', {
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
      return tgApp.fetchWithAuth(BASE_URL + '/api/reminders');
    },
    async completeReminder(id) {
      return tgApp.fetchWithAuth(BASE_URL+ `/api/reminders/${id}/complete`, {
        method: 'PATCH'
      });
    }
  };

  // ========== Helpers ==========
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

  // ========== Ekspos ke global ==========
  window.Core = {
    state: AppState,
    api,
    helpers,
    tgApp
  };

})(window);