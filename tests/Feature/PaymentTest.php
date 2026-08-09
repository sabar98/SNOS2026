<?php

use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

test('a participant can upload proof of payment', function () {
    Storage::fake('public');

    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'menunggu_pembayaran']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TEST123',
        'status' => 'belum_bayar',
    ]);

    $response = $this->actingAs($participant)->post("/participant/payments/{$payment->id}/proof", [
        'proof' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
    ]);

    $response->assertRedirect();
    $payment->refresh();
    expect($payment->status)->toBe('menunggu_verifikasi');
    expect($payment->proof_file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($payment->proof_file_path);
});

test('a participant cannot upload proof for someone else\'s payment', function () {
    $owner = User::factory()->create();
    $owner->assignRole('peserta');
    $intruder = User::factory()->create();
    $intruder->assignRole('peserta');

    $registration = EventRegistration::factory()->for($owner, 'user')->create();
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TEST456',
        'status' => 'belum_bayar',
    ]);

    $response = $this->actingAs($intruder)->post("/participant/payments/{$payment->id}/proof", [
        'proof' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
    ]);

    $response->assertForbidden();
});

test('an admin verifying a registration payment marks the registration as paid', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'menunggu_verifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TEST789',
        'status' => 'menunggu_verifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'terverifikasi',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('terverifikasi');
    expect($payment->verified_by)->toBe($admin->id);
    expect($registration->status)->toBe('pembayaran_terverifikasi');
});

test('an admin rejecting a payment requires notes and does not advance the registration', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'menunggu_verifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTABC',
        'status' => 'menunggu_verifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'perlu_perbaikan',
        'notes' => 'Nominal tidak sesuai.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('perlu_perbaikan');
    expect($payment->notes)->toBe('Nominal tidak sesuai.');
    expect($registration->status)->toBe('menunggu_verifikasi');
});
