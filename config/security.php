<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEC-03: AES-256-GCM Telepon Encryption Key
    |--------------------------------------------------------------------------
    |
    | Key 256-bit (32 bytes) dibaca dalam bentuk base64 dari environment variable
    | TELEPON_KEY_V1. Service AesGcm membaca config ini, bukan env() langsung.
    |
    */
    'telepon_key' => env('TELEPON_KEY_V1'),
];
