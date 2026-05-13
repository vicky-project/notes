<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\Api\NoteController;
use Modules\Notes\Http\Controllers\Api\ReminderController;

Route::middleware(['auth:sanctum'])->prefix('notes')->group(function () {
  Route::apiResource('notes', NoteController::class)->names('notes');
  Route::get('reminders', [ReminderController::class, 'index']);
  Route::patch('reminders/{id}/complete', [ReminderController::class, 'complete']);
});