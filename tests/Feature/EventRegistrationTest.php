<?php

use App\Models\EventRegistration;
use App\Models\User;

function participant(): User
{
    $user = User::factory()->create();
    $user->assignRole('peserta');

    return $user;
}

test('a participant can register for the event and a registration fee payment is created', function () {
    $user = participant();

    $response = $this->actingAs($user)->post('/participant/registrations', [
        'participant_type' => 'presenter_luring',
        'attendance_method' => 'luring',
        'institution' => 'Universitas Contoh',
        'terms_accepted' => true,
    ]);

    $registration = EventRegistration::where('user_id', $user->id)->first();

    $response->assertRedirect(route('participant.registrations.show', $registration));
    expect($registration)->not->toBeNull();
    expect($registration->status)->toBe('menunggu_pembayaran');
    expect($registration->payments)->toHaveCount(1);
    expect((float) $registration->payments->first()->amount)->toBe(750000.0);
});

test('registration requires accepting the terms', function () {
    $user = participant();

    $response = $this->actingAs($user)->post('/participant/registrations', [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'luring',
        'institution' => 'Universitas Contoh',
    ]);

    $response->assertSessionHasErrors('terms_accepted');
});

test('a participant cannot view another participant\'s registration', function () {
    $owner = participant();
    $intruder = participant();
    $registration = EventRegistration::factory()->for($owner, 'user')->create();

    $response = $this->actingAs($intruder)->get("/participant/registrations/{$registration->id}");

    $response->assertForbidden();
});

test('an admin can view any participant\'s registration', function () {
    $owner = participant();
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $registration = EventRegistration::factory()->for($owner, 'user')->create();

    $response = $this->actingAs($admin)->get("/admin/participants/{$registration->id}");

    $response->assertOk();
});

test('a participant can update their registration data before payment is verified', function () {
    $user = participant();
    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'peserta_umum',
        'institution' => 'Universitas Lama',
    ]);
    $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TEST-1',
        'status' => 'belum_bayar',
    ]);

    $response = $this->actingAs($user)->put("/participant/registrations/{$registration->id}", [
        'participant_type' => 'presenter_luring',
        'attendance_method' => 'daring',
        'institution' => 'Universitas Baru',
        'article_scope' => 'Kecerdasan Buatan',
        'join_gala_dinner' => true,
    ]);

    $response->assertRedirect(route('participant.registrations.show', $registration));
    $registration->refresh();
    expect($registration->participant_type)->toBe('presenter_luring');
    expect($registration->attendance_method)->toBe('daring');
    expect($registration->institution)->toBe('Universitas Baru');
    expect($registration->article_scope)->toBe('Kecerdasan Buatan');
    expect($registration->join_gala_dinner)->toBeTrue();
    expect((float) $registration->payments()->where('type', 'registrasi')->first()->amount)->toBe(750000.0);
});

test('a participant cannot change participant_type once the registration payment is verified', function () {
    $user = participant();
    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'peserta_umum',
    ]);
    $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TEST-2',
        'status' => 'terverifikasi',
    ]);

    $response = $this->actingAs($user)->put("/participant/registrations/{$registration->id}", [
        'participant_type' => 'presenter_luring',
        'attendance_method' => 'luring',
        'institution' => 'Universitas Baru',
    ]);

    $response->assertRedirect(route('participant.registrations.show', $registration));
    $registration->refresh();
    expect($registration->participant_type)->toBe('peserta_umum');
    expect($registration->institution)->toBe('Universitas Baru');
    expect((float) $registration->payments()->where('type', 'registrasi')->first()->amount)->toBe(150000.0);
});

test('a participant cannot update another participant\'s registration', function () {
    $owner = participant();
    $intruder = participant();
    $registration = EventRegistration::factory()->for($owner, 'user')->create();

    $response = $this->actingAs($intruder)->put("/participant/registrations/{$registration->id}", [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'luring',
        'institution' => 'Universitas Lain',
    ]);

    $response->assertForbidden();
});
