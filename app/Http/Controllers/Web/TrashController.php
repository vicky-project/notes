<?php

namespace Modules\Notes\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Modules\Notes\Services\NoteService;

class TrashController extends Controller
{
  public function __construct(protected NoteService $noteService) {}

  public function index() {
    $notes = $this->noteService->getTrashedNotes(auth()->id());
    return view('notes::web.trash', compact('notes'));
  }

  public function restore(int $id) {
    $this->noteService->restoreNote($id, auth()->id());
    return back()->with('success', 'Catatan dipulihkan.');
  }

  public function forceDelete(int $id) {
    $this->noteService->forceDeleteNote($id, auth()->id());
    return back()->with('success', 'Catatan dihapus permanen.');
  }
}