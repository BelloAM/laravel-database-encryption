<?php

return [
    'enable_encrypt' => true,
    'encrypt_key' => env('APP_KEY'),
    'encrypt_method' => 'AES-256-CBC',
    'hash_method' => 'sha512',
];
