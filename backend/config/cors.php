<?php

$defaultOrigins = ['http://127.0.0.1:5173', 'http://localhost:5173'];
$extraOrigins = array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', ''))));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_unique(array_merge($defaultOrigins, $extraOrigins))),
    'allowed_origins_patterns' => [
        '/^https:\/\/.*\.onrender\.com$/',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
