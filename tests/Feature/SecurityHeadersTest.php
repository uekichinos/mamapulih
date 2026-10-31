<?php

it('sets baseline security headers on every response', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Content-Security-Policy');
});

it('does not set HSTS over a plain HTTP request', function () {
    $response = $this->get('/');

    $response->assertHeaderMissing('Strict-Transport-Security');
});

it('sets HSTS over an HTTPS request', function () {
    $response = $this->get('https://localhost/');

    $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});
