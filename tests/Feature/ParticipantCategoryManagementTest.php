<?php

use App\Models\EventRegistration;
use App\Models\ParticipantCategory;
use App\Models\User;

test('an admin can view the participant categories page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/participant-categories');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/ParticipantCategories')
        ->has('participantCategories')
    );
});

test('an admin can create a participant category', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/participant-categories', [
        'key' => 'peserta_alumni',
        'label' => 'Peserta Alumni',
        'is_presenter' => false,
    ]);

    $response->assertRedirect();

    $category = ParticipantCategory::where('key', 'peserta_alumni')->first();
    expect($category)->not->toBeNull();
    expect($category->label)->toBe('Peserta Alumni');
    expect($category->is_active)->toBeTrue();
});

test('a participant category key must be unique', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/participant-categories', [
        'key' => 'peserta_umum',
        'label' => 'Duplikat',
        'is_presenter' => false,
    ]);

    $response->assertSessionHasErrors('key');
});

test('an admin can update a participant category label, presenter flag, and active status, but not its key', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $category = ParticipantCategory::create([
        'key' => 'peserta_alumni',
        'label' => 'Peserta Alumni',
        'is_presenter' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->put("/admin/participant-categories/{$category->id}", [
        'label' => 'Alumni SNOS',
        'is_presenter' => true,
        'is_active' => false,
    ]);

    $response->assertRedirect();
    $category->refresh();
    expect($category->key)->toBe('peserta_alumni');
    expect($category->label)->toBe('Alumni SNOS');
    expect($category->is_presenter)->toBeTrue();
    expect($category->is_active)->toBeFalse();
});

test('a participant category still in use cannot be deleted', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $user = User::factory()->create();
    $user->assignRole('peserta');
    EventRegistration::factory()->for($user, 'user')->create(['participant_type' => 'peserta_umum']);

    $response = $this->actingAs($admin)->delete('/admin/participant-categories/'.ParticipantCategory::where('key', 'peserta_umum')->value('id'));

    $response->assertSessionHasErrors('key');
    expect(ParticipantCategory::where('key', 'peserta_umum')->exists())->toBeTrue();
});

test('an admin can delete a participant category that is not in use', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $category = ParticipantCategory::create([
        'key' => 'peserta_alumni',
        'label' => 'Peserta Alumni',
        'is_presenter' => false,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/participant-categories/{$category->id}");

    $response->assertRedirect();
    expect(ParticipantCategory::find($category->id))->toBeNull();
});

test('a non-admin cannot manage participant categories', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $category = ParticipantCategory::where('key', 'peserta_umum')->first();

    $this->actingAs($participant)->get('/admin/participant-categories')->assertForbidden();

    $this->actingAs($participant)->post('/admin/participant-categories', [
        'key' => 'peserta_alumni',
        'label' => 'Tidak boleh',
        'is_presenter' => false,
    ])->assertForbidden();

    $this->actingAs($participant)->put("/admin/participant-categories/{$category->id}", [
        'label' => 'Tidak boleh',
        'is_presenter' => false,
        'is_active' => true,
    ])->assertForbidden();

    $this->actingAs($participant)->delete("/admin/participant-categories/{$category->id}")->assertForbidden();
});
