<?php

use App\Models\User;

test('an admin can create a reviewer account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/reviewers', [
        'name' => 'Reviewer Baru',
        'email' => 'reviewer.baru@snos.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect();

    $reviewer = User::where('email', 'reviewer.baru@snos.test')->first();
    expect($reviewer)->not->toBeNull();
    expect($reviewer->hasRole('reviewer'))->toBeTrue();
    expect($reviewer->email_verified_at)->not->toBeNull();
});

test('an admin can view the create and edit reviewer pages', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $reviewer = User::factory()->create(['name' => 'Reviewer Uji']);
    $reviewer->assignRole('reviewer');

    $this->actingAs($admin)->get('/admin/reviewers/create')->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/ReviewerForm')
    );

    $this->actingAs($admin)->get("/admin/reviewers/{$reviewer->id}/edit")->assertOk()->assertInertia(fn ($page) => $page
        ->component('Admin/ReviewerForm')
        ->where('reviewer.id', $reviewer->id)
        ->where('reviewer.name', 'Reviewer Uji')
    );
});

test('an admin can update a reviewer account without changing the password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $reviewer = User::factory()->create(['name' => 'Nama Lama', 'email' => 'lama.reviewer@snos.test']);
    $reviewer->assignRole('reviewer');
    $originalPassword = $reviewer->password;

    $response = $this->actingAs($admin)->put("/admin/reviewers/{$reviewer->id}", [
        'name' => 'Nama Baru',
        'email' => 'baru.reviewer@snos.test',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertRedirect();
    $reviewer->refresh();
    expect($reviewer->name)->toBe('Nama Baru');
    expect($reviewer->email)->toBe('baru.reviewer@snos.test');
    expect($reviewer->password)->toBe($originalPassword);
});

test('an admin can reset a reviewer account password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $reviewer = User::factory()->create();
    $reviewer->assignRole('reviewer');
    $originalPassword = $reviewer->password;

    $response = $this->actingAs($admin)->put("/admin/reviewers/{$reviewer->id}", [
        'name' => $reviewer->name,
        'email' => $reviewer->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $reviewer->refresh();
    expect($reviewer->password)->not->toBe($originalPassword);
});

test('an admin can delete a reviewer account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $reviewer = User::factory()->create();
    $reviewer->assignRole('reviewer');

    $response = $this->actingAs($admin)->delete("/admin/reviewers/{$reviewer->id}");

    $response->assertRedirect();
    expect(User::find($reviewer->id))->toBeNull();
});

test('an admin cannot manage a user account that is not a reviewer through this endpoint', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $updateResponse = $this->actingAs($admin)->put("/admin/reviewers/{$participant->id}", [
        'name' => 'Hijacked',
        'email' => $participant->email,
    ]);
    $updateResponse->assertNotFound();

    $destroyResponse = $this->actingAs($admin)->delete("/admin/reviewers/{$participant->id}");
    $destroyResponse->assertNotFound();

    expect(User::find($participant->id))->not->toBeNull();
});

test('a non-admin cannot manage reviewer accounts', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $reviewer = User::factory()->create();
    $reviewer->assignRole('reviewer');

    $this->actingAs($participant)->post('/admin/reviewers', [
        'name' => 'Tidak boleh',
        'email' => 'tidakboleh.reviewer@snos.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertForbidden();

    $this->actingAs($participant)->put("/admin/reviewers/{$reviewer->id}", [
        'name' => 'Tidak boleh',
        'email' => $reviewer->email,
    ])->assertForbidden();

    $this->actingAs($participant)->delete("/admin/reviewers/{$reviewer->id}")->assertForbidden();
    $this->actingAs($participant)->get('/admin/reviewers/create')->assertForbidden();
    $this->actingAs($participant)->get("/admin/reviewers/{$reviewer->id}/edit")->assertForbidden();
});
