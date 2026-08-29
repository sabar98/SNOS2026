<?php

use App\Models\RegistrationFee;
use App\Models\User;

test('an admin can view the registration fees page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/registration-fees');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/RegistrationFees')
        ->has('registrationFees')
    );
});

test('an admin can create a registration fee rule', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    RegistrationFee::query()->delete();

    $response = $this->actingAs($admin)->post('/admin/registration-fees', [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'luring',
        'amount' => 100000,
    ]);

    $response->assertRedirect();

    $fee = RegistrationFee::where('participant_type', 'peserta_umum')->where('attendance_method', 'luring')->first();
    expect($fee)->not->toBeNull();
    expect($fee->amount)->toBe(100000);
});

test('a registration fee rule must be unique per participant type and attendance method', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    RegistrationFee::query()->delete();
    RegistrationFee::create(['participant_type' => 'peserta_umum', 'attendance_method' => 'luring', 'amount' => 100000]);

    $response = $this->actingAs($admin)->post('/admin/registration-fees', [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'luring',
        'amount' => 150000,
    ]);

    $response->assertSessionHasErrors('participant_type');
});

test('an admin can update a registration fee rule', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    RegistrationFee::query()->delete();
    $fee = RegistrationFee::create(['participant_type' => 'peserta_umum', 'attendance_method' => 'daring', 'amount' => 50000]);

    $response = $this->actingAs($admin)->put("/admin/registration-fees/{$fee->id}", [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'daring',
        'amount' => 75000,
    ]);

    $response->assertRedirect();
    $fee->refresh();
    expect($fee->amount)->toBe(75000);
});

test('an admin can delete a registration fee rule', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    RegistrationFee::query()->delete();
    $fee = RegistrationFee::create(['participant_type' => 'peserta_mahasiswa', 'attendance_method' => 'daring', 'amount' => 50000]);

    $response = $this->actingAs($admin)->delete("/admin/registration-fees/{$fee->id}");

    $response->assertRedirect();
    expect(RegistrationFee::find($fee->id))->toBeNull();
});

test('a non-admin cannot manage registration fees', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    RegistrationFee::query()->delete();
    $fee = RegistrationFee::create(['participant_type' => 'peserta_umum', 'attendance_method' => 'luring', 'amount' => 100000]);

    $this->actingAs($participant)->get('/admin/registration-fees')->assertForbidden();

    $this->actingAs($participant)->post('/admin/registration-fees', [
        'participant_type' => 'peserta_mahasiswa',
        'attendance_method' => 'luring',
        'amount' => 90000,
    ])->assertForbidden();

    $this->actingAs($participant)->put("/admin/registration-fees/{$fee->id}", [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'luring',
        'amount' => 120000,
    ])->assertForbidden();

    $this->actingAs($participant)->delete("/admin/registration-fees/{$fee->id}")->assertForbidden();
});
