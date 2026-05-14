<?php

use Illuminate\Support\Facades\Route;
use Modules\Notes\Http\Controllers\NotesController;

Route::prefix('apps')->name('apps.')->group(function () {
  Route::get('notes/{any?}', [NotesController::class, 'index'])->where('any', '.*');
});