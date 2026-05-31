<?php

namespace Modules\Notes\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notes\Services\NoteService;

class TrashController extends Controller
{
  public function __construct(protected NoteService $noteService) {}

  public function index(Request $request) {
    $notes = $this->noteService->getTrashedNotes($request->telegram_id);
    return view('notes::web.trash', compact('notes'));
  }

  public function restore(Request $request, int $id) {
    $this->noteService->restoreNote($id, $request->telegram_id);
    return back()->with('success', 'Catatan dipulihkan.');
  }

  public function forceDelete(Request $request, int $id) {
    $this->noteService->forceDeleteNote($id, $request->telegram_id);
    return back()->with('success', 'Catatan dihapus permanen.');
  }
}