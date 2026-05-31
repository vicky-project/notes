<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\NotesController; < ?php

use Modules\Notes\Http\Controllers\Web\NoteController;
use Modules\Notes\Http\Controllers\Web\DailyController;
use Modules\Notes\Http\Controllers\Web\ReminderController;
use Modules\Notes\Http\Controllers\Web\ProfileController;
use Modules\Notes\Http\Controllers\Web\TrashController;

Route::middleware(['web', 'auth'])->prefix('notes')->name('notes.web.')->group(function () {
  // Home / Dashboard
  Route::get('/home', [NoteController::class, 'home'])->name('home');

  // Catatan CRUD
  Route::get('/', [NoteController::class, 'index'])->name('index');
  Route::get('/create', [NoteController::class, 'create'])->name('create');
  Route::post('/', [NoteController::class, 'store'])->name('store');
  Route::get('/{id}', [NoteController::class, 'show'])->name('show');
  Route::get('/{id}/edit', [NoteController::class, 'edit'])->name('edit');
  Route::put('/{id}', [NoteController::class, 'update'])->name('update');
  Route::delete('/{id}', [NoteController::class, 'destroy'])->name('destroy');

  // Daily
  Route::get('/daily', [DailyController::class, 'index'])->name('daily');

  // Reminders
  Route::get('/reminders', [ReminderController::class, 'index'])->name('reminders');
  Route::patch('/reminders/{id}/complete', [ReminderController::class, 'complete'])->name('reminders.complete');
  Route::delete('/reminders/{id}', [ReminderController::class, 'destroy'])->name('reminders.destroy');

  // Profile
  Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

  // Trash
  Route::get('/trash', [TrashController::class, 'index'])->name('trash');
  Route::patch('/trash/{id}/restore', [TrashController::class, 'restore'])->name('trash.restore');
  Route::delete('/trash/{id}/force', [TrashController::class, 'forceDelete'])->name('trash.force-delete');
});

Route::prefix('apps')->name('apps.')->group(function () {
  Route::get('notes/{any?}', [NotesController::class, 'index'])->where('any', '.*');
});