<?php

use App\Models\Journal;
use App\Models\User;

test('an admin can view the journals page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/journals');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Journals')
        ->has('journals')
        ->has('articlesByJournal')
    );
});

test('an admin can create a journal', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/journals', [
        'name' => 'Jurnal Uji Coba',
        'type' => 'jurnal',
        'publisher' => 'Penerbit Contoh',
        'issn' => '1234-5678',
        'website_url' => 'https://jurnal-uji.test',
        'publication_fee' => 250000,
        'description' => 'Deskripsi singkat.',
    ]);

    $response->assertRedirect();

    $journal = Journal::where('name', 'Jurnal Uji Coba')->first();
    expect($journal)->not->toBeNull();
    expect($journal->type)->toBe('jurnal');
    expect($journal->is_active)->toBeTrue();
});

test('an admin can update a journal', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $journal = Journal::create([
        'name' => 'Jurnal Lama',
        'type' => 'jurnal',
        'publication_fee' => 100000,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->put("/admin/journals/{$journal->id}", [
        'name' => 'Jurnal Baru',
        'type' => 'prosiding',
        'publication_fee' => 200000,
        'is_active' => false,
    ]);

    $response->assertRedirect();
    $journal->refresh();
    expect($journal->name)->toBe('Jurnal Baru');
    expect($journal->type)->toBe('prosiding');
    expect((float) $journal->publication_fee)->toBe(200000.0);
    expect($journal->is_active)->toBeFalse();
});

test('an admin can delete a journal', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $journal = Journal::create([
        'name' => 'Jurnal Dihapus',
        'type' => 'jurnal',
        'publication_fee' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/journals/{$journal->id}");

    $response->assertRedirect();
    expect(Journal::find($journal->id))->toBeNull();
});

test('a non-admin cannot manage journals', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $journal = Journal::create([
        'name' => 'Jurnal Terlindungi',
        'type' => 'jurnal',
        'publication_fee' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($participant)->get('/admin/journals')->assertForbidden();

    $this->actingAs($participant)->post('/admin/journals', [
        'name' => 'Tidak boleh',
        'type' => 'jurnal',
        'publication_fee' => 0,
    ])->assertForbidden();

    $this->actingAs($participant)->put("/admin/journals/{$journal->id}", [
        'name' => 'Tidak boleh',
        'type' => 'jurnal',
        'publication_fee' => 0,
    ])->assertForbidden();

    $this->actingAs($participant)->delete("/admin/journals/{$journal->id}")->assertForbidden();
});
