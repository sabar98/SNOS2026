<?php

use App\Models\User;

function userWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('each role can reach its own dashboard', function (string $role, string $path) {
    $user = userWithRole($role);

    $response = $this->actingAs($user)->get($path);

    $response->assertOk();
})->with([
    ['peserta', '/participant/dashboard'],
    ['admin', '/admin/dashboard'],
    ['reviewer', '/reviewer/articles'],
    ['moderator', '/moderator/sessions'],
    ['pimpinan', '/pimpinan/dashboard'],
]);

test('non-admin roles are forbidden from the admin area', function (string $role) {
    $user = userWithRole($role);

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertForbidden();
})->with(['peserta', 'reviewer', 'moderator', 'pimpinan']);

test('a participant is forbidden from reviewer, moderator, and leadership areas', function (string $path) {
    $user = userWithRole('peserta');

    $response = $this->actingAs($user)->get($path);

    $response->assertForbidden();
})->with([
    '/reviewer/articles',
    '/moderator/sessions',
    '/pimpinan/dashboard',
]);

test('an admin account without other roles cannot enter reviewer, moderator, leadership, or participant areas', function (string $path) {
    // Route access is role-gated, not admin-bypassed — only object-level Policy
    // checks (via each Policy's before()) grant admin a blanket bypass.
    $admin = userWithRole('admin');

    $response = $this->actingAs($admin)->get($path);

    $response->assertForbidden();
})->with([
    '/reviewer/articles',
    '/moderator/sessions',
    '/pimpinan/dashboard',
    '/participant/dashboard',
]);

test('guests are redirected away from every protected area', function (string $path) {
    $response = $this->get($path);

    $response->assertRedirect('/login');
})->with([
    '/dashboard',
    '/participant/dashboard',
    '/admin/dashboard',
    '/reviewer/articles',
    '/moderator/sessions',
    '/pimpinan/dashboard',
]);
