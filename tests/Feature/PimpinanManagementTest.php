<?php

use App\Models\User;

test('an admin can create a pimpinan account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/pimpinan', [
        'name' => 'Pimpinan Baru',
        'email' => 'pimpinan.baru@snos.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect();

    $leader = User::where('email', 'pimpinan.baru@snos.test')->first();
    expect($leader)->not->toBeNull();
    expect($leader->hasRole('pimpinan'))->toBeTrue();
    expect($leader->email_verified_at)->not->toBeNull();
});

test('an admin can update a pimpinan account without changing the password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $leader = User::factory()->create(['name' => 'Nama Lama', 'email' => 'lama.pimpinan@snos.test']);
    $leader->assignRole('pimpinan');
    $originalPassword = $leader->password;

    $response = $this->actingAs($admin)->put("/admin/pimpinan/{$leader->id}", [
        'name' => 'Nama Baru',
        'email' => 'baru.pimpinan@snos.test',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect();
    $leader->refresh();
    expect($leader->name)->toBe('Nama Baru');
    expect($leader->email)->toBe('baru.pimpinan@snos.test');
    expect($leader->password)->toBe($originalPassword);
});

test('an admin can reset a pimpinan account password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $leader = User::factory()->create();
    $leader->assignRole('pimpinan');
    $originalPassword = $leader->password;

    $response = $this->actingAs($admin)->put("/admin/pimpinan/{$leader->id}", [
        'name' => $leader->name,
        'email' => $leader->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $leader->refresh();
    expect($leader->password)->not->toBe($originalPassword);
});

test('an admin can delete a pimpinan account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $leader = User::factory()->create();
    $leader->assignRole('pimpinan');

    $response = $this->actingAs($admin)->delete("/admin/pimpinan/{$leader->id}");

    $response->assertRedirect();
    expect(User::find($leader->id))->toBeNull();
});

test('an admin cannot manage a user account that is not pimpinan through this endpoint', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $updateResponse = $this->actingAs($admin)->put("/admin/pimpinan/{$participant->id}", [
        'name' => 'Hijacked',
        'email' => $participant->email,
    ]);
    $updateResponse->assertNotFound();

    $destroyResponse = $this->actingAs($admin)->delete("/admin/pimpinan/{$participant->id}");
    $destroyResponse->assertNotFound();

    expect(User::find($participant->id))->not->toBeNull();
});

test('a non-admin cannot manage pimpinan accounts', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $leader = User::factory()->create();
    $leader->assignRole('pimpinan');

    $this->actingAs($participant)->post('/admin/pimpinan', [
        'name' => 'Tidak boleh',
        'email' => 'tidakboleh.pimpinan@snos.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertForbidden();

    $this->actingAs($participant)->put("/admin/pimpinan/{$leader->id}", [
        'name' => 'Tidak boleh',
        'email' => $leader->email,
    ])->assertForbidden();

    $this->actingAs($participant)->delete("/admin/pimpinan/{$leader->id}")->assertForbidden();
});
