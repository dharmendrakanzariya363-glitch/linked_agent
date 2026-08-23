<?php

return [

    'client_id' => env('LINKEDIN_CLIENT_ID'),

    'client_secret' => env('LINKEDIN_CLIENT_SECRET'),

    'redirect' => env('LINKEDIN_REDIRECT_URI', env('APP_URL').'/linkedin/callback'),

    'scopes' => [
        'openid',
        'profile',
        'email',
        'w_member_social',
    ],

    'api_version' => env('LINKEDIN_API_VERSION', '202411'),

    'authorize_url' => 'https://www.linkedin.com/oauth/v2/authorization',

    'token_url' => 'https://www.linkedin.com/oauth/v2/accessToken',

    'api_base_url' => 'https://api.linkedin.com',

];
