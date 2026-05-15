<?php

return [
  'name' => 'Notes',
  'integration' => [
    'endpoint' => 'api/integration/note'
  ],
  'ai' => [
    'enabled' => (bool) env('GEMINI_API_KEY', false),
    'api_key' => env('GEMINI_API_KEY', ''),
  ],
];