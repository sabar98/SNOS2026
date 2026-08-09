<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users are redirected to their role dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('peserta');
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertRedirect(route('participant.dashboard'));
});

test('admins are redirected to the admin dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertRedirect(route('admin.dashboard'));
});
