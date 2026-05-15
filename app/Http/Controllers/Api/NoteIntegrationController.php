<?php

namespace Modules\Notes\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Notes\Enums\NoteType;
use Modules\Notes\Services\NoteIntegrationService;
use Modules\Notes\Http\Resources\NoteResource;

class NoteIntegrationController extends Controller
{
  public function __construct(protected NoteIntegrationService $integrationService) {}

  public function store(Request $request) {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'content' => 'nullable',
      'type' => ['nullable', Rule::enum(NoteType::class)],
      'tags' => 'nullable|array',
      'metadata' => 'nullable|array',
      'source_module' => 'nullable|string',
      'source_id' => 'nullable',
      'reminder_at' => 'nullable|date',
    ]);

    $user = $request->user();
    $note = $this->integrationService->createNote(
      telegramUserId: $user->id,
      payload: $validated
    );

    return response()->json([
      'success' => true,
      'message' => 'Catatan berhasil dibuat',
      'data' => new NoteResource($note),
    ], 201);
  }
}