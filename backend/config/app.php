<?php

return [
    'name'     => env('APP_NAME', 'VanguardiaShift'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => (bool) env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),
    'timezone' => 'America/Argentina/Buenos_Aires',
    'locale'   => 'es',
    'key'      => env('APP_KEY'),
    'cipher'   => 'AES-256-CBC',
    'providers' => [],
];
