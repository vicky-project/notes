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
  'back_home_route' => 'apps.index',
  'hooks' => [
    'enabled' => env('NOTES_HOOK_ENABLED', false),
    'service' => \Modules\CoreUI\Services\UIService::class,
  ]
];