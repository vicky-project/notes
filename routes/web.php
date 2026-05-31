<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\NotesController;
use Modules\Notes\Http\Controllers\Web\NoteController;
use Modules\Notes\Http\Controllers\Web\DailyController;
use Modules\Notes\Http\Controllers\Web\ReminderController;
use Modules\Notes\Http\Controllers\Web\ProfileController;
use Modules\Notes\Http\Controllers\Web\TrashController;

Route::middleware(['web', 'auth'])->prefix('notes')->name('notes.web.')->group(function () {
  // Halaman khusus (spesifik) – letakkan di atas
  Route::get('/home', [NoteController::class, 'home'])->name('home');
  Route::get('/create', [NoteController::class, 'create'])->name('create');
  Route::post('/', [NoteController::class, 'store'])->name('store');

  Route::get('/daily', [DailyController::class, 'index'])->name('daily');
  Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders');
  Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
  Route::get('/trash', [TrashController::class, 'index'])->name('trash');

  // Rute generik dengan parameter id – setelah semua rute spesifik
  Route::get('/', [NoteController::class, 'index'])->name('index');
  Route::get('/{id}', [NoteController::class, 'show'])
  ->where('id', '[0-9]+')
  ->name('show');
  Route::get('/{id}/edit', [NoteController::class, 'edit'])
  ->where('id', '[0-9]+')
  ->name('edit');
  Route::put('/{id}', [NoteController::class, 'update'])
  ->where('id', '[0-9]+')
  ->name('update');
  Route::delete('/{id}', [NoteController::class, 'destroy'])
  ->where('id', '[0-9]+')
  ->name('destroy');

  // Reminder actions
  Route::patch('/reminders/{id}/complete', [ReminderController::class, 'complete'])
  ->where('id', '[0-9]+')
  ->name('reminders.complete');
  Route::delete('/reminders/{id}', [ReminderController::class, 'destroy'])
  ->where('id', '[0-9]+')
  ->name('reminders.destroy');

  // Trash actions
  Route::patch('/trash/{id}/restore', [TrashController::class, 'restore'])
  ->where('id', '[0-9]+')
  ->name('trash.restore');
  Route::delete('/trash/{id}/force', [TrashController::class, 'forceDelete'])
  ->where('id', '[0-9]+')
  ->name('trash.force-delete');
});

Route::prefix('apps')->name('apps.')->group(function () {
  Route::get('notes/{any?}', [NotesController::class, 'index'])->where('any', '.*');
});