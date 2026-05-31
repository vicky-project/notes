<?php

namespace Modules\Notes\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Notes\Repositories\ReminderRepository;

class ReminderController extends Controller
{
  public function __construct(protected ReminderRepository $reminderRepository) {}

  public function index(Request $request) {
    $reminders = $this->reminderRepository->getUserReminders($request->telegram_id);
    return view('notes::web.reminders', compact('reminders'));
  }

  public function complete(Request $request, int $id) {
    $reminder = $this->reminderRepository->findForUser($id, $request->telegram_id);
    if ($reminder) {
      $this->reminderRepository->complete($reminder);
    }
    return back()->with('success', 'Pengingat diselesaikan.');
  }

  public function destroy(Request $request, int $id) {
    $reminder = $this->reminderRepository->findForUser($id, $request->telegram_id);
    if ($reminder) {
      $this->reminderRepository->delete($reminder);
    }
    return back()->with('success', 'Pengingat dihapus.');
  }
}