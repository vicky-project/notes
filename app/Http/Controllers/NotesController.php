<?php

namespace Modules\Notes\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotesController extends Controller
{
  /**
  * Display a listing of the resource.
  */
  public function index() {
    return view('notes::index');
  }
}