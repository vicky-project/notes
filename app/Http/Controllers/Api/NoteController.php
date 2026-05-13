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

  public function show(Request $request, int $id): NoteResource
  {
    $user = $request->user();
    // pengecekan manual di service (bisa pakai policy juga)
    $note = $this->noteService->getNote($id, $user->id); // kita tambahkan method
    return new NoteResource($note);
  }

  public function update(UpdateNoteRequest $request, int $id): NoteResource
  {
    $user = $request->user();
    $note = $this->noteService->updateNote($id, $user->id, $request->validated());
    return new NoteResource($note);
  }

  public function destroy(Request $request, int $id): JsonResponse
  {
    $user = $request->user();
    $this->noteService->deleteNote($id, $user->id);
    return response()->json(['message' => 'Catatan dihapus']);
  }
}