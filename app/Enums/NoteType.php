<?php

namespace Modules\Notes\Enums;

enum NoteType: string
{
  case Text = 'text';
  case Checklist = 'checklist';
  case Image = 'image';
  case Voice = 'voice';
}