<?php

namespace Modules\Notes\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notes\Services\NoteService;

class DailyController extends Controller
{
  public function index(Request $request, NoteService $noteService) {
    $date = $request->get('date', now()->format('Y-m-d'));
    $userId = $request->telegram_id;
    $notes = $noteService->listNotes($userId, ['date' => $date, 'per_page' => 50]);

    return view('notes::web.daily', compact('notes', 'date'));
  }
}