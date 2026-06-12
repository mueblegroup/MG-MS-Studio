<?php

$rootDomain = env('SAAS_ROOT_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost');
$rootDomain = strtolower(trim((string) $rootDomain));
$rootDomain = preg_replace('#^https?://#', '', $rootDomain);
$rootDomain = trim($rootDomain, '/');

$centralDomains = array_filter(array_map('trim', explode(',', (string) env('SAAS_CENTRAL_DOMAINS', ''))));
$appHost = parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST);

if ($appHost) {
    $centralDomains[] = strtolower($appHost);
}

return [
    // Studio tenant URLs are generated as: {studio-subdomain}.{root_domain}
    // For staging, set SAAS_ROOT_DOMAIN=studio.mueble-playground.cc
    // Example tenant URL: demo.studio.mueble-playground.cc
    'root_domain' => $rootDomain,

    // Central domains are allowed to show the marketing/institute registration pages.
    // Example: staging-sms.mueble-playground.cc
    'central_domains' => array_values(array_unique(array_map('strtolower', $centralDomains))),

    'trial_days' => (int) env('SAAS_TRIAL_DAYS', 14),

    'reserved_subdomains' => [
        'www',
        'app',
        'api',
        'admin',
        'dashboard',
        'login',
        'register',
        'support',
        'help',
        'mail',
        'smtp',
        'imap',
        'pop',
        'ftp',
        'cpanel',
        'webmail',
        'studio',
        'staging',
        'production',
        'mueble',
    ],
];
