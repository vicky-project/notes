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
}