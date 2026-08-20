<?php

use App\Models\Certificate;
use App\Models\User;

test('an admin can create a narasumber account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/narasumber', [
        'name' => 'Narasumber Baru',
        'email' => 'narasumber.baru@snos.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect();

    $speaker = User::where('email', 'narasumber.baru@snos.test')->first();
    expect($speaker)->not->toBeNull();
    expect($speaker->hasRole('narasumber'))->toBeTrue();
    expect($speaker->email_verified_at)->not->toBeNull();
});

test('an admin can view the create and edit narasumber pages', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $speaker = User::factory()->create(['name' => 'Narasumber Uji']);
    $speaker->assignRole('narasumber');

    $this->actingAs($admin)->get('/admin/narasumber/create')->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/NarasumberForm')
    );

    $this->actingAs($admin)->get("/admin/narasumber/{$speaker->id}/edit")->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/NarasumberForm')
        ->where('narasumber.id', $speaker->id)
        ->where('narasumber.name', 'Narasumber Uji')
    );
});

test('an admin can update a narasumber account without changing the password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $speaker = User::factory()->create(['name' => 'Nama Lama', 'email' => 'lama.narasumber@snos.test']);
    $speaker->assignRole('narasumber');
    $originalPassword = $speaker->password;

    $response = $this->actingAs($admin)->put("/admin/narasumber/{$speaker->id}", [
        'name' => 'Nama Baru',
        'email' => 'baru.narasumber@snos.test',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect();
    $speaker->refresh();
    expect($speaker->name)->toBe('Nama Baru');
    expect($speaker->email)->toBe('baru.narasumber@snos.test');
    expect($speaker->password)->toBe($originalPassword);
});

test('an admin can reset a narasumber account password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $speaker = User::factory()->create();
    $speaker->assignRole('narasumber');
    $originalPassword = $speaker->password;

    $response = $this->actingAs($admin)->put("/admin/narasumber/{$speaker->id}", [
        'name' => $speaker->name,
        'email' => $speaker->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $speaker->refresh();
    expect($speaker->password)->not->toBe($originalPassword);
});

test('an admin can delete a narasumber account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $speaker = User::factory()->create();
    $speaker->assignRole('narasumber');

    $response = $this->actingAs($admin)->delete("/admin/narasumber/{$speaker->id}");

    $response->assertRedirect();
    expect(User::find($speaker->id))->toBeNull();
});

test('an admin cannot manage a user account that is not narasumber through this endpoint', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $updateResponse = $this->actingAs($admin)->put("/admin/narasumber/{$participant->id}", [
        'name' => 'Hijacked',
        'email' => $participant->email,
    ]);
    $updateResponse->assertNotFound();

    $destroyResponse = $this->actingAs($admin)->delete("/admin/narasumber/{$participant->id}");
    $destroyResponse->assertNotFound();

    expect(User::find($participant->id))->not->toBeNull();
});

test('a non-admin cannot manage narasumber accounts', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $speaker = User::factory()->create();
    $speaker->assignRole('narasumber');

    $this->actingAs($participant)->post('/admin/narasumber', [
        'name' => 'Tidak boleh',
        'email' => 'tidakboleh.narasumber@snos.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertForbidden();

    $this->actingAs($participant)->put("/admin/narasumber/{$speaker->id}", [
        'name' => 'Tidak boleh',
        'email' => $speaker->email,
    ])->assertForbidden();

    $this->actingAs($participant)->delete("/admin/narasumber/{$speaker->id}")->assertForbidden();
    $this->actingAs($participant)->get('/admin/narasumber/create')->assertForbidden();
    $this->actingAs($participant)->get("/admin/narasumber/{$speaker->id}/edit")->assertForbidden();
});

test('a narasumber account is redirected to its own dashboard and can log in successfully', function () {
    $speaker = User::factory()->create(['email' => 'login.narasumber@snos.test']);
    $speaker->assignRole('narasumber');

    $this->actingAs($speaker)->followingRedirects()->get(route('dashboard'))->assertOk()->assertInertia(fn ($page) => $page
        ->component('Narasumber/Dashboard')
    );
});

test('a narasumber dashboard only lists certificates issued with the narasumber role to that user', function () {
    $speaker = User::factory()->create();
    $speaker->assignRole('narasumber');

    Certificate::create([
        'user_id' => $speaker->id,
        'certificate_number' => 'CERT-NARASUMBER-1',
        'role' => 'narasumber',
        'verification_token' => 'token-narasumber-1',
    ]);
    Certificate::create([
        'user_id' => $speaker->id,
        'certificate_number' => 'CERT-PESERTA-1',
        'role' => 'peserta',
        'verification_token' => 'token-narasumber-2',
    ]);

    $response = $this->actingAs($speaker)->get('/narasumber/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Narasumber/Dashboard')
        ->has('certificates', 1)
        ->where('certificates.0.certificate_number', 'CERT-NARASUMBER-1')
    );
});
