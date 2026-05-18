<?php

namespace Modules\Notes\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notes\Services\AIService;
use Modules\Notes\Repositories\NoteRepository;
use Modules\Notes\Http\Resources\NoteResource;

class AIController extends Controller
{
  public function __construct(
    protected AIService $aiService,
    protected NoteRepository $noteRepository
  ) {}

  /**
  * Pencarian semantik.
  */
  public function search(Request $request) {
    if (!$this->aiService->isEnabled()) {
      return response()->json(['message' => 'Fitur AI tidak tersedia']);
    }

    $request->validate(['query' => 'required|string|min:3']);

    $user = $request->user();
    // Ambil semua catatan user (batasi 50 terbaru agar tidak terlalu berat)
    $notes = $this->noteRepository->getUserNotes($user->id, ['per_page' => 50]);
    $notesArray = $notes->items();

    // Format untuk AI
    $formatted = array_map(fn($n) => [
      'id' => $n->id,
      'title' => $n->title,
      'content' => $n->content,
    ], $notesArray);

    $ids = $this->aiService->semanticSearch($formatted, $request->query('query'));

    // Ambil catatan yang sesuai
    $results = array_filter($notesArray, fn($n) => in_array($n->id, $ids));

    return NoteResource::collection(collect($results));
  }

  /**
  * Merangkum catatan.
  */
  public function summarize(Request $request, int $id) {
    if (!$this->aiService->isEnabled()) {
      return response()->json(['message' => 'Fitur AI tidak tersedia']);
    }

    $user = $request->user();
    $note = $this->noteRepository->findForUser($id, $user->id);
    if (!$note) abort(404, 'Catatan tidak ditemukan');

    $summary = $this->aiService->summarize($note->content, $note->title);

    return response()->json([
      'success' => true,
      'summary' => $summary,
    ]);
  }
}