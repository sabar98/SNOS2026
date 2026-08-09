<?php

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('an admin can publish an announcement with a thumbnail', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/announcements', [
        'type' => 'pengumuman',
        'title' => 'Pengumuman Uji Coba',
        'body' => '<p>Isi pengumuman</p>',
        'published_at' => now()->format('Y-m-d'),
        'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
    ]);

    $response->assertRedirect();
    $announcement = Announcement::first();
    expect($announcement)->not->toBeNull();
    expect($announcement->title)->toBe('Pengumuman Uji Coba');
    expect($announcement->file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($announcement->file_path);
});

test('a participant can view published announcements', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    Announcement::create([
        'type' => 'best_paper',
        'title' => 'Pemenang Best Paper',
        'body' => '<p>Selamat kepada pemenang.</p>',
        'published_at' => now(),
    ]);

    $response = $this->actingAs($participant)->get('/participant/announcements');

    $response->assertInertia(fn ($page) => $page
        ->component('Participant/Announcements')
        ->where('announcements.0.title', 'Pemenang Best Paper')
    );
});

test('a non-admin cannot publish an announcement', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $response = $this->actingAs($participant)->post('/admin/announcements', [
        'type' => 'pengumuman',
        'title' => 'Tidak boleh',
        'published_at' => now()->format('Y-m-d'),
    ]);

    $response->assertForbidden();
});
