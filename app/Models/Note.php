<?php
namespace Modules\Notes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Notes\Enums\NoteType;
use Modules\Telegram\Models\TelegramUser;

class Note extends Model
{
  use SoftDeletes;

  protected $table = 'notes';

  protected $fillable = [
    'telegram_user_id',
    'title',
    'content',
    'type',
    'metadata'
  ];

  protected $casts = [
    'metadata' => 'array',
    'type' => NoteType::class
  ];

  public function user() {
    return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
  }

  public function tags() {
    return $this->belongsToMany(Tag::class, 'note_tag');
  }

  public function reminder() {
    return $this->hasOne(Reminder::class);
  }
}