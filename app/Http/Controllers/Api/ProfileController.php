<?php

namespace Modules\Notes\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ProfileController extends Controller
{
  /**
  * Menampilkan data profil pengguna yang sedang login.
  */
  public function show(Request $request): JsonResponse
  {
    $user = $request->user();

    return response()->json([
      'id' => $user->telegram_id ?? $user->id,
      'first_name' => $user->first_name,
      'last_name' => $user->last_name,
      'username' => $user->username ?? null,
      'photo_url' => $user->photo_url ?? null,
    ]);
  }
}