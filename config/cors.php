<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
    'http://localhost:5173',
    'http://localhost:5174',
    'https://blog-six-rouge-10.vercel.app', // Vercel deployment
],
    'allowed_headers' => ['*'],
    'supports_credentials' => false,
];
