<?php

use App\Mail\WelcomeAccountMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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

test('registration still succeeds and logs the user in even if the welcome email fails to send', function () {
    Mail::shouldReceive('to->send')->once()->andThrow(new RuntimeException('Connection could not be established with host smtp.example.com [Connection timed out #110]'));

    $response = $this->post('/register', [
        'name' => 'Test User',
        'nik' => '3201234567899999',
        'institution' => 'Universitas Contoh',
        'whatsapp_number' => '081234567899',
        'email' => 'mail-fails@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    expect(User::where('email', 'mail-fails@example.com')->exists())->toBeTrue();
});

test('a welcome email with the account credentials is sent to the address the user registered with', function () {
    Mail::fake();

    $this->post('/register', [
        'name' => 'Test User',
        'nik' => '3201234567891234',
        'institution' => 'Universitas Contoh',
        'whatsapp_number' => '081234567891',
        'email' => 'welcome-mail@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'welcome-mail@example.com')->first();

    Mail::assertSent(WelcomeAccountMail::class, function (WelcomeAccountMail $mail) use ($user) {
        return $mail->hasTo('welcome-mail@example.com')
            && $mail->user->is($user)
            && $mail->plainPassword === 'password';
    });
});
