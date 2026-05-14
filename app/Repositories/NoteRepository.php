<?php
namespace Modules\Notes\Repositories;

use Modules\Notes\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NoteRepository
{
  public function getUserNotes(int $telegramUserId, array $filters = []): LengthAwarePaginator
  {
    return Note::with(['tags', 'reminder'])->where('telegram_user_id', $telegramUserId)
    ->when(isset($filters['tag']), function ($q) use ($filters) {
      $q->whereHas('tags', fn($q) => $q->where('name', $filters['tag']));
    })
    ->when(isset($filters['search']), function ($q) use ($filters) {
      $q->where(function ($q) use ($filters) {
        $q->where('title', 'like', '%'.$filters['search'].'%')
        ->orWhere('content', 'like', '%'.$filters['search'].'%');
      });
    })
    ->latest()
    ->paginate($filters['per_page'] ?? 15);
  }

  public function findForUser(int $id, int $telegramUserId): ?Note
  {
    return Note::with(['tags', 'reminder'])->where('id', $id)->where('telegram_user_id', $telegramUserId)->first();
  }

  public function create(array $data): Note
  {
    return Note::create($data);
  }

  public function update(Note $note, array $data): Note
  {
    $note->update($data);
    return $note;
  }

  public function delete(Note $note): void
  {
    $note->delete();
  }
}