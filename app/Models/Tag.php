<?php
namespace Modules\Notes\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
  protected $table = 'notes_tags';

  protected $fillable = ['telegram_user_id',
    'name',
    'color'];

  public function notes() {
    return $this->belongsToMany(Note::class, 'note_tag');
  }
}