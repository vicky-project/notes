<?php
namespace Modules\Notes\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
  protected $table = 'notes_reminders';

  protected $fillable = [
    'note_id',
    'remind_at',
    'is_completed',
    'notified_at'
  ];

  protected $casts = [
    'remind_at' => 'datetime:Y-m-d\TH:i:s\Z',
    'is_completed' => 'boolean',
    'notified_at' => 'datetime:Y-m-d\TH:i:s\Z',
  ];

  public function note() {
    return $this->belongsTo(Note::class);
  }
}