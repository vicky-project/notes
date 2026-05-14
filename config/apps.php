<?php

return [
  'id' => 'notes',
  'name' => 'Notes',
  'description' => 'Your personal second brain — capture ideas, set reminders, and never forget important things.',
  'icon_emoji' => '📝',
  'render_type' => 'iframe',
  'render_config' => [
    'url' => env('APP_URL') . '/apps/notes'
  ]
];