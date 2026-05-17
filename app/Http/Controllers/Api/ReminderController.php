<?php
namespace Modules\Notes\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Notes\Repositories\ReminderRepository;
use Modules\Notes\Services\NoteService;
use Modules\Notes\Http\Resources\ReminderResource;

class ReminderController extends Controller
{
  public function __construct(
    protected ReminderRepository $reminderRepository,
    protected NoteService $noteService
  ) {}

  public function index(Request $request): JsonResponse
  {
    $user = $request->user();
    $reminders = $this->reminderRepository->getUserReminders($user->id);
    return ReminderResource::collection($reminders)->response();
  }

  public function datesWithReminders(Request $request): JsonResponse
  {
    $user = $request->user();

    return response()->json($this->reminderRepository->datesWithReminders($user->id));
  }

  public function destroy(Request $request, int $id): JsonResponse
  {
    $user = $request->user();
    $this->noteService->deleteReminder($id, $user->id);
    return response()->json(['message' => 'Reminder dihapus']);
  }

  public function complete(Request $request, int $id): JsonResponse
  {
    $user = $request->user();
    $reminder = $this->reminderRepository->findForUser($id, $user->id);
    if (!$reminder) abort(404);

    $this->reminderRepository->complete($reminder);
    return response()->json(['message' => 'Pengingat diselesaikan']);
  }
}