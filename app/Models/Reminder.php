<?php
namespace Modules\Notes\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
  protected $table = 'notes_reminder';

  protected $fillable = ['note_id',
    'remind_at',
    'is_completed'];

  protected $casts = [
    'remind_at' => 'datetime',
    'is_completed' => 'boolean',
  ];

  public function note() {
    return $this->belongsTo(Note::class);
  }
}