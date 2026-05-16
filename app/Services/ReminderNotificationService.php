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
    Log::info('Notifikasi pengingat: ' . count($dueReminders) . ' item.');

    foreach ($dueReminders as $reminder) {
      $telegramUser = $reminder->note->user ?? null;
      if (!$telegramUser || !$telegramUser->telegram_id) continue;

      $chatId = $telegramUser->telegram_id;
      $noteTitle = $reminder->note->title;
      $message = "⏰ *Pengingat*\n\n📝 *{$noteTitle}*\n";

      if ($reminder->note->content) {
        $plain = strip_tags($reminder->note->content);
        if (mb_strlen($plain) > 150) $plain = mb_substr($plain, 0, 150) . '...';
        $message .= "{$plain}\n";
      }

      $replyMarkup = [
        'inline_keyboard' => [
          [['text' => '✅ Selesaikan',
            'callback_data' => "reminder_complete_{$reminder->id}"]],
          [['text' => '📖 Buka Notes',
            'url' => config('app.url') . '/notes']]
        ]
      ];

      $result = $this->telegramApi->sendMessage(
        chatId: $chatId,
        text: $message,
        parseMode: 'Markdown',
        replyMarkup: $replyMarkup
      );

      if ($result) {
        $this->reminderRepository->markNotified($reminder);
        Log::info('Notifikasi terkirim', ['chat_id' => $chatId]);
      }
    }
  }
}