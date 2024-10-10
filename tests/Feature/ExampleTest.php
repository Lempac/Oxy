<?php

it('checks the application environment', function () {
    expect(env('APP_ENV'))->toBe('testing');
});

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
