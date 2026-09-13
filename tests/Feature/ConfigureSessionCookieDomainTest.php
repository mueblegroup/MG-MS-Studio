<?php

use App\Http\Middleware\ConfigureSessionCookieDomain;
use Illuminate\Http\Request;

it('keeps the configured shared session domain for a tenant subdomain', function () {
    config([
        'saas.root_domain' => 'classm8.mueblegroup.com',
        'saas.central_domains' => ['classm8.mueblegroup.com'],
        'session.domain' => '.classm8.mueblegroup.com',
    ]);

    $request = Request::create(
        'https://mueble-tutor.classm8.mueblegroup.com/admin/settings/payment-gateways',
        'GET'
    );

    $response = (new ConfigureSessionCookieDomain())->handle($request, function () {
        expect(config('session.domain'))->toBe('.classm8.mueblegroup.com');

        return response('OK');
    });

    expect($response->getStatusCode())->toBe(200);
});
