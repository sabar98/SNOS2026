<?php

use App\Models\User;

test('an admin can create a participant account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/participant-accounts', [
        'name' => 'Peserta Baru',
        'nik' => '3201234567890099',
        'institution' => 'Universitas Contoh',
        'whatsapp_number' => '081234567899',
        'email' => 'peserta.baru@snos.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect();

    $participant = User::where('email', 'peserta.baru@snos.test')->first();
    expect($participant)->not->toBeNull();
    expect($participant->hasRole('peserta'))->toBeTrue();
    expect($participant->nik)->toBe('3201234567890099');
    expect($participant->email_verified_at)->not->toBeNull();
});

test('an admin can view the participant accounts list, create, and edit pages', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $participant = User::factory()->create(['name' => 'Peserta Uji']);
    $participant->assignRole('peserta');

    $this->actingAs($admin)->get('/admin/participant-accounts')->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/ParticipantAccounts')
        ->has('participants')
    );

    $this->actingAs($admin)->get('/admin/participant-accounts/create')->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/ParticipantAccountForm')
    );

    $this->actingAs($admin)->get("/admin/participant-accounts/{$participant->id}/edit")->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/ParticipantAccountForm')
        ->where('participant.id', $participant->id)
        ->where('participant.name', 'Peserta Uji')
    );
});

test('an admin can update a participant account without changing the password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $participant = User::factory()->create([
        'name' => 'Nama Lama',
        'email' => 'lama.peserta@snos.test',
        'nik' => '3201111111111111',
        'institution' => 'Universitas Lama',
        'whatsapp_number' => '081100000000',
    ]);
    $participant->assignRole('peserta');
    $originalPassword = $participant->password;

    $response = $this->actingAs($admin)->put("/admin/participant-accounts/{$participant->id}", [
        'name' => 'Nama Baru',
        'nik' => $participant->nik,
        'institution' => $participant->institution,
        'whatsapp_number' => $participant->whatsapp_number,
        'email' => 'baru.peserta@snos.test',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect();
    $participant->refresh();
    expect($participant->name)->toBe('Nama Baru');
    expect($participant->email)->toBe('baru.peserta@snos.test');
    expect($participant->password)->toBe($originalPassword);
});

test('an admin can reset a participant account password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $participant = User::factory()->create([
        'nik' => '3202222222222222',
        'institution' => 'Universitas Contoh',
        'whatsapp_number' => '081200000000',
    ]);
    $participant->assignRole('peserta');
    $originalPassword = $participant->password;

    $response = $this->actingAs($admin)->put("/admin/participant-accounts/{$participant->id}", [
        'name' => $participant->name,
        'nik' => $participant->nik,
        'institution' => $participant->institution,
        'whatsapp_number' => $participant->whatsapp_number,
        'email' => $participant->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $participant->refresh();
    expect($participant->password)->not->toBe($originalPassword);
});

test('an admin can delete a participant account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $response = $this->actingAs($admin)->delete("/admin/participant-accounts/{$participant->id}");

    $response->assertRedirect();
    expect(User::find($participant->id))->toBeNull();
});

test('an admin cannot manage a user account that is not a participant through this endpoint', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $reviewer = User::factory()->create();
    $reviewer->assignRole('reviewer');

    $updateResponse = $this->actingAs($admin)->put("/admin/participant-accounts/{$reviewer->id}", [
        'name' => 'Hijacked',
        'nik' => '0000000000000000',
        'institution' => 'X',
        'whatsapp_number' => '080000000000',
        'email' => $reviewer->email,
    ]);
    $updateResponse->assertNotFound();

    $destroyResponse = $this->actingAs($admin)->delete("/admin/participant-accounts/{$reviewer->id}");
    $destroyResponse->assertNotFound();

    expect(User::find($reviewer->id))->not->toBeNull();
});

test('a non-admin cannot manage participant accounts', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $other = User::factory()->create();
    $other->assignRole('peserta');

    $this->actingAs($participant)->post('/admin/participant-accounts', [
        'name' => 'Tidak boleh',
        'nik' => '1111111111111111',
        'institution' => 'X',
        'whatsapp_number' => '081111111111',
        'email' => 'tidakboleh.peserta@snos.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertForbidden();

    $this->actingAs($participant)->put("/admin/participant-accounts/{$other->id}", [
        'name' => 'Tidak boleh',
        'nik' => $other->nik,
        'institution' => $other->institution,
        'whatsapp_number' => $other->whatsapp_number,
        'email' => $other->email,
    ])->assertForbidden();

    $this->actingAs($participant)->delete("/admin/participant-accounts/{$other->id}")->assertForbidden();
    $this->actingAs($participant)->get('/admin/participant-accounts/create')->assertForbidden();
    $this->actingAs($participant)->get("/admin/participant-accounts/{$other->id}/edit")->assertForbidden();
});
