<?php

use App\Models\BankAccount;
use App\Models\EventRegistration;
use App\Models\ParticipantCategory;
use App\Models\User;

function participant(): User
{
    $user = User::factory()->create();
    $user->assignRole('peserta');

    return $user;
}

function activeBankAccount(): BankAccount
{
    return BankAccount::create([
        'bank_name' => 'BCA',
        'account_number' => '1234567890',
        'account_holder' => 'Panitia SNOS 2026',
        'is_active' => true,
    ]);
}

test('a participant can register for the event and a registration fee payment is created', function () {
    $user = participant();
    $bank = activeBankAccount();

    $response = $this->actingAs($user)->post('/participant/registrations', [
        'participant_type' => 'presenter_luring',
        'attendance_method' => 'luring',
        'institution' => 'Universitas Contoh',
        'bank_account_id' => $bank->id,
        'terms_accepted' => true,
    ]);

    $registration = EventRegistration::where('user_id', $user->id)->first();

    $response->assertRedirect(route('participant.registrations.show', $registration));
    expect($registration)->not->toBeNull();
    expect($registration->status)->toBe('menunggu_pembayaran');
    expect($registration->payments)->toHaveCount(1);
    expect((float) $registration->payments->first()->amount)->toBe(750000.0);
    expect($registration->payments->first()->bank_account_id)->toBe($bank->id);
    expect($registration->payments->first()->bank_account)->toContain('BCA', '1234567890', 'Panitia SNOS 2026');
});

test('a participant can register under a custom admin-added participant category', function () {
    // Regression test: participant_categories.key is admin-editable (Admin >
    // Kategori Peserta lets you add e.g. a "Peserta Dosen" golongan), but
    // event_registrations.participant_type used to be a fixed ENUM seeded only
    // with the 4 original categories. Registering under any newer category
    // failed with a 500 (MySQL "Data truncated for column participant_type").
    ParticipantCategory::create([
        'key' => 'peserta_dosen',
        'label' => 'Peserta Dosen',
        'golongan' => 'dosen',
        'is_presenter' => false,
        'is_active' => true,
    ]);
    $user = participant();
    $bank = activeBankAccount();

    $response = $this->actingAs($user)->post('/participant/registrations', [
        'participant_type' => 'peserta_dosen',
        'attendance_method' => 'luring',
        'institution' => 'Universitas Contoh',
        'bank_account_id' => $bank->id,
        'terms_accepted' => true,
    ]);

    $registration = EventRegistration::where('user_id', $user->id)->first();

    $response->assertRedirect(route('participant.registrations.show', $registration));
    expect($registration)->not->toBeNull();
    expect($registration->participant_type)->toBe('peserta_dosen');
});

test('a participant can opt into wisata sabang and wisata lokal when registering', function () {
    $user = participant();
    $bank = activeBankAccount();

    $response = $this->actingAs($user)->post('/participant/registrations', [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'luring',
        'institution' => 'Universitas Contoh',
        'bank_account_id' => $bank->id,
        'terms_accepted' => true,
        'join_wisata_sabang' => true,
        'join_wisata_lokal' => true,
    ]);

    $registration = EventRegistration::where('user_id', $user->id)->first();

    $response->assertRedirect(route('participant.registrations.show', $registration));
    expect($registration->join_wisata_sabang)->toBeTrue();
    expect($registration->join_wisata_lokal)->toBeTrue();
});

test('a participant can update their wisata sabang and wisata lokal choices', function () {
    $user = participant();
    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'peserta_umum',
        'join_wisata_sabang' => false,
        'join_wisata_lokal' => false,
    ]);

    $response = $this->actingAs($user)->put("/participant/registrations/{$registration->id}", [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'luring',
        'institution' => $registration->institution ?? 'Universitas Contoh',
        'join_wisata_sabang' => true,
        'join_wisata_lokal' => true,
    ]);

    $response->assertRedirect(route('participant.registrations.show', $registration));
    $registration->refresh();
    expect($registration->join_wisata_sabang)->toBeTrue();
    expect($registration->join_wisata_lokal)->toBeTrue();
});

test('a participant can register for more than one event/activity', function () {
    $user = participant();
    $bank = activeBankAccount();

    $first = $this->actingAs($user)->post('/participant/registrations', [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'luring',
        'institution' => 'Universitas Contoh',
        'bank_account_id' => $bank->id,
        'terms_accepted' => true,
    ]);
    $first->assertRedirect();

    $second = $this->actingAs($user)->post('/participant/registrations', [
        'participant_type' => 'presenter_daring',
        'attendance_method' => 'daring',
        'institution' => 'Universitas Contoh',
        'bank_account_id' => $bank->id,
        'terms_accepted' => true,
    ]);
    $second->assertRedirect();

    $registrations = EventRegistration::where('user_id', $user->id)->get();
    expect($registrations)->toHaveCount(2);
    expect($registrations->pluck('participant_type')->all())->toBe(['peserta_umum', 'presenter_daring']);

    // Both registrations must be independently visible on the dashboard.
    $this->actingAs($user)->get('/participant/dashboard')
        ->assertInertia(fn ($page) => $page->has('registrations', 2));
});

test('registration requires accepting the terms', function () {
    $user = participant();
    $bank = activeBankAccount();

    $response = $this->actingAs($user)->post('/participant/registrations', [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'luring',
        'institution' => 'Universitas Contoh',
        'bank_account_id' => $bank->id,
    ]);

    $response->assertSessionHasErrors('terms_accepted');
});

test('registration requires a valid bank account', function () {
    $user = participant();

    $response = $this->actingAs($user)->post('/participant/registrations', [
        'participant_type' => 'peserta_umum',
        'attendance_method' => 'luring',
        'institution' => 'Universitas Contoh',
        'terms_accepted' => true,
    ]);

    $response->assertSessionHasErrors('bank_account_id');
});

test('a participant cannot view another participant\'s registration', function () {
    $owner = participant();
    $intruder = participant();
    $registration = EventRegistration::factory()->for($owner, 'user')->create();

    $response = $this->actingAs($intruder)->get("/participant/registrations/{$registration->id}");

    $response->assertForbidden();
});

test('the registration detail page reports whether the participant type is a presenter category', function () {
    $presenter = participant();
    $presenterRegistration = EventRegistration::factory()->for($presenter, 'user')->create(['participant_type' => 'presenter_luring']);

    $nonPresenter = participant();
    $nonPresenterRegistration = EventRegistration::factory()->for($nonPresenter, 'user')->create(['participant_type' => 'peserta_umum']);

    $this->actingAs($presenter)->get("/participant/registrations/{$presenterRegistration->id}")
        ->assertInertia(fn ($page) => $page->where('isPresenter', true));

    $this->actingAs($nonPresenter)->get("/participant/registrations/{$nonPresenterRegistration->id}")
        ->assertInertia(fn ($page) => $page->where('isPresenter', false));
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
