<?php

use App\Models\User;

test('an admin can create a moderator account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/moderators', [
        'name' => 'Moderator Baru',
        'email' => 'moderator.baru@snos.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect();

    $moderator = User::where('email', 'moderator.baru@snos.test')->first();
    expect($moderator)->not->toBeNull();
    expect($moderator->hasRole('moderator'))->toBeTrue();
    expect($moderator->email_verified_at)->not->toBeNull();
});

test('an admin can update a moderator account without changing the password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $moderator = User::factory()->create(['name' => 'Nama Lama', 'email' => 'lama@snos.test']);
    $moderator->assignRole('moderator');
    $originalPassword = $moderator->password;

    $response = $this->actingAs($admin)->put("/admin/moderators/{$moderator->id}", [
        'name' => 'Nama Baru',
        'email' => 'baru@snos.test',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect();
    $moderator->refresh();
    expect($moderator->name)->toBe('Nama Baru');
    expect($moderator->email)->toBe('baru@snos.test');
    expect($moderator->password)->toBe($originalPassword);
});

test('an admin can reset a moderator account password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');
    $originalPassword = $moderator->password;

    $response = $this->actingAs($admin)->put("/admin/moderators/{$moderator->id}", [
        'name' => $moderator->name,
        'email' => $moderator->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $moderator->refresh();
    expect($moderator->password)->not->toBe($originalPassword);
});

test('an admin can delete a moderator account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    $response = $this->actingAs($admin)->delete("/admin/moderators/{$moderator->id}");

    $response->assertRedirect();
    expect(User::find($moderator->id))->toBeNull();
});

test('an admin cannot manage a user account that is not a moderator through this endpoint', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $updateResponse = $this->actingAs($admin)->put("/admin/moderators/{$participant->id}", [
        'name' => 'Hijacked',
        'email' => $participant->email,
    ]);
    $updateResponse->assertNotFound();

    $destroyResponse = $this->actingAs($admin)->delete("/admin/moderators/{$participant->id}");
    $destroyResponse->assertNotFound();

    expect(User::find($participant->id))->not->toBeNull();
});

test('a non-admin cannot manage moderator accounts', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');

    $this->actingAs($participant)->post('/admin/moderators', [
        'name' => 'Tidak boleh',
        'email' => 'tidakboleh@snos.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertForbidden();

    $this->actingAs($participant)->put("/admin/moderators/{$moderator->id}", [
        'name' => 'Tidak boleh',
        'email' => $moderator->email,
    ])->assertForbidden();

    $this->actingAs($participant)->delete("/admin/moderators/{$moderator->id}")->assertForbidden();
});
