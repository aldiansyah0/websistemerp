<?php

beforeEach(function (): void {
    $this->seed();
});

test('core security headers are present on dashboard response', function () {
    $response = $this->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()')
        ->assertHeader('X-XSS-Protection', '0');
});

