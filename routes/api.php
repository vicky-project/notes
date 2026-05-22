<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\Api\AIController;
use Modules\Notes\Http\Controllers\Api\NoteController;
use Modules\Notes\Http\Controllers\Api\NoteIntegrationController;
use Modules\Notes\Http\Controllers\Api\ReminderController;
use Modules\Notes\Http\Controllers\Api\ProfileController;
use Modules\Notes\Http\Controllers\Api\TagController;

Route::middleware(['auth:sanctum'])->group(function () {
  Route::prefix('notes')->group(function() {
    Route::get('reminders', [ReminderController::class, 'index']);
    Route::get('reminders/dates-with-reminders', [ReminderController::class, 'datesWithReminders']);
    Route::patch('reminders/{id}/complete', [ReminderController::class, 'complete']);
    Route::delete('reminders/{id}', [ReminderController::class, 'destroy']);
    Route::get('tags', [TagController::class, 'index']);
    Route::get('profile', [ProfileController::class, 'show']);
    Route::get('dates-with-notes', [NoteController::class, 'datesWithNotes']);
    Route::get('trashed', [NoteController::class, 'trashed']);
    Route::post('send-ics', [NoteController::class, 'sendIcsToTelegram']);
    Route::patch('{id}/restore', [NoteController::class, 'restore']);
    Route::delete('{id}/force', [NoteController::class, 'forceDelete']);
  });

  Route::prefix('ai')->group(function() {
    Route::get('search', [AIController::class, 'search']);
    Route::post('note/{id}/summarize', [AIController::class, 'summarize']);
  });

  Route::prefix('integration')->group(function() {
    Route::post('note', [NoteIntegrationController::class, 'store']);
  });
  Route::apiResource('notes', NoteController::class)->names('notes');
});