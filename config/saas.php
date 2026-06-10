<?php

return [
    'root_domain' => env('SAAS_ROOT_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'),
    'central_domains' => array_filter(array_map('trim', explode(',', env('SAAS_CENTRAL_DOMAINS', 'localhost,127.0.0.1')))),
    'trial_days' => (int) env('SAAS_TRIAL_DAYS', 14),
    'reserved_subdomains' => ['www', 'app', 'admin', 'api'],
];
