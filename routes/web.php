<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\NotesController;

Route::middleware(['auth:sanctum'])->prefix('apps')->name('apps.')->group(function () {
  Route::get('notes/{any?}', [NotesController::class, 'index'])->where('any', '.*');
});