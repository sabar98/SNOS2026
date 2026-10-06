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

function makeLoaSettingsAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

test('an admin can view the LoA signer settings page', function () {
    $admin = makeLoaSettingsAdmin();

    $response = $this->actingAs($admin)->get('/admin/loa-settings');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/LoaSettings')
        ->has('setting')
    );
});

test('an admin can save the LoA signer name, title and signature together', function () {
    Storage::fake('public');
    $admin = makeLoaSettingsAdmin();

    $response = $this->actingAs($admin)->post('/admin/loa-settings', [
        'signer_name' => 'Prof. Dr. Pimpinan Uji, M.T.',
        'signer_title' => 'Ketua Panitia SNOS 2026',
        'signature' => UploadedFile::fake()->image('signature.png', 400, 150),
    ]);

    $response->assertRedirect();
    $setting = LoaSetting::current();
    expect($setting->signer_name)->toBe('Prof. Dr. Pimpinan Uji, M.T.');
    expect($setting->signer_title)->toBe('Ketua Panitia SNOS 2026');
    expect($setting->signature_path)->not->toBeNull();
    Storage::disk('public')->assertExists($setting->signature_path);
});

test('the signer name is required', function () {
    Storage::fake('public');
    $admin = makeLoaSettingsAdmin();

    $response = $this->actingAs($admin)->post('/admin/loa-settings', [
        'signer_name' => '',
    ]);

    $response->assertSessionHasErrors('signer_name');
    expect(LoaSetting::current()->signer_name)->toBeNull();
});

test('updating the name without a new file keeps the existing signature', function () {
    Storage::fake('public');
    $admin = makeLoaSettingsAdmin();

    $this->actingAs($admin)->post('/admin/loa-settings', [
        'signer_name' => 'Nama Lama',
        'signature' => UploadedFile::fake()->image('signature.png', 400, 150),
    ]);
    $signaturePath = LoaSetting::current()->signature_path;

    $this->actingAs($admin)->post('/admin/loa-settings', [
        'signer_name' => 'Nama Baru',
        'signature' => null,
    ]);

    $setting = LoaSetting::current();
    expect($setting->signer_name)->toBe('Nama Baru');
    expect($setting->signature_path)->toBe($signaturePath);
    Storage::disk('public')->assertExists($signaturePath);
});

test('re-uploading a LoA signature replaces the old file', function () {
    Storage::fake('public');
    $admin = makeLoaSettingsAdmin();

    $this->actingAs($admin)->post('/admin/loa-settings', [
        'signer_name' => 'Pimpinan Uji',
        'signature' => UploadedFile::fake()->image('first.png', 400, 150),
    ]);
    $firstPath = LoaSetting::current()->signature_path;

    $this->actingAs($admin)->post('/admin/loa-settings', [
        'signer_name' => 'Pimpinan Uji',
        'signature' => UploadedFile::fake()->image('second.png', 400, 150),
    ]);
    $secondPath = LoaSetting::current()->signature_path;

    expect($secondPath)->not->toBe($firstPath);
    Storage::disk('public')->assertExists($secondPath);
    Storage::disk('public')->assertMissing($firstPath);
});

test('uploading a non-image signature file is rejected', function () {
    Storage::fake('public');
    $admin = makeLoaSettingsAdmin();

    $response = $this->actingAs($admin)->post('/admin/loa-settings', [
        'signer_name' => 'Pimpinan Uji',
        'signature' => UploadedFile::fake()->create('signature.pdf', 100, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('signature');
    expect(LoaSetting::current()->signature_path)->toBeNull();
});

test('an admin can remove the LoA signature', function () {
    Storage::fake('public');
    $admin = makeLoaSettingsAdmin();
    $this->actingAs($admin)->post('/admin/loa-settings', [
        'signer_name' => 'Pimpinan Uji',
        'signature' => UploadedFile::fake()->image('signature.png', 400, 150),
    ]);
    $path = LoaSetting::current()->signature_path;

    $response = $this->actingAs($admin)->delete('/admin/loa-settings');

    $response->assertRedirect();
    expect(LoaSetting::current()->signature_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('a non-admin cannot manage the LoA signer settings', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->get('/admin/loa-settings')->assertForbidden();
    $this->actingAs($participant)->post('/admin/loa-settings', [
        'signer_name' => 'Pimpinan Uji',
        'signature' => UploadedFile::fake()->image('signature.png', 400, 150),
    ])->assertForbidden();
    $this->actingAs($participant)->delete('/admin/loa-settings')->assertForbidden();
});

test('the LoA PDF blade view renders the uploaded signature image only, with no stylized text fallback', function () {
    $sharedData = [
        'article' => Article::factory()->make(['title' => 'Judul Uji', 'article_number' => 'ART-TEST-1']),
        'seminarName' => 'SNOS 2026',
        'seminarDateRange' => '1-2 Januari 2027',
        'seminarLocation' => 'Gedung Uji',
        'participantName' => 'Nama Uji Signature',
        'journalName' => null,
        'signerName' => 'Dr. Contoh',
        'signerTitle' => 'Ketua Panitia',
        'letterheadBase64' => base64_encode('fake-letterhead-bytes'),
    ];

    $withSignature = view('loa.pdf', $sharedData + [
        'loa' => new LetterOfAcceptance(['loa_number' => 'LOA-TEST-1', 'issued_at' => now()]),
        'signatureBase64' => base64_encode('fake-signature-bytes'),
        'signatureMime' => 'image/png',
    ])->render();

    expect($withSignature)->toContain('class="signature-image"');
    expect($withSignature)->toContain('data:image/png;base64,');

    $withoutSignature = view('loa.pdf', $sharedData + [
        'loa' => new LetterOfAcceptance(['loa_number' => 'LOA-TEST-2', 'issued_at' => now()]),
        'signatureBase64' => null,
        'signatureMime' => null,
    ])->render();

    // No signature uploaded yet: no image, and no stylized text standing in for one either
    // — only a real uploaded signature is ever rendered as "the signature".
    expect($withoutSignature)->not->toContain('class="signature-image"');
    expect($withoutSignature)->not->toContain('class="signature-mark"');
});

test('the LoA PDF blade view prints the saved signer name and title under the signature', function () {
    $html = view('loa.pdf', [
        'loa' => new LetterOfAcceptance(['loa_number' => 'LOA-TEST-3', 'issued_at' => now()]),
        'article' => Article::factory()->make(['title' => 'Judul Uji', 'article_number' => 'ART-TEST-3']),
        'seminarName' => 'SNOS 2026',
        'participantName' => 'Nama Uji',
        'journalName' => null,
        'signerName' => 'Prof. Dr. Pimpinan Tersimpan',
        'signerTitle' => 'Ketua Panitia Tersimpan',
        'signatureBase64' => null,
        'signatureMime' => null,
        'letterheadBase64' => base64_encode('fake-letterhead-bytes'),
    ])->render();

    expect($html)->toContain('Prof. Dr. Pimpinan Tersimpan');
    expect($html)->toContain('Ketua Panitia Tersimpan');
});

test('issuing a LoA after uploading a signature still produces a valid stored PDF', function () {
    Storage::fake('public');
    $admin = makeLoaSettingsAdmin();
    $this->actingAs($admin)->post('/admin/loa-settings', [
        'signer_name' => 'Pimpinan Uji',
        'signature' => UploadedFile::fake()->image('signature.png', 400, 150),
    ]);

    $article = makeReviewedArticleForLoaSignature();

    $this->actingAs($admin)->post("/admin/articles/{$article->id}/loa");

    $article->refresh()->load('letterOfAcceptance');
    expect($article->letterOfAcceptance)->not->toBeNull();
    Storage::disk('public')->assertExists($article->letterOfAcceptance->file_path);
    expect(Storage::disk('public')->size($article->letterOfAcceptance->file_path))->toBeGreaterThan(0);
});
