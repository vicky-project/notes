<?php

namespace Modules\Notes\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Modules\Notes\Models\Note;

class ProfileController extends Controller
{
  public function index() {
    $userId = auth()->id();
    $totalNotes = Note::where('telegram_user_id', $userId)->count();
    $activeReminders = \Modules\Notes\Models\Reminder::whereHas('note', fn($q) => $q->where('telegram_user_id', $userId))
    ->where('is_completed', false)
    ->count();

    return view('notes::web.profile', compact('totalNotes', 'activeReminders'));
  }
}