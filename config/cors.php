<?php

$allowedOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
), static fn (string $origin): bool => $origin !== '' && $origin !== '*'));

if ($allowedOrigins === []) {
    $frontendUrl = rtrim(trim((string) env('FRONTEND_URL', '')), '/');
    $allowedOrigins = [$frontendUrl !== '' ? $frontendUrl : 'http://localhost:5173'];
}

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => $allowedOrigins,
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'Origin', 'X-Requested-With'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
