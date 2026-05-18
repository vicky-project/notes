<?php

namespace Modules\Notes\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Notes\Services\AIService;

class NotesController extends Controller
{
  /**
  * Display a listing of the resource.
  */
  public function index() {
    return view('notes::index', [
      'aiEnabled' => app(AIService::class)->isEnabled()
    ]);
  }
}