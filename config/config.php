<?php

return [
  'name' => 'Notes',
  'integration' => [
    'endpoint' => 'api/integration/note'
  ],
  'ai' => [
    'enabled' => (bool) env('GROQ_API_KEY', false),
    'api_key' => env('GROQ_API_KEY', ''),
  ],
];