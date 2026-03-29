<?php

test('registration screen is not available to guests', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('public users can not register staff accounts', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertNotFound();
});
