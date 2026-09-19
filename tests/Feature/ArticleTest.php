<?php

use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

function makePresenterRegistration(?User $user = null, bool $paymentVerified = true): EventRegistration
{
    $user ??= User::factory()->create();
    $user->assignRole('peserta');

    $registration = EventRegistration::factory()->for($user, 'user')->create([
        'participant_type' => 'presenter_luring',
        'status' => $paymentVerified ? 'pembayaran_terverifikasi' : 'menunggu_verifikasi',
    ]);

    if ($paymentVerified) {
        $registration->payments()->create([
            'type' => 'registrasi',
            'amount' => 750000,
            'payment_code' => 'PAY-TEST-'.$registration->id,
            'status' => 'terverifikasi',
        ]);
    }

    return $registration;
}

test('a presenter can submit an article once payment is verified', function () {
    Storage::fake('public');
    $registration = makePresenterRegistration();

    $response = $this->actingAs($registration->user)->post("/participant/registrations/{$registration->id}/articles", [
        'title' => 'Optimasi Sistem Antrian Berbasis AI',
        'abstract' => 'Penelitian ini membahas optimasi sistem antrian.',
        'keywords' => 'antrian, optimasi, AI',
        'field' => 'Teknologi Informasi',
        'file' => UploadedFile::fake()->create('artikel.pdf', 200, 'application/pdf'),
        'statement_letter' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        'authors' => [
            ['name' => $registration->user->name, 'email' => $registration->user->email, 'is_corresponding' => true],
        ],
    ]);

    $response->assertRedirect(route('participant.registrations.show', $registration));
    $article = Article::where('event_registration_id', $registration->id)->first();
    expect($article)->not->toBeNull();
    expect($article->status)->toBe('diajukan');
    expect($article->authors)->toHaveCount(1);
    $registration->refresh();
    expect($registration->status)->toBe('artikel_diajukan');
});

test('a presenter cannot submit an article after the submission deadline has passed', function () {
    Storage::fake('public');
    $registration = makePresenterRegistration();
    $this->travelTo(Carbon::parse(config('seminar.article_submission_deadline'))->addDay());

    $response = $this->actingAs($registration->user)->post("/participant/registrations/{$registration->id}/articles", [
        'title' => 'Judul',
        'abstract' => 'Abstrak',
        'keywords' => 'kata kunci',
        'field' => 'Teknologi Informasi',
        'file' => UploadedFile::fake()->create('artikel.pdf', 200, 'application/pdf'),
        'statement_letter' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        'authors' => [['name' => 'A', 'email' => 'a@example.com']],
    ]);

    $response->assertStatus(422);
    expect(Article::where('event_registration_id', $registration->id)->count())->toBe(0);

    $this->travelBack();
});

test('a presenter can submit an article even before payment is verified', function () {
    // Explicit product decision: article submission is not gated on payment
    // status — a participant may upload their article regardless of whether
    // the registration fee has been paid or verified yet.
    Storage::fake('public');
    $registration = makePresenterRegistration(paymentVerified: false);

    $response = $this->actingAs($registration->user)->post("/participant/registrations/{$registration->id}/articles", [
        'title' => 'Judul',
        'abstract' => 'Abstrak',
        'keywords' => 'kata kunci',
        'field' => 'Teknologi Informasi',
        'file' => UploadedFile::fake()->create('artikel.pdf', 200, 'application/pdf'),
        'statement_letter' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
        'authors' => [['name' => 'A', 'email' => 'a@example.com']],
    ]);

    $response->assertRedirect(route('participant.registrations.show', $registration));
    expect(Article::where('event_registration_id', $registration->id)->count())->toBe(1);
});

test('the article upload page and form are accessible regardless of payment status', function () {
    $registration = makePresenterRegistration(paymentVerified: false);

    $this->actingAs($registration->user)
        ->get("/participant/registrations/{$registration->id}/articles/create")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Participant/ArticleCreate'));
});

test('an admin can approve an article for review', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $registration = makePresenterRegistration();
    $article = Article::factory()->for($registration, 'eventRegistration')->create(['status' => 'diajukan']);

    $response = $this->actingAs($admin)->put("/admin/articles/{$article->id}", [
        'decision' => 'proses_review',
        'similarity_score' => 8,
    ]);

    $response->assertRedirect();
    $article->refresh();
    $registration->refresh();
    expect($article->status)->toBe('proses_review');
    expect((float) $article->similarity_score)->toBe(8.0);
    expect($registration->status)->toBe('sedang_direview');
});

test('an admin can reject an article at the administration stage', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $registration = makePresenterRegistration();
    $article = Article::factory()->for($registration, 'eventRegistration')->create(['status' => 'diajukan']);

    $response = $this->actingAs($admin)->put("/admin/articles/{$article->id}", [
        'decision' => 'ditolak_administrasi',
        'admin_notes' => 'Tidak sesuai template.',
    ]);

    $response->assertRedirect();
    $article->refresh();
    expect($article->status)->toBe('ditolak_administrasi');
    expect($article->admin_notes)->toBe('Tidak sesuai template.');
});
