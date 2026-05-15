<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\Api\NoteController;
use Modules\Notes\Http\Controllers\Api\NoteIntegrationController;
use Modules\Notes\Http\Controllers\Api\ReminderController;
use Modules\Notes\Http\Controllers\Api\ProfileController;

Route::middleware(['auth:sanctum'])->group(function () {
  Route::prefix('notes')->group(function() {
    Route::get('reminders', [ReminderController::class, 'index']);
    Route::patch('reminders/{id}/complete', [ReminderController::class, 'complete']);
    Route::get('profile', [ProfileController::class, 'show']);
    Route::get('trashed', [NoteController::class, 'trashed']);
    Route::patch('{id}/restore', [NoteController::class, 'restore']);
    Route::delete('{id}/force', [NoteController::class, 'forceDelete']);
  });

  Route::prefix('integration')->group(function() {
    Route::post('note', [NoteIntegrationController::class, 'store']);
  });
  Route::apiResource('notes', NoteController::class)->names('notes');
});