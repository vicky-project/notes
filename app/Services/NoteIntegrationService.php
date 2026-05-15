<?php

namespace Modules\Notes\Services;

use Modules\Notes\Enums\NoteType;
use Modules\Notes\Models\Note;

class NoteIntegrationService
{
  public function __construct(protected NoteService $noteService) {}

  public function createNote(int $telegramUserId, array $payload): Note
  {
    $data = [
      'title' => $payload['title'] ?? 'Untitled',
      'content' => $this->normalizeContent($payload),
      'type' => $payload['type'] ?? NoteType::Text,
      'tags' => $payload['tags'] ?? [],
      'metadata' => $this->buildMetadata($payload),
      'reminder_at' => $payload['reminder_at'] ?? null,
    ];

    return $this->noteService->createNote($telegramUserId, $data);
  }

  protected function normalizeContent(array $payload): string
  {
    $type = $payload['type'] ?? NoteType::Text;
    $content = $payload['content'] ?? '';

    if ($type === NoteType::Checklist) {
      if (is_array($content)) return json_encode($content);
      $decoded = json_decode($content, true);
      return (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
      ? $content
      : '[]';
    }

    return $content;
  }

  protected function buildMetadata(array $payload): array
  {
    return array_merge($payload['metadata'] ?? [], [
      'source_module' => $payload['source_module'] ?? 'external',
      'source_id' => $payload['source_id'] ?? null,
      'integrated_at' => now()->toIso8601String(),
    ]);
  }
}