<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\Api\NoteController;
use Modules\Notes\Http\Controllers\Api\ReminderController;
use Modules\Notes\Http\Controllers\Api\ProfileController;

Route::middleware(['auth:sanctum'])->group(function () {
  Route::get('notes/reminders', [ReminderController::class, 'index']);
  Route::patch('notes/reminders/{id}/complete', [ReminderController::class, 'complete']);
  Route::get('notes/profile', [ProfileController::class, 'show']);
  Route::get('notes/trashed', [NoteController::class, 'trashed']);
  Route::patch('notes/{id}/restore', [NoteController::class, 'restore']);
  Route::delete('notes/{id}/force', [NoteController::class, 'forceDelete']);
  Route::apiResource('notes', NoteController::class)->names('notes');
});