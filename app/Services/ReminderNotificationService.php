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

    Log::info('[Reminder] Memulai pengiriman notifikasi.', [
      'total_due' => count($dueReminders),
      'time' => now()->toDateTimeString(),
    ]);

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

      $replyMarkup = [
        'inline_keyboard' => [
          [
            [
              'text' => '✅ Selesaikan',
              'callback_data' => "reminder_complete_{$reminder->id}"
            ]
          ],
          [
            [
              'text' => '📖 Buka Notes',
              'url' => config('app.url') . '/notes'
            ]
          ]
        ]
      ];

      Log::info('[Reminder] Mengirim pesan ke chat.', [
        'chat_id' => $chatId,
        'reminder_id' => $reminder->id,
        'note_title' => $noteTitle,
      ]);

      $result = $this->telegramApi->sendMessage(
        chatId: $chatId,
        text: $message,
        parseMode: 'Markdown',
        replyMarkup: $replyMarkup
      );

      if ($result) {
        $this->reminderRepository->markNotified($reminder);
        Log::info('[Reminder] Notifikasi berhasil dikirim.', [
          'chat_id' => $chatId,
          'reminder_id' => $reminder->id,
        ]);
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