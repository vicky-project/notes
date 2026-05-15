<?php

namespace Modules\Notes\Console;

use Illuminate\Console\Command;
use Modules\Notes\Services\NoteService;

class SendReminderNotifications extends Command
{
  protected $signature = 'app:send-note-reminders';
  protected $description = 'Kirim notifikasi pengingat yang jatuh tempo';

  public function handle(NoteService $noteService): int
  {
    $noteService->sendDueReminders();
    $this->info('Pengingat dikirim.');
    return Command::SUCCESS;
  }
}