<?php
namespace Modules\Notes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Notes\Enums\NoteType;

class StoreNoteRequest extends FormRequest
{
  public function rules(): array
  {
    return [
      'title' => 'required|string|max:255',
      'content' => 'nullable|string',
      'type' => ['nullable',
        Rule::enum(NoteType::class)],
      'tags' => 'nullable|array',
      'tags.*' => 'string|max:50',
      'reminder_at' => 'nullable|date',
      'metadata' => 'nullable|array',
      'note_date' => 'nullable|date'
    ];
  }
}