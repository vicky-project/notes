<?php

namespace Modules\Notes\Services;

use Modules\Notes\Repositories\ReminderRepository;
use Modules\Telegram\Services\Support\TelegramApi;
use Illuminate\Support\Facades\Log;

class ReminderNotificationService
{
  public function __construct(
    protected ReminderRepository $reminderRepository,
    protected TelegramApi $telegramApi
  ) {}

  public function sendDueReminders(): void
  {
    $dueReminders = $this->reminderRepository->getDueReminders();

    if (empty($dueReminders)) {
      Log::info('[Reminder] Tidak ada pengingat yang jatuh tempo.');
      return;
    }

    foreach ($dueReminders as $reminder) {
      $telegramUser = $reminder->note->user ?? null;

      if (!$telegramUser) {
        Log::warning('[Reminder] TelegramUser tidak ditemukan.', [
          'note_id' => $reminder->note_id,
          'reminder_id' => $reminder->id,
          'user' => $telegramUser,
          'note' => $reminder->note,
          'reminder' => $reminder
        ]);
        continue;
      }

      $chatId = $telegramUser->telegram_id ?? $telegramUser->id; // fallback ke id

      if (empty($chatId)) {
        Log::warning('[Reminder] Chat ID kosong.', [
          'telegram_user_id' => $telegramUser->id,
          'reminder_id' => $reminder->id,
        ]);
        continue;
      }

      $noteTitle = $reminder->note->title ?? 'Tanpa Judul';
      $message = "⏰ *Pengingat*\n\n📝 *" . $noteTitle . "*\n";

      if ($reminder->note->content) {
        $plain = strip_tags($reminder->note->content);
        if (mb_strlen($plain) > 150) {
          $plain = mb_substr($plain, 0, 150) . '...';
        }
        $message .= $plain . "\n";
      }

      $message .= "\n⏱ " . $reminder->remind_at->translatedFormat('d M Y, H:i');

      $result = $this->telegramApi->sendMessage(
        chatId: $chatId,
        text: $message,
        parseMode: 'MarkdownV2',
      );

      if ($result) {
        $this->reminderRepository->markNotified($reminder);
      } else {
        Log::error('[Reminder] Gagal mengirim notifikasi.', [
          'chat_id' => $chatId,
          'reminder_id' => $reminder->id,
          'note_id' => $reminder->note_id,
        ]);
      }
    }
  }
}