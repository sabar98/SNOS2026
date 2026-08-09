<?php

use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('a participant can update their whatsapp number along with the rest of their profile', function () {
    Storage::fake('public');
    $participant = User::factory()->create(['whatsapp_number' => '080000000000']);
    $participant->assignRole('peserta');

    $response = $this->actingAs($participant)->put('/participant/profile', [
        'gender' => 'laki_laki',
        'address' => 'Jl. Contoh No. 1',
        'institution' => 'Universitas Contoh',
        'study_program' => 'Teknik Informatika',
        'whatsapp_number' => '081234567890',
    ]);

    $response->assertRedirect();
    expect($participant->fresh()->whatsapp_number)->toBe('081234567890');
    expect($participant->fresh()->profile->is_complete)->toBeTrue();
});

test('the profile edit page shows the current whatsapp number', function () {
    $participant = User::factory()->create(['whatsapp_number' => '081234567890']);
    $participant->assignRole('peserta');

    $response = $this->actingAs($participant)->get('/participant/profile');

    $response->assertInertia(fn ($page) => $page
        ->component('Participant/ProfileEdit')
        ->where('whatsappNumber', '081234567890')
    );
});
