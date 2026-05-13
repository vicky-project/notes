<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\Api\NoteController;
use Modules\Notes\Http\Controllers\Api\ReminderController;

Route::middleware(['auth:sanctum'])->group(function () {
  Route::get('notes/reminders', [ReminderController::class, 'index']);
  Route::patch('notes/reminders/{id}/complete', [ReminderController::class, 'complete']);
  Route::apiResource('notes', NoteController::class)->names('notes');
});