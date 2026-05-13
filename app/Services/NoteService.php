<?php
namespace Modules\Notes\Services;

use Modules\Notes\Repositories\NoteRepository;
use Modules\Notes\Repositories\ReminderRepository;
use Modules\Notes\Entities\Tag;

class NoteService
{
  public function __construct(
    protected NoteRepository $noteRepository,
    protected ReminderRepository $reminderRepository
  ) {}

  public function listNotes(int $telegramUserId, array $filters) {
    return $this->noteRepository->getUserNotes($telegramUserId, $filters);
  }

  public function createNote(int $telegramUserId, array $data): Note
  {
    $data['telegram_user_id'] = $telegramUserId;
    // Extract tags and reminder from data
    $tags = $data['tags'] ?? [];
    unset($data['tags']);
    $reminderAt = $data['reminder_at'] ?? null;
    unset($data['reminder_at']);

    $note = $this->noteRepository->create($data);

    if (!empty($tags)) {
      $this->syncTags($note, $tags);
    }

    if ($reminderAt) {
      $note->reminder()->create(['remind_at' => $reminderAt]);
    }

    return $note->load('tags', 'reminder');
  }

  public function updateNote(int $id, int $telegramUserId, array $data): Note
  {
    $note = $this->noteRepository->findForUser($id, $telegramUserId);
    if (!$note) throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Note not found');

    $tags = $data['tags'] ?? null;
    unset($data['tags']);
    $reminderAt = $data['reminder_at'] ?? null;
    unset($data['reminder_at']);

    $note = $this->noteRepository->update($note, $data);

    if (is_array($tags)) {
      $this->syncTags($note, $tags);
    }

    if ($reminderAt) {
      // Update or create reminder
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
    if (!$note) throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Note not found');
    $this->noteRepository->delete($note);
  }

  protected function syncTags(Note $note, array $tagNames): void
  {
    $tagIds = [];
    foreach ($tagNames as $name) {
      $name = trim($name);
      if (empty($name)) continue;
      $tag = Tag::firstOrCreate(
        ['telegram_user_id' => $note->telegram_user_id, 'name' => $name],
        ['color' => null] // bisa random color nanti
      );
      $tagIds[] = $tag->id;
    }
    $note->tags()->sync($tagIds);
  }
}