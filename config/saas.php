<?php

$rootDomain = env('SAAS_ROOT_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost');
$rootDomain = strtolower(trim((string) $rootDomain));
$rootDomain = preg_replace('#^https?://#', '', $rootDomain);
$rootDomain = trim($rootDomain, '/');

return [
    // Studio tenant URLs are generated as: {studio-subdomain}.{root_domain}
    // For staging, set this to: studio.mueble-playground.cc
    'root_domain' => $rootDomain,

    // Central