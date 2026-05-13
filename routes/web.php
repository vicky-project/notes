<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\NotesController;

Route::middleware(['auth:sanctum'])->prefix('notes')->name('notes.')->group(function () {
  Route::get('{any?}', [NotesController::class, 'index'])->where('any', '.*');
});