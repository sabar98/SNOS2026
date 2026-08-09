<?php

use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\Journal;
use App\Models\User;

function makeAcceptedArticleForPublication(): Article
{
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create();

    return Article::factory()->for($registration, 'eventRegistration')->create(['status' => 'diterima']);
}

test('an admin can set publication details for an accepted article', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $article = makeAcceptedArticleForPublication();
    $journal = Journal::factory()->create();

    $response = $this->actingAs($admin)->post("/admin/publications/{$article->id}", [
        'journal_id' => $journal->id,
        'status' => 'terbit',
        'volume' => '12',
        'issue_number' => '3',
        'doi' => '10.1234/snos.2026.001',
        'article_url' => 'https://example.com/artikel',
    ]);

    $response->assertRedirect();
    $article->refresh();
    expect($article->publication)->not->toBeNull();
    expect($article->publication->status)->toBe('terbit');
    expect($article->publication->doi)->toBe('10.1234/snos.2026.001');
    expect($article->publication->published_at)->not->toBeNull();
});

test('publication status stays without a published_at timestamp while still diproses', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $article = makeAcceptedArticleForPublication();
    $journal = Journal::factory()->create();

    $this->actingAs($admin)->post("/admin/publications/{$article->id}", [
        'journal_id' => $journal->id,
        'status' => 'diproses',
    ]);

    $article->refresh();
    expect($article->publication->status)->toBe('diproses');
    expect($article->publication->published_at)->toBeNull();
});

test('a participant sees their publication status on the article detail page', function () {
    $article = makeAcceptedArticleForPublication();
    $journal = Journal::factory()->create(['name' => 'Jurnal Uji Coba']);
    $article->publication()->create([
        'journal_id' => $journal->id,
        'status' => 'terbit',
        'doi' => '10.1234/test',
        'published_at' => now(),
    ]);

    $response = $this->actingAs($article->eventRegistration->user)->get("/participant/articles/{$article->id}");

    $response->assertInertia(fn ($page) => $page
        ->component('Participant/ArticleShow')
        ->where('article.publication.status', 'terbit')
        ->where('article.publication.journal.name', 'Jurnal Uji Coba')
    );
});

test('a non-admin cannot set publication details', function () {
    $article = makeAcceptedArticleForPublication();
    $journal = Journal::factory()->create();

    $response = $this->actingAs($article->eventRegistration->user)->post("/admin/publications/{$article->id}", [
        'journal_id' => $journal->id,
        'status' => 'terbit',
    ]);

    $response->assertForbidden();
});
