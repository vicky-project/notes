<?php
namespace Modules\Notes\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Notes\Repositories\ReminderRepository;
use Modules\Notes\Http\Resources\ReminderResource;

class ReminderController extends Controller
{
  public function __construct(protected ReminderRepository $reminderRepository) {}

  public function index(Request $request): JsonResponse
  {
    $user = $request->user();
    $reminders = $this->reminderRepository->getUserReminders($user->id);
    return ReminderResource::collection($reminders)->response();
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