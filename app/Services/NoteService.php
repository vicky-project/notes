<?php

namespace Modules\Notes\Services;

use Modules\Notes\Repositories\NoteRepository;
use Modules\Notes\Repositories\ReminderRepository;
use Modules\Notes\Enums\NoteType;
use Modules\Notes\Models\Note;
use Modules\Notes\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;

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

  public function getNoteDates(int $telegramUserId) {
    return $this->noteRepository->getUserNoteDates($telegramUserId);
  }

  public function createNote(int $telegramUserId, array $data): Note
  {
    $data['telegram_user_id'] = $telegramUserId;

    if (empty($data['note_date'])) {
      $data['note_date'] = Carbon::today()->format('Y-m-d');
    }

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

    if (array_key_exists('note_date', $data) && empty($data['note_date'])) {
      unset($data['note_date']);
    }

    $hasTags = array_key_exists('tags', $data);
    if ($hasTags) {
      $tags = $this->parseTags($data['tags']);
    }
    unset($data['tags']);

    $reminderAt = $data['reminder_at'] ?? null;
    unset($data['reminder_at']);

    $type = $data['type'] ?? ($note->type instanceof NoteType ? $note->type->value : NoteType::Text->value);
    $content = $data['content'] ?? $note->content;

    if ($type === NoteType::Checklist->value) {
      $oldItems = json_decode($note->content, true) ?: [];
      $newItems = json_decode($content, true) ?: [];

      $oldMap = [];
      foreach ($oldItems as $old) {
        $text = is_string($old) ? $old : ($old['text'] ?? '');
        $done = is_array($old) ? ($old['done'] ?? false) : false;
        if ($text) $oldMap[$text] = $done;
      }

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

  public function deleteReminder(int $reminderId, int $telegramUserId): void
  {
    $reminder = $this->reminderRepository->findForUser($reminderId, $telegramUserId);
    if (!$reminder) {
      throw new ModelNotFoundException('Reminder tidak ditemukan.');
    }
    $this->reminderRepository->delete($reminder);
  }

  // Trash methods
  public function getTrashedNotes(int $telegramUserId) {
    return $this->noteRepository->getTrashedNotes($telegramUserId);
  }

  public function restoreNote(int $id, int $telegramUserId): Note
  {
    $note = $this->noteRepository->findTrashed($id, $telegramUserId);
    if (!$note) {
      throw new ModelNotFoundException('Catatan tidak ditemukan di trash.');
    }
    return $this->noteRepository->restore($note);
  }

  public function forceDeleteNote(int $id, int $telegramUserId): void
  {
    $note = $this->noteRepository->findTrashed($id, $telegramUserId);
    if (!$note) {
      throw new ModelNotFoundException('Catatan tidak ditemukan di trash.');
    }
    $this->noteRepository->forceDelete($note);
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
    switch ($type) {
      case NoteType::Checklist->value:
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
          $normalized = array_map(function ($item) {
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

      case NoteType::Text->value:
        $allowedTags = '<p><br><strong><em><u><s><h1><h2><blockquote><ol><ul><li><a><img><code><pre><span>';
        $clean = strip_tags($content,
          $allowedTags);
        $clean = preg_replace('/ on\w+="[^"]*"/i',
          '',
          $clean);
        return $clean;

      case NoteType::Image->value:
      case NoteType::Voice->value:
        $content = trim($content);
        if (filter_var($content, FILTER_VALIDATE_URL)) {
          return $content;
        }
        return strip_tags($content);

      default:
        return $content;
    }
  }
}