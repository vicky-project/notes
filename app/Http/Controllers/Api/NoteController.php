<?php
namespace Modules\Notes\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Notes\Http\Requests\StoreNoteRequest;
use Modules\Notes\Http\Requests\UpdateNoteRequest;
use Modules\Notes\Http\Resources\NoteResource;
use Modules\Notes\Services\NoteService;
use Modules\Notes\Services\IcsExportService;
use Modules\Telegram\Services\Support\TelegramApi;
use Modules\Telegram\Services\Support\TelegramMarkdownHelper;

class NoteController extends Controller
{
  public function __construct(protected NoteService $noteService) {}

  public function index(Request $request): JsonResponse
  {
    try {
      $user = $request->user();
      $notes = $this->noteService->listNotes($user->id, request()->all());
      return NoteResource::collection($notes)->response();
    } catch(\Exception $e) {
      \Log::error("Error get all notes", [
        'message' => $e->getMessage(),
        'trace' => $e->getTrace()
      ]);

      return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
      ], 500);
    }
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
    try {
      $note = $this->noteService->getNote((int) $id, $user->id);
      return new NoteResource($note);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      abort(404, 'Catatan tidak ditemukan.');
    }
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
    try {
      $notes = $this->noteService->getTrashedNotes($user->id);
      return NoteResource::collection($notes);
    } catch (\Exception $e) {
      \Log::error("Failed to get trashed", [
        'message' => $e->getMessage(),
        'trace' => $e->getTrace()
      ]);
      return NoteResource::collection([]);
    }
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

  public function datesWithNotes(Request $request) {
    $dates = $this->noteService->getNoteDates($request->user()->id);

    return response()->json($dates);
  }

  public function sendIcsToTelegram(Request $request, IcsExportService $icsService, TelegramApi $telegramApi) {
    $user = $request->user();
    $chatId = $user->telegram_id ?? $user->id;

    if (empty($chatId)) {
      return response()->json(['message' => 'Telegram chat ID tidak ditemukan.'], 400);
    }

    try {
      if ($request->has('id')) {
        $note = $this->noteService->getNote($request->id, $user->id);
        $content = $icsService->generateForNote($note);
        $filename = 'catatan-' . $note->id . '.ics';
        $caption = "📅 Catatan: {$note->title}";
      } else {
        $content = $icsService->generateForUser($user->id);
        $filename = 'semua-catatan-' . now()->format('Ymd_His') . '.ics';
        $caption = "📅 Semua catatan Anda dalam format ICS.\nBisa diimpor ke Google Calendar, Apple Calendar, dll.";
      }
    } catch(\RuntimeException $e) {
      return response()->json(['message' => $e->getMessage()], 400);
    }

    $tempPath = Storage::disk('local')->path('temp/' . $filename);
    file_put_contents($tempPath, $content);

    $result = $telegramApi->sendDocument(
      chatId: $chatId,
      filePath: $tempPath,
      caption: TelegramMarkdownHelper::safeText($caption, 'MarkdownV2'),
      parseMode: 'MarkdownV2'
    );

    if (file_exists($tempPath)) {
      unlink($tempPath);
    }

    if ($result) {
      return response()->json(['success' => true, 'message' => 'File ICS dikirim ke Telegram Anda.']);
    } else {
      return response()->json(['message' => 'Gagal mengirim file ke Telegram.'], 500);
    }
  }
}