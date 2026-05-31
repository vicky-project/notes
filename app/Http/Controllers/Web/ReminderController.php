<?php

namespace Modules\Notes\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notes\Repositories\ReminderRepository;

class ReminderController extends Controller
{
  public function __construct(protected ReminderRepository $reminderRepository) {}

  public function index() {
    $reminders = $this->reminderRepository->getUserReminders(auth()->id());
    return view('notes::web.reminders', compact('reminders'));
  }

  public function complete(int $id) {
    $reminder = $this->reminderRepository->findForUser($id, auth()->id());
    if ($reminder) {
      $this->reminderRepository->complete($reminder);
    }
    return back()->with('success', 'Pengingat diselesaikan.');
  }

  public function destroy(int $id) {
    $reminder = $this->reminderRepository->findForUser($id, auth()->id());
    if ($reminder) {
      $this->reminderRepository->delete($reminder);
    }
    return back()->with('success', 'Pengingat dihapus.');
  }
}