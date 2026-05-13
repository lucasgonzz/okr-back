<?php

/*
|--------------------------------------------------------------------------
| CORS (fruitcake/laravel-cors + asm89/stack-cors)
|--------------------------------------------------------------------------
|
| - paths: usar ['*'] en subdominio dedicado al API evita que el preflight
|   OPTIONS no pase el matcher (p. ej. diferencias de path en el hosting).
|
| - supports_credentials: el front usa Bearer en cabecera, sin cookies → false
|   simplifica la respuesta preflight.
|
| - CORS_ALLOWED_ORIGINS_PATTERNS: regex alternativas separadas por | (opcional).
|   Útil si el origen no coincide exactamente con la lista.
|
| Tras cambiar .env: php artisan config:clear
|--------------------------------------------------------------------------
*/

$corsExtra = env('CORS_ALLOWED_ORIGINS');
$rawOrigins = ($corsExtra !== null && trim($corsExtra) !== '')
    ? $corsExtra
    : env('FRONTEND_URL', 'http://localhost:3000');

$allowedOrigins = array_values(array_unique(array_filter(array_map(
    static function (string $origin): string {
        return rtrim(trim($origin), '/');
    },
    explode(',', $rawOrigins)
))));

$patternsRaw = env('CORS_ALLOWED_ORIGINS_PATTERNS', '');
$allowedPatterns = array_values(array_filter(array_map(
    static function (string $p): string {
        return trim($p);
    },
    explode('|', $patternsRaw)
)));

return [

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => $allowedPatterns,

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86_400,

    'supports_credentials' => false,

];
