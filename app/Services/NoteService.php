<?php

namespace Modules\Notes\Services;

use Modules\Notes\Repositories\NoteRepository;
use Modules\Notes\Repositories\ReminderRepository;
use Modules\Notes\Enums\NoteType;
use Modules\Notes\Models\Note;
use Modules\Notes\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NoteService
{
  public function __construct(
    protected NoteRepository $noteRepository,
    protected ReminderRepository $reminderRepository
  ) {}

  public function listNotes(int $telegramUserId, array $filters) {
    return $this->noteRepository->getUserNotes($telegramUserId, $filters);
  }

  public function getNote(int $id, int $telegramUserId): Note
  {
    $note = $this->noteRepository->findForUser($id, $telegramUserId);
    if (!$note) {
      throw new ModelNotFoundException('Catatan tidak ditemukan.');
    }
    return $note;
  }

  public function createNote(int $telegramUserId, array $data): Note
  {
    $data['telegram_user_id'] = $telegramUserId;

    $tags = $this->parseTags($data['tags'] ?? []);
    unset($data['tags']);

    $reminderAt = $data['reminder_at'] ?? null;
    unset($data['reminder_at']);

    $data['content'] = $this->sanitizeContent($data['content'] ?? '', $data['type'] ?? NoteType::Text->value);

    $note = $this->noteRepository->create($data);

    $this->syncTags($note, $tags);

    if ($reminderAt) {
      $note->reminder()->create(['remind_at' => $reminderAt]);
    }

    return $note->load('tags', 'reminder');
  }

  public function updateNote(int $id, int $telegramUserId, array $data): Note
  {
    $note = $this->noteRepository->findForUser($id, $telegramUserId);
    if (!$note) {
      throw new ModelNotFoundException('Catatan tidak ditemukan.');
    }

    // Hanya proses tags jika ada dalam request
    $hasTags = array_key_exists('tags', $data);
    if ($hasTags) {
      $tags = $this->parseTags($data['tags']);
    }
    unset($data['tags']);

    $reminderAt = $data['reminder_at'] ?? null;
    unset($data['reminder_at']);

    $type = $data['type'] ?? $note->type;
    $content = $data['content'] ?? $note->content;

    // Jika checklist, lakukan merge item lama & baru
    if ($type === 'checklist') {
      $oldItems = json_decode($note->content, true) ?: [];
      $newItems = json_decode($content, true) ?: [];

      // Buat map dari item lama (text => done)
      $oldMap = [];
      foreach ($oldItems as $old) {
        $text = is_string($old) ? $old : ($old['text'] ?? '');
        $done = is_array($old) ? ($old['done'] ?? false) : false;
        if ($text) $oldMap[$text] = $done;
      }

      // Gabungkan: item baru pertahankan status done jika sebelumnya ada
      $merged = [];
      foreach ($newItems as $item) {
        if (is_string($item)) {
          $text = $item;
          $done = $oldMap[$text] ?? false;
        } else {
          $text = $item['text'] ?? '';
          $done = $item['done'] ?? $oldMap[$text] ?? false;
        }
        if ($text) $merged[] = ['text' => $text,
          'done' => $done];
      }

      $data['content'] = json_encode($merged);
    } else {
      $data['content'] = $this->sanitizeContent($content, $type);
    }

    $note = $this->noteRepository->update($note, $data);

    // Hanya sync tags jika tags dikirim dalam request
    if ($hasTags) {
      $this->syncTags($note, $tags);
    }

    if ($reminderAt) {
      $note->reminder()->updateOrCreate(
        ['note_id' => $note->id],
        ['remind_at' => $reminderAt]
      );
    }

    return $note->load('tags', 'reminder');
  }

  public function deleteNote(int $id, int $telegramUserId): void
  {
    $note = $this->noteRepository->findForUser($id, $telegramUserId);
    if (!$note) {
      throw new ModelNotFoundException('Catatan tidak ditemukan.');
    }
    $this->noteRepository->delete($note);
  }

  protected function syncTags(Note $note, array $tagNames): void
  {
    $tagIds = [];
    foreach ($tagNames as $name) {
      $name = trim($name);
      if (empty($name)) continue;

      $tag = Tag::firstOrCreate(
        [
          'telegram_user_id' => $note->telegram_user_id,
          'name' => $name,
        ],
        ['color' => null]
      );
      $tagIds[] = $tag->id;
    }
    $note->tags()->sync($tagIds);
  }

  private function parseTags($tags): array
  {
    if (is_string($tags)) {
      $decoded = json_decode($tags, true);
      return is_array($decoded) ? $decoded : [];
    }
    return is_array($tags) ? $tags : [];
  }

  private function sanitizeContent(string $content, string $type): string
  {
    if ($type === NoteType::Checklist->value) {
      // Validasi JSON, jangan sanitasi HTML
      $decoded = json_decode($content, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        // Normalisasi setiap item ke {text, done}
        $normalized = array_map(function($item) {
          if (is_string($item)) return ['text' => $item,
            'done' => false];
          return [
            'text' => $item['text'] ?? '',
            'done' => $item['done'] ?? false
          ];
        },
          $decoded);
        return json_encode($normalized);
      }
      return '[]';
    }

    // Tipe text: sanitasi HTML
    $allowedTags = '<p><br><strong><em><u><s><h1><h2><blockquote><ol><ul><li><a><img><code><pre><span>';
    $clean = strip_tags($content,
      $allowedTags);
    $clean = preg_replace('/ on\w+="[^"]*"/i',
      '',
      $clean);
    return $clean;
  }
}