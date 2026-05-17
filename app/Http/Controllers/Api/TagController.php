<?php

namespace Modules\Notes\Http\Controllers\Api;

use Modules\Notes\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TagController extends Controller
{
  public function index(Request $request): JsonResponse
  {
    $user = $request->user();
    $tags = Tag::where('telegram_user_id', $user->id)
    ->orderBy('name')
    ->get(['id', 'name', 'color']);

    return response()->json($tags);
  }
}