<?php
namespace Modules\Notes\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Notes\Http\Requests\StoreNoteRequest;
use Modules\Notes\Http\Requests\UpdateNoteRequest;
use Modules\Notes\Http\Resources\NoteResource;
use Modules\Notes\Services\NoteService;
use Illuminate\Routing\Controller;

class NoteController extends Controller
{
  public function __construct(protected NoteService $noteService) {}

  public function index(Request $request): JsonResponse
  {
    $user = $request->user();
    $notes = $this->noteService->listNotes($user->id, request()->all());
    return NoteResource::collection($notes)->response();
  }

  public function store(StoreNoteRequest $request): JsonResponse
  {
    $user = $request->user();
    $note = $this->noteService->createNote($user->id, $request->validated());
    return (new NoteResource($note))->response()->setStatusCode(201);
  }

  public function show(Request $request, $id): NoteResource
  {
    $user = $request->user();
    // pengecekan manual di service (bisa pakai policy juga)
    $note = $this->noteService->getNote((int) $id, $user->id); // kita tambahkan method
    return new NoteResource($note);
  }

  public function update(UpdateNoteRequest $request, $id): NoteResource
  {
    $user = $request->user();
    $note = $this->noteService->updateNote((int) $id, $user->id, $request->validated());
    return new NoteResource($note);
  }

  public function destroy(Request $request, $id): JsonResponse
  {
    $user = $request->user();
    $this->noteService->deleteNote((int) $id, $user->id);
    return response()->json(['message' => 'Catatan dihapus']);
  }

  /**
  * Menampilkan semua catatan yang dihapus (trash).
  */
  public function trashed(Request $request) {
    $user = $request->user();
    $notes = $this->noteService->getTrashedNotes($user->id);
    return NoteResource::collection($notes);
  }

  /**
  * Memulihkan catatan dari trash.
  */
  public function restore(Request$request, $id) {
    $user = $request->user();
    $note = $this->noteService->restoreNote($id, $user->id);
    return new NoteResource($note);
  }

  /**
  * Menghapus catatan secara permanen.
  */
  public function forceDelete(Request $request, $id) {
    $user = $request->user();
    $this->noteService->forceDeleteNote($id, $user->id);
    return response()->json(['message' => 'Catatan dihapus permanen']);
  }
}