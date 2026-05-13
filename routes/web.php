<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\NotesController;

Route::middleware(['auth:sanctum'])->prefix('notes')->name('notes.')->group(function () {
  Route::get('notes', [NotesController::class, 'index'])->name('index');
});