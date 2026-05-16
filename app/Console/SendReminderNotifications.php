<?php

namespace Modules\Notes\Console;

use Illuminate\Console\Command;
use Modules\Notes\Services\ReminderNotificationService;

class SendReminderNotifications extends Command
{
  protected $signature = 'app:send-note-reminders';
  protected $description = 'Kirim notifikasi pengingat yang jatuh tempo';

  public function handle(ReminderNotificationService $service): int
  {
    $service->sendDueReminders();
    $this->info('Pengingat dikirim.');
    return Command::SUCCESS;
  }
}