<?php

namespace Modules\Notes\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notes\Services\NoteService;

class NoteController extends Controller
{
  public function __construct(protected NoteService $noteService) {}

  public function home() {
    $userId = auth()->id();
    $notes = $this->noteService->listNotes($userId, ['per_page' => 5]);
    $reminders = app(\Modules\Notes\Repositories\ReminderRepository::class)->getUserReminders($userId);

    return view('notes::web.home', compact('notes', 'reminders'));
  }

  public function index(Request $request) {
    $userId = auth()->id();
    $notes = $this->noteService->listNotes($userId, [
      'search' => $request->get('search'),
      'tag' => $request->get('tag'),
      'per_page' => 15,
    ]);
    $allTags = \Modules\Notes\Models\Tag::where('telegram_user_id', $userId)->orderBy('name')->get();

    return view('notes::web.notes.index', compact('notes', 'allTags'));
  }

  public function create(Request $request) {
    $allTags = \Modules\Notes\Models\Tag::where('telegram_user_id', auth()->id())->orderBy('name')->get();
    return view('notes::web.notes.create', [
      'allTags' => $allTags,
      'defaultDate' => $request->get('date'),
    ]);
  }

  public function store(Request $request) {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'content' => 'nullable|string',
      'type' => 'nullable|in:text,checklist,image,voice',
      'tags' => 'nullable|array',
      'note_date' => 'nullable|date',
      'reminder_at' => 'nullable|date',
    ]);

    $this->noteService->createNote(auth()->id(), $validated);

    return redirect()->route('notes.web.index')->with('success', 'Catatan berhasil dibuat.');
  }

  public function show(int $id) {
    $note = $this->noteService->getNote($id, auth()->id());
    return view('notes::web.notes.show', compact('note'));
  }

  public function edit(int $id) {
    $note = $this->noteService->getNote($id, auth()->id());
    $allTags = \Modules\Notes\Models\Tag::where('telegram_user_id', auth()->id())->orderBy('name')->get();
    return view('notes::web.notes.edit', compact('note', 'allTags'));
  }

  public function update(Request $request, int $id) {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'content' => 'nullable|string',
      'type' => 'nullable|in:text,checklist,image,voice',
      'tags' => 'nullable|array',
      'note_date' => 'nullable|date',
      'reminder_at' => 'nullable|date',
    ]);

    $this->noteService->updateNote($id, auth()->id(), $validated);

    return redirect()->route('notes.web.show', $id)->with('success', 'Catatan berhasil diperbarui.');
  }

  public function destroy(int $id) {
    $this->noteService->deleteNote($id, auth()->id());
    return redirect()->route('notes.web.index')->with('success', 'Catatan berhasil dihapus.');
  }
}