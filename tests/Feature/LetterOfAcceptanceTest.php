<?php

use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function makeReviewedArticleForLoa(?Journal $journal = null): Article
{
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create([
        'status' => 'sedang_direview',
    ]);

    return Article::factory()->for($registration, 'eventRegistration')->create([
        'status' => 'proses_review',
        'journal_id' => $journal?->id,
    ]);
}

test('issuing a LoA generates a downloadable PDF and advances statuses', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $article = makeReviewedArticleForLoa();

    $response = $this->actingAs($admin)->post("/admin/articles/{$article->id}/loa");

    $response->assertRedirect();
    $article->refresh()->load('letterOfAcceptance', 'eventRegistration');
    expect($article->status)->toBe('diterima');
    expect($article->article_number)->not->toBeNull();
    expect($article->letterOfAcceptance)->not->toBeNull();
    expect($article->letterOfAcceptance->file_path)->not->toBeNull();
    Storage::disk('public')->assertExists($article->letterOfAcceptance->file_path);
    expect($article->eventRegistration->status)->toBe('artikel_diterima');
});

test('issuing a LoA for an article whose journal has a publication fee creates a payment', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $journal = Journal::factory()->create(['publication_fee' => 250000]);
    $article = makeReviewedArticleForLoa($journal);

    $this->actingAs($admin)->post("/admin/articles/{$article->id}/loa");

    $article->refresh()->load('payments');
    expect($article->payments)->toHaveCount(1);
    expect($article->payments->first()->type)->toBe('publikasi');
    expect((float) $article->payments->first()->amount)->toBe(250000.0);
    expect($article->payments->first()->status)->toBe('belum_bayar');
});

test('issuing a LoA for a journal without a publication fee creates no payment', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $journal = Journal::factory()->create(['publication_fee' => 0]);
    $article = makeReviewedArticleForLoa($journal);

    $this->actingAs($admin)->post("/admin/articles/{$article->id}/loa");

    expect($article->refresh()->payments)->toHaveCount(0);
});

test('a participant can see and download their LoA and pay the publication fee', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $journal = Journal::factory()->create(['publication_fee' => 100000]);
    $article = makeReviewedArticleForLoa($journal);

    $this->actingAs($admin)->post("/admin/articles/{$article->id}/loa");

    $participant = $article->eventRegistration->user;
    $response = $this->actingAs($participant)->get("/participant/articles/{$article->id}");

    $response->assertInertia(fn ($page) => $page
        ->component('Participant/ArticleShow')
        ->where('article.letter_of_acceptance.file_path', fn (?string $path) => $path !== null)
        ->where('article.payments.0.amount', '100000.00')
    );
});

test('issuing a LoA twice for the same article is rejected', function () {
    Storage::fake('public');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $article = makeReviewedArticleForLoa();

    $this->actingAs($admin)->post("/admin/articles/{$article->id}/loa");
    $response = $this->actingAs($admin)->post("/admin/articles/{$article->id}/loa");

    $response->assertStatus(422);
});
