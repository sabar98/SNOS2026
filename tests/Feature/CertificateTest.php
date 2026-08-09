<?php

use App\Models\Certificate;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a nonpresenter registration becomes eligible once paid, present, and evaluated', function () {
    $user = User::factory()->create();
    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'peserta_umum',
    ]);

    expect($registration->isCertificateEligible())->toBeFalse();

    $registration->payments()->create([
        'type' => 'registrasi',
        'amount' => 150000,
        'payment_code' => 'PAY-ELIG1',
        'status' => 'terverifikasi',
    ]);
    expect($registration->fresh()->isCertificateEligible())->toBeFalse();

    $registration->attendances()->create([
        'type' => 'registrasi_ulang',
        'method' => 'qr_code',
        'status' => 'hadir',
    ]);
    expect($registration->fresh()->isCertificateEligible())->toBeFalse();

    $registration->evaluation()->create([
        'speaker_rating' => 5,
        'committee_rating' => 5,
        'material_quality_rating' => 5,
        'facility_rating' => 5,
    ]);
    expect($registration->fresh()->isCertificateEligible())->toBeTrue();
});

test('a presenter also needs an accepted article to be eligible', function () {
    $user = User::factory()->create();
    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'presenter_luring',
    ]);
    $registration->payments()->create(['type' => 'registrasi', 'amount' => 750000, 'payment_code' => 'PAY-ELIG2', 'status' => 'terverifikasi']);
    $registration->attendances()->create(['type' => 'registrasi_ulang', 'method' => 'qr_code', 'status' => 'hadir']);
    $registration->evaluation()->create(['speaker_rating' => 5, 'committee_rating' => 5, 'material_quality_rating' => 5, 'facility_rating' => 5]);

    expect($registration->fresh()->isCertificateEligible())->toBeFalse();

    $registration->articles()->create([
        'title' => 'Judul',
        'abstract' => 'Abstrak',
        'keywords' => 'kk',
        'field' => 'TI',
        'status' => 'diterima',
    ]);

    expect($registration->fresh()->isCertificateEligible())->toBeTrue();
});

test('markCompletedIfEligible advances status to selesai only when fully eligible', function () {
    $user = User::factory()->create();
    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'peserta_umum',
        'status' => 'hadir',
    ]);

    $registration->markCompletedIfEligible();
    expect($registration->fresh()->status)->toBe('hadir');

    $registration->payments()->create(['type' => 'registrasi', 'amount' => 150000, 'payment_code' => 'PAY-ELIG3', 'status' => 'terverifikasi']);
    $registration->attendances()->create(['type' => 'registrasi_ulang', 'method' => 'qr_code', 'status' => 'hadir']);
    $registration->evaluation()->create(['speaker_rating' => 5, 'committee_rating' => 5, 'material_quality_rating' => 5, 'facility_rating' => 5]);

    $registration->markCompletedIfEligible();
    expect($registration->fresh()->status)->toBe('selesai');
});

test('an admin issuing a certificate generates a downloadable PDF with a working verification page', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $user = User::factory()->create();
    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'peserta_umum',
        'status' => 'selesai',
    ]);

    $response = $this->actingAs($admin)->post('/admin/certificates', [
        'event_registration_id' => $registration->id,
        'role' => 'peserta',
        'jp_hours' => 8,
    ]);

    $response->assertRedirect();
    $certificate = Certificate::where('event_registration_id', $registration->id)->first();
    expect($certificate)->not->toBeNull();
    expect($certificate->file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($certificate->file_path);

    $registration->refresh();
    expect($registration->status)->toBe('sertifikat_terbit');

    $verifyResponse = $this->get("/certificates/verify/{$certificate->verification_token}");
    $verifyResponse->assertOk();
    $verifyResponse->assertInertia(fn ($page) => $page
        ->component('CertificateVerify')
        ->where('certificate.certificate_number', $certificate->certificate_number)
    );
});

test('an admin can upload a certificate file directly instead of generating one', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $user = User::factory()->create(['name' => 'Peserta Unggahan']);
    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'peserta_umum',
        'status' => 'selesai',
    ]);

    $response = $this->actingAs($admin)->post('/admin/certificates', [
        'event_registration_id' => $registration->id,
        'role' => 'peserta',
        'jp_hours' => 8,
        'certificate_file' => UploadedFile::fake()->create('sertifikat.pdf', 100, 'application/pdf'),
    ]);

    $response->assertRedirect();
    $certificate = Certificate::where('event_registration_id', $registration->id)->first();
    expect($certificate)->not->toBeNull();
    expect($certificate->user->name)->toBe('Peserta Unggahan');
    expect($certificate->file_path)->toBe("certificates/{$certificate->certificate_number}.pdf");
    Storage::disk('public')->assertExists($certificate->file_path);
});

test('uploading a non-PDF certificate file is rejected', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $user = User::factory()->create();
    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'peserta_umum',
        'status' => 'selesai',
    ]);

    $response = $this->actingAs($admin)->post('/admin/certificates', [
        'event_registration_id' => $registration->id,
        'role' => 'peserta',
        'jp_hours' => 8,
        'certificate_file' => UploadedFile::fake()->create('sertifikat.jpg', 100, 'image/jpeg'),
    ]);

    $response->assertSessionHasErrors('certificate_file');
    expect(Certificate::where('event_registration_id', $registration->id)->exists())->toBeFalse();
});

test('an invalid verification token shows no certificate instead of erroring', function () {
    $response = $this->get('/certificates/verify/not-a-real-token');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('CertificateVerify')
        ->where('certificate', null)
    );
});

test('a participant can only see their own certificates', function () {
    $owner = User::factory()->create();
    $owner->assignRole('peserta');
    $intruder = User::factory()->create();
    $intruder->assignRole('peserta');

    $registration = EventRegistration::factory()->for($owner, 'user')->create();
    Certificate::create([
        'user_id' => $owner->id,
        'event_registration_id' => $registration->id,
        'certificate_number' => 'CERT-TEST-1',
        'role' => 'peserta',
        'verification_token' => 'token-1',
    ]);

    $response = $this->actingAs($intruder)->get('/participant/certificates');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->where('certificates', []));
});
