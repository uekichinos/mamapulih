<?php

use Illuminate\Support\Facades\App;

it('defaults to the configured application locale', function () {
    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page->where('locale', config('app.locale')));
});

it('switches locale and persists it in the session', function () {
    $this->patch('/locale/ms')->assertRedirect();

    expect(session('locale'))->toBe('ms');

    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page->where('locale', 'ms'));
    expect(App::getLocale())->toBe('ms');
});

it('rejects an unsupported locale', function () {
    $this->patch('/locale/fr')->assertNotFound();
});
