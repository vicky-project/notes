<?php
namespace Modules\Notes\Repositories;

use Modules\Notes\Models\Reminder;
use Carbon\Carbon;

class ReminderRepository
{
  public function getUserReminders(int $telegramUserId, bool $upcomingOnly = false) {
    return Reminder::whereHas('note', fn($q) => $q->where('telegram_user_id', $telegramUserId))
    ->when($upcomingOnly, fn($q) => $q->where('remind_at', '>=', Carbon::now())
      ->where('is_completed', false))
    ->with('note')
    ->orderBy('remind_at')
    ->get();
  }

  public function findForUser(int $id, int $telegramUserId): ?Reminder
  {
    return Reminder::where('id', $id)
    ->whereHas('note', fn($q) => $q->where('telegram_user_id', $telegramUserId))
    ->first();
  }

  public function complete(Reminder $reminder): void
  {
    $reminder->update(['is_completed' => true]);
  }

  /**
  * Ambil pengingat yang jatuh tempo dan belum diberitahu.
  */
  public function getDueReminders(): array
  {
    return Reminder::where('remind_at', '<=', Carbon::now())
    ->with(['note.user']) // pastikan relasi note -> user
    ->where('is_completed', false)
    ->whereNull('notified_at')
    ->get()
    ->all();
  }

  public function markNotified(Reminder $reminder): void
  {
    $reminder->update(['notified_at' => Carbon::now()]);
  }

  public function delete(Reminder $reminder): void
  {
    $reminder->delete();
  }

  public function datesWithReminders(int $telegramUserId): array
  {
    return Reminder::whereHas('note', fn($q) => $q->where('telegram_user_id', $telegramUserId))
    ->whereNotNull('remind_at')
    ->selectRaw('DATE(remind_at) as date')
    ->distinct()
    ->pluck('date')
    ->map(fn($d) => $d)
    ->values()
    ->all();
  }
}