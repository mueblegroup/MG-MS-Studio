<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SaaS Root Domain
    |--------------------------------------------------------------------------
    |
    | This is the parent domain used for studio subdomains. For example, if the
    | root domain is mueble.app and a studio chooses "bright", the studio URL
    | becomes https://bright.mueble.app.
    |
    */

    'root_domain' => env('SAAS_ROOT_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | Central Domains
    |--------------------------------------------------------------------------
    |
    | These domains belong to the main SaaS application itself and should not be
    | resolved as tenant