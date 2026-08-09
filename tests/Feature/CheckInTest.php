<?php

use App\Models\EventRegistration;
use App\Models\User;

test('an admin can check in a participant by scanning their registration number', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create([
        'registration_number' => 'SNOS2026-QRTEST1',
        'status' => 'jadwal_ditetapkan',
    ]);

    $response = $this->actingAs($admin)->post('/admin/check-in', [
        'registration_number' => 'SNOS2026-QRTEST1',
    ]);

    $response->assertRedirect();
    $registration->refresh();
    expect($registration->status)->toBe('hadir');
    expect($registration->attendances)->toHaveCount(1);
    expect($registration->attendances->first()->method)->toBe('qr_code');
});

test('checking in an unknown registration number shows a validation error', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/check-in', [
        'registration_number' => 'DOES-NOT-EXIST',
    ]);

    $response->assertSessionHasErrors('registration_number');
});

test('a non-admin cannot access the check-in scanner', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $response = $this->actingAs($participant)->get('/admin/check-in');

    $response->assertForbidden();
});
