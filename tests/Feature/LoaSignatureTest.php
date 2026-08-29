<?php

use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\LetterOfAcceptance;
use App\Models\LoaSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeReviewedArticleForLoaSignature(): Article
{
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create([
        'status' => 'sedang_direview',
    ]);

    return Article::factory()->for($registration, 'eventRegistration')->create([
        'status' => 'proses_review',
    ]);
}

test('an admin can view the LoA signature page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/loa-settings');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/LoaSettings')
        ->has('setting')
    );
});

test('an admin can upload a LoA signature', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/loa-settings', [
        'signature' => UploadedFile::fake()->image('signature.png', 400, 150),
    ]);

    $response->assertRedirect();
    $setting = LoaSetting::current();
    expect($setting->signature_path)->not->toBeNull();
    Storage::disk('public')->assertExists($setting->signature_path);
});

test('re-uploading a LoA signature replaces the old file', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->post('/admin/loa-settings', [
        'signature' => UploadedFile::fake()->image('first.png', 400, 150),
    ]);
    $firstPath = LoaSetting::current()->signature_path;

    $this->actingAs($admin)->post('/admin/loa-settings', [
        'signature' => UploadedFile::fake()->image('second.png', 400, 150),
    ]);
    $secondPath = LoaSetting::current()->signature_path;

    expect($secondPath)->not->toBe($firstPath);
    Storage::disk('public')->assertExists($secondPath);
    Storage::disk('public')->assertMissing($firstPath);
});

test('uploading a non-image signature file is rejected', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/loa-settings', [
        'signature' => UploadedFile::fake()->create('signature.pdf', 100, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('signature');
    expect(LoaSetting::current()->signature_path)->toBeNull();
});

test('an admin can remove the LoA signature', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin)->post('/admin/loa-settings', [
        'signature' => UploadedFile::fake()->image('signature.png', 400, 150),
    ]);
    $path = LoaSetting::current()->signature_path;

    $response = $this->actingAs($admin)->delete('/admin/loa-settings');

    $response->assertRedirect();
    expect(LoaSetting::current()->signature_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('a non-admin cannot manage the LoA signature', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->get('/admin/loa-settings')->assertForbidden();
    $this->actingAs($participant)->post('/admin/loa-settings', [
        'signature' => UploadedFile::fake()->image('signature.png', 400, 150),
    ])->assertForbidden();
    $this->actingAs($participant)->delete('/admin/loa-settings')->assertForbidden();
});

test('the LoA PDF blade view renders the uploaded signature instead of the stylized text mark', function () {
    $sharedData = [
        'article' => Article::factory()->make(['title' => 'Judul Uji', 'article_number' => 'ART-TEST-1']),
        'seminarName' => 'SNOS 2026',
        'seminarDateRange' => '1-2 Januari 2027',
        'seminarLocation' => 'Gedung Uji',
        'participantName' => 'Nama Uji Signature',
        'journalName' => null,
        'signerName' => 'Dr. Contoh',
        'signerTitle' => 'Ketua Panitia',
    ];

    $withSignature = view('loa.pdf', $sharedData + [
        'loa' => new LetterOfAcceptance(['loa_number' => 'LOA-TEST-1', 'issued_at' => now()]),
        'signatureBase64' => base64_encode('fake-signature-bytes'),
        'signatureMime' => 'image/png',
    ])->render();

    expect($withSignature)->toContain('class="signature-image"');
    expect($withSignature)->toContain('data:image/png;base64,');
    expect($withSignature)->not->toContain('class="signature-mark"');

    $withoutSignature = view('loa.pdf', $sharedData + [
        'loa' => new LetterOfAcceptance(['loa_number' => 'LOA-TEST-2', 'issued_at' => now()]),
        'signatureBase64' => null,
        'signatureMime' => null,
    ])->render();

    expect($withoutSignature)->toContain('class="signature-mark"');
    expect($withoutSignature)->not->toContain('class="signature-image"');
});

test('issuing a LoA after uploading a signature still produces a valid stored PDF', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin)->post('/admin/loa-settings', [
        'signature' => UploadedFile::fake()->image('signature.png', 400, 150),
    ]);

    $article = makeReviewedArticleForLoaSignature();

    $this->actingAs($admin)->post("/admin/articles/{$article->id}/loa");

    $article->refresh()->load('letterOfAcceptance');
    expect($article->letterOfAcceptance)->not->toBeNull();
    Storage::disk('public')->assertExists($article->letterOfAcceptance->file_path);
    expect(Storage::disk('public')->size($article->letterOfAcceptance->file_path))->toBeGreaterThan(0);
});
