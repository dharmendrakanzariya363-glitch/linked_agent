<?php

return [

    'media_disk' => env('MEDIA_DISK', 'public'),

    'max_prompt_length' => 2000,
    'max_description_length' => 3000,

    'ai' => [
        'max_prompt_length' => 2000,
        'max_description_length' => 3000,
        'rate_limit_per_minute' => 6,
    ],

    'publish' => [
        'rate_limit_per_minute' => 3,
    ],

    'generation' => [
        'stale_after_minutes' => 20,
    ],

];
