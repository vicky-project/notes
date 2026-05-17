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
    allTags: [],
    tags: [],
    reminders: [],
    isLoading: false,
    activeRoute: '/notes/home',
    listeners: [],
    activeDate: helpers.getToday(),
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
    },
    async getTags() {
      return tgApp.fetchWithAuth(BASE_URL + '/api/tags');
    },
  };

  const helpers = {
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
      });
    },
    formatDateTime(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('id-ID', {
        day: 'numeric', month: 'short', year: 'numeric'
      }) + ', ' +
      date.toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit'
      });
    },
    toLocalInputValue(utcString) {
      if (!utcString) return '';
      const date = new Date(utcString);
      // Konversi ke waktu lokal untuk input datetime-local
      const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
      return local.toISOString().slice(0, 16);
    },
    toUTCDateTime(localValue) {
      if (!localValue) return null;
      // Input datetime-local dianggap waktu lokal, konversi ke UTC
      return new Date(localValue).toISOString();
    },
    formatDateYMD(date) {
      return date.toISOString().slice(0, 10); // YYYY-MM-DD
    },
    getToday() {
      return this.formatDateYMD(new Date());
    },
    getCalendarDays(year, month) {
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const startDate = new Date(firstDay);
      startDate.setDate(1 - firstDay.getDay()); // mundur ke hari Minggu
      const endDate = new Date(lastDay);
      endDate.setDate(lastDay.getDate() + (6 - lastDay.getDay())); // maju ke Sabtu

      const days = [];
      let current = new Date(startDate);
      while (current <= endDate) {
        days.push(new Date(current));
        current.setDate(current.getDate() + 1);
      }
      return days;
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