<?php

return [
    'cache' => [
        'enabled' => env(
            'IAM_AUTHORIZATION_CACHE_ENABLED',
            true
        ),

        /*
         * Null uses Laravel's default cache store.
         * Set to "redis" only after Redis is deployed and validated.
         */
        'store' => env(
            'IAM_AUTHORIZATION_CACHE_STORE'
        ),

        'ttl_seconds' => (int) env(
            'IAM_AUTHORIZATION_CACHE_TTL',
            900
        ),

        'prefix' => env(
            'IAM_AUTHORIZATION_CACHE_PREFIX',
            'iam:v2:authorization'
        ),
    ],
];
