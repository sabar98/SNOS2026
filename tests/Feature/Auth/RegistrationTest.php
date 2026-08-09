<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register and are immediately active with no email or WhatsApp verification step', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'nik' => '3201234567890001',
        'institution' => 'Universitas Contoh',
        'whatsapp_number' => '081234567890',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@example.com')->first();
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->hasRole('peserta'))->toBeTrue();

    // Follow the full redirect chain: /dashboard -> /participant/dashboard must actually load, not 403/404/500.
    $this->followingRedirects()->get(route('dashboard'))->assertOk();
});
