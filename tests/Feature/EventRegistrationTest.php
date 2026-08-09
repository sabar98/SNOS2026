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
