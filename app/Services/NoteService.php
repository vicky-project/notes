<?php

namespace Modules\Notes\Services;

use Modules\Notes\Repositories\NoteRepository;
use Modules\Notes\Repositories\ReminderRepository;
use Modules\Notes\Models\Note;
use Modules\Notes\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NoteService
{
  public function __construct(
    protected NoteRepository $noteRepository,
    protected ReminderRepository $reminderRepository
  ) {}

  /**
  * Mendapatkan daftar catatan user dengan filter.
  */
  public function listNotes(int $telegramUserId, array $filters) {
    return $this->noteRepository->getUserNotes($telegramUserId, $filters);
  }

  /**
  * Mendapatkan satu catatan berdasarkan ID dan user ID.
  */
  public function getNote(int $id, int $telegramUserId): Note
  {
    $note = $this->noteRepository->findForUser($id, $telegramUserId);

    if (!$note) {
      throw new ModelNotFoundException('Catatan tidak ditemukan.');
    }

    return $note;
  }

  /**
  * Membuat catatan baru beserta tag dan pengingat opsional.
  */
  public function createNote(int $telegramUserId, array $data): Note
  {
    $data['telegram_user_id'] = $telegramUserId;

    $tags = $data['tags'] ?? [];
    unset($data['tags']);
    $reminderAt = $data['reminder_at'] ?? null;
    unset($data['reminder_at']);

    // Decode JSON string jika perlu
    if (is_string($tags)) {
      $decoded = json_decode($tags, true);
      $tags = is_array($decoded) ? $decoded : [];
    } elseif (!is_array($tags)) {
      $tags = [];
    }

    $data['content'] = $this->sanitizeContent($data['content'] ?? '');
    $note = $this->noteRepository->create($data);

    $this->syncTags($note, $tags); // selalu panggil, meski kosong

    if ($reminderAt) {
      $note->reminder()->create(['remind_at' => $reminderAt]);
    }

    return $note->load('tags', 'reminder');
  }

  /**
  * Memperbarui catatan beserta tag dan pengingat.
  */
  public function updateNote(int $id, int $telegramUserId, array $data): Note
  {
    $note = $this->noteRepository->findForUser($id, $telegramUserId);
    if (!$note) {
      throw new ModelNotFoundException('Catatan tidak ditemukan.');
    }

    $tags = $data['tags'] ?? null;
    unset($data['tags']);
    $reminderAt = $data['reminder_at'] ?? null;
    unset($data['reminder_at']);

    if (is_string($tags)) {
      $decoded = json_decode($tags, true);
      $tags = is_array($decoded) ? $decoded : [];
    } elseif (!is_array($tags)) {
      $tags = [];
    }

    $data['content'] = $this->sanitizeContent($data['content'] ?? '');
    $note = $this->noteRepository->update($note, $data);

    $this->syncTags($note, $tags);

    if ($reminderAt) {
      $note->reminder()->updateOrCreate(
        ['note_id' => $note->id],
        ['remind_at' => $reminderAt]
      );
    }

    return $note->load('tags', 'reminder');
  }

  /**
  * Menghapus catatan.
  */
  public function deleteNote(int $id, int $telegramUserId): void
  {
    $note = $this->noteRepository->findForUser($id, $telegramUserId);

    if (!$note) {
      throw new ModelNotFoundException('Catatan tidak ditemukan.');
    }

    $this->noteRepository->delete($note);
  }

  /**
  * Sinkronisasi tag dengan catatan.
  */
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
        ['color' => null] // nanti bisa diganti random color
      );

      $tagIds[] = $tag->id;
    }

    $note->tags()->sync($tagIds);
  }

  private function sanitizeContent(string $html): string
  {
    $allowedTags = '<p><br><strong><em><u><s><h1><h2><blockquote><ol><ul><li><a><img><code><pre><span>';
    $clean = strip_tags($html, $allowedTags);
    // Hapus event handler sederhana
    $clean = preg_replace('/ on\w+="[^"]*"/i', '', $clean);
    return $clean;
  }
}