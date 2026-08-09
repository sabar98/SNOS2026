<?php

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\EventRegistration;
use App\Models\User;
use App\Services\CertificatePdfGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('an admin can upload a certificate template for a role', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/certificate-templates', [
        'role' => 'narasumber',
        'template_file' => UploadedFile::fake()->image('template-narasumber.png', 1200, 900),
    ]);

    $response->assertRedirect();
    $template = CertificateTemplate::where('role', 'narasumber')->first();
    expect($template)->not->toBeNull();
    Storage::disk('public')->assertExists($template->file_path);
});

test('re-uploading a template for the same role replaces the old file instead of creating a second row', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->post('/admin/certificate-templates', [
        'role' => 'panitia',
        'template_file' => UploadedFile::fake()->image('first.png', 1200, 900),
    ]);
    $firstTemplate = CertificateTemplate::where('role', 'panitia')->first();
    $firstPath = $firstTemplate->file_path;

    $this->actingAs($admin)->post('/admin/certificate-templates', [
        'role' => 'panitia',
        'template_file' => UploadedFile::fake()->image('second.png', 1200, 900),
    ]);

    expect(CertificateTemplate::where('role', 'panitia')->count())->toBe(1);
    $secondTemplate = CertificateTemplate::where('role', 'panitia')->first();
    expect($secondTemplate->id)->toBe($firstTemplate->id);
    expect($secondTemplate->file_path)->not->toBe($firstPath);
    Storage::disk('public')->assertExists($secondTemplate->file_path);
    Storage::disk('public')->assertMissing($firstPath);
});

test('uploading a non-image template file is rejected', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/certificate-templates', [
        'role' => 'peserta',
        'template_file' => UploadedFile::fake()->create('template.pdf', 100, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('template_file');
    expect(CertificateTemplate::where('role', 'peserta')->exists())->toBeFalse();
});

test('an admin can delete a certificate template', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin)->post('/admin/certificate-templates', [
        'role' => 'reviewer',
        'template_file' => UploadedFile::fake()->image('template.png', 1200, 900),
    ]);
    $template = CertificateTemplate::where('role', 'reviewer')->first();

    $response = $this->actingAs($admin)->delete("/admin/certificate-templates/{$template->id}");

    $response->assertRedirect();
    expect(CertificateTemplate::find($template->id))->toBeNull();
    Storage::disk('public')->assertMissing($template->file_path);
});

test('a non-admin cannot manage certificate templates', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->post('/admin/certificate-templates', [
        'role' => 'peserta',
        'template_file' => UploadedFile::fake()->image('template.png', 1200, 900),
    ])->assertForbidden();
});

test('the certificate PDF blade view renders the uploaded template as a full-bleed background', function () {
    $user = User::factory()->create(['name' => 'Nama Uji Template']);
    $registration = EventRegistration::factory()->for($user, 'user')->create();
    $certificate = Certificate::create([
        'user_id' => $user->id,
        'event_registration_id' => $registration->id,
        'certificate_number' => 'CERT-TEST-TEMPLATE',
        'role' => 'moderator',
        'jp_hours' => 8,
        'verification_token' => 'token-template-test',
        'signed_at' => now(),
    ]);
    $certificate->setRelation('user', $user);

    $withTemplate = view('certificates.pdf', [
        'certificate' => $certificate,
        'seminarName' => 'SNOS 2026',
        'roleLabel' => 'Moderator',
        'verificationUrl' => 'http://example.test/verify/token-template-test',
        'qrCodeBase64' => base64_encode('<svg></svg>'),
        'signerName' => 'Dr. Contoh',
        'signerTitle' => 'Ketua Panitia',
        'templateImageBase64' => base64_encode('fake-image-bytes'),
        'templateMime' => 'image/png',
    ])->render();

    expect($withTemplate)->toContain('template-background');
    expect($withTemplate)->toContain('data:image/png;base64,');
    expect($withTemplate)->toContain('Nama Uji Template');

    $withoutTemplate = view('certificates.pdf', [
        'certificate' => $certificate,
        'seminarName' => 'SNOS 2026',
        'roleLabel' => 'Moderator',
        'verificationUrl' => 'http://example.test/verify/token-template-test',
        'qrCodeBase64' => base64_encode('<svg></svg>'),
        'signerName' => 'Dr. Contoh',
        'signerTitle' => 'Ketua Panitia',
        'templateImageBase64' => null,
        'templateMime' => null,
    ])->render();

    expect($withoutTemplate)->not->toContain('template-background');
    expect($withoutTemplate)->toContain('Nama Uji Template');
});

test('generating a certificate for a role with an uploaded template still produces a valid stored PDF', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin)->post('/admin/certificate-templates', [
        'role' => 'peserta',
        'template_file' => UploadedFile::fake()->image('template-peserta.png', 1200, 900),
    ]);

    $user = User::factory()->create();
    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'peserta_umum',
        'status' => 'selesai',
    ]);
    $certificate = Certificate::create([
        'user_id' => $user->id,
        'event_registration_id' => $registration->id,
        'certificate_number' => 'CERT-TEST-GEN',
        'role' => 'peserta',
        'verification_token' => 'token-gen-test',
        'signed_at' => now(),
    ]);

    $path = app(CertificatePdfGenerator::class)->generate($certificate);

    Storage::disk('public')->assertExists($path);
    expect(Storage::disk('public')->size($path))->toBeGreaterThan(0);
});

test('an admin can preview a certificate for a role without creating any records or files', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/certificates/preview?role=peserta&jp_hours=8');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    expect(substr($response->getContent(), 0, 4))->toBe('%PDF');
    expect(Certificate::count())->toBe(0);
    expect(CertificateTemplate::count())->toBe(0);
    expect(Storage::disk('public')->allFiles())->toBe([]);
});

test('an admin can preview a certificate using a real selected participant name', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $user = User::factory()->create(['name' => 'Nama Pratinjau Uji']);
    $registration = EventRegistration::factory()->for($user, 'user')->create(['status' => 'selesai']);

    $response = $this->actingAs($admin)->get("/admin/certificates/preview?role=peserta&event_registration_id={$registration->id}");

    $response->assertOk();
    expect(substr($response->getContent(), 0, 4))->toBe('%PDF');
});

test('a preview reflects the template uploaded for that role, distinct from the default design', function () {
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $beforeUpload = app(CertificatePdfGenerator::class)->preview('peserta', 'Nama Contoh');

    $this->actingAs($admin)->post('/admin/certificate-templates', [
        'role' => 'peserta',
        'template_file' => UploadedFile::fake()->image('template-peserta.png', 1200, 900),
    ]);

    $afterUpload = app(CertificatePdfGenerator::class)->preview('peserta', 'Nama Contoh');

    expect($afterUpload)->not->toBe($beforeUpload);
});

test('previewing with an invalid role is rejected', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get('/admin/certificates/preview?role=not-a-role')->assertSessionHasErrors('role');
});

test('a non-admin cannot preview certificates', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->get('/admin/certificates/preview?role=peserta')->assertForbidden();
});
