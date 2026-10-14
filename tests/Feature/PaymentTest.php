<?php

use App\Models\Article;
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

test('an admin can reject a payment outright, requiring notes and not advancing the registration', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'menunggu_verifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTREJ',
        'status' => 'menunggu_verifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'ditolak',
        'notes' => 'Bukti pembayaran milik orang lain.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('ditolak');
    expect($payment->notes)->toBe('Bukti pembayaran milik orang lain.');
    expect($payment->verified_by)->toBe($admin->id);
    expect($registration->status)->toBe('menunggu_verifikasi');
});

test('an admin can cancel a payment, requiring notes', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'menunggu_pembayaran']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTCANCEL',
        'status' => 'belum_bayar',
    ]);

    $noNotesResponse = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'dibatalkan',
    ]);
    $noNotesResponse->assertSessionHasErrors('notes');

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'dibatalkan',
        'notes' => 'Duplikat transaksi.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    expect($payment->status)->toBe('dibatalkan');
    expect($payment->notes)->toBe('Duplikat transaksi.');
    expect($payment->verified_by)->toBe($admin->id);
});

test('an admin can reset a verified payment back to belum_bayar, which also reopens the registration for payment', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'pembayaran_terverifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTUNDO1',
        'status' => 'terverifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'belum_bayar',
        'notes' => 'Bukti ternyata tidak valid, diminta mengunggah ulang.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('belum_bayar');
    expect($registration->status)->toBe('menunggu_pembayaran');
});

test('an admin cancelling a previously verified payment also reopens the registration for payment', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'pembayaran_terverifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTUNDO2',
        'status' => 'terverifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'dibatalkan',
        'notes' => 'Pembayaran ternyata keliru, dibatalkan.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('dibatalkan');
    expect($registration->status)->toBe('menunggu_pembayaran');
});

test('undoing a verified payment does not touch a registration that has already moved past it', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    // The registration progressed (e.g. the article was already submitted) since this
    // payment was verified, so reverting the payment must not silently rewind it.
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'artikel_diajukan']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTUNDO3',
        'status' => 'terverifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'dibatalkan',
        'notes' => 'Dibatalkan untuk pengujian.',
    ]);

    $response->assertRedirect();
    $payment->refresh();
    $registration->refresh();
    expect($payment->status)->toBe('dibatalkan');
    expect($registration->status)->toBe('artikel_diajukan');
});

test('resetting a payment decision still requires notes', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['status' => 'pembayaran_terverifikasi']);
    $payment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-TESTUNDO4',
        'status' => 'terverifikasi',
    ]);

    $response = $this->actingAs($admin)->put("/admin/payments/{$payment->id}", [
        'decision' => 'belum_bayar',
    ]);

    $response->assertSessionHasErrors('notes');
    expect($payment->fresh()->status)->toBe('terverifikasi');
});

test('the admin payment list shows the participant name and email for both registration and publication fees', function () {
    $admin = makeAdmin();
    $participant = User::factory()->create(['name' => 'Peserta Pembayaran Uji', 'email' => 'pembayaran.uji@snos.test']);
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create();
    $registrationPayment = $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-REGTEST',
        'status' => 'belum_bayar',
    ]);
    $article = Article::factory()->for($registration, 'eventRegistration')->create();
    $publicationPayment = $article->payments()->create([
        'type' => 'publikasi',
        'amount' => 300000,
        'payment_code' => 'PAY-PUBTEST',
        'status' => 'belum_bayar',
    ]);

    $this->actingAs($admin)->get('/admin/payments')->assertOk()->assertInertia(fn ($page) => $page
        ->where('payments.data.0.participant_name', 'Peserta Pembayaran Uji')
        ->where('payments.data.0.participant_email', 'pembayaran.uji@snos.test')
        ->where('payments.data.1.participant_name', 'Peserta Pembayaran Uji')
        ->where('payments.data.1.participant_email', 'pembayaran.uji@snos.test')
    );

    expect($registrationPayment->fresh()->participant_name)->toBe('Peserta Pembayaran Uji');
    expect($registrationPayment->fresh()->participant_email)->toBe('pembayaran.uji@snos.test');
    expect($publicationPayment->fresh()->participant_name)->toBe('Peserta Pembayaran Uji');
    expect($publicationPayment->fresh()->participant_email)->toBe('pembayaran.uji@snos.test');
});
