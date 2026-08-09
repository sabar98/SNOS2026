<?php

use App\Models\Article;
use App\Models\ArticleReviewer;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function makeReviewableArticle(): Article
{
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create([
        'participant_type' => 'presenter_luring',
        'status' => 'sedang_direview',
    ]);

    return Article::factory()->for($registration, 'eventRegistration')->create(['status' => 'proses_review']);
}

test('an admin can assign a reviewer to an article', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $reviewer = User::factory()->create();
    $reviewer->assignRole('reviewer');
    $article = makeReviewableArticle();

    $response = $this->actingAs($admin)->post('/admin/reviewer-assignments', [
        'article_id' => $article->id,
        'reviewer_id' => $reviewer->id,
    ]);

    $response->assertRedirect();
    expect(ArticleReviewer::where('article_id', $article->id)->where('reviewer_id', $reviewer->id)->exists())->toBeTrue();
    $article->refresh();
    expect($article->status)->toBe('sedang_direview');
});

test('a reviewer can accept an article outright', function () {
    $article = makeReviewableArticle();
    $reviewer = User::factory()->create();
    $reviewer->assignRole('reviewer');
    $assignment = ArticleReviewer::create([
        'article_id' => $article->id,
        'reviewer_id' => $reviewer->id,
        'status' => 'ditugaskan',
    ]);

    $response = $this->actingAs($reviewer)->post("/reviewer/articles/{$assignment->id}/review", [
        'theme_suitability_score' => 5,
        'novelty_score' => 5,
        'methodology_score' => 5,
        'results_discussion_score' => 5,
        'reference_quality_score' => 5,
        'language_grammar_score' => 5,
        'recommendation' => 'diterima_tanpa_revisi',
    ]);

    $response->assertRedirect(route('reviewer.articles.index'));
    $article->refresh();
    expect($article->status)->toBe('diterima');
});

test('a reviewer requesting minor revision routes the article back to the participant', function () {
    $article = makeReviewableArticle();
    $reviewer = User::factory()->create();
    $reviewer->assignRole('reviewer');
    $assignment = ArticleReviewer::create([
        'article_id' => $article->id,
        'reviewer_id' => $reviewer->id,
        'status' => 'ditugaskan',
    ]);

    $this->actingAs($reviewer)->post("/reviewer/articles/{$assignment->id}/review", [
        'theme_suitability_score' => 4,
        'novelty_score' => 3,
        'methodology_score' => 3,
        'results_discussion_score' => 3,
        'reference_quality_score' => 4,
        'language_grammar_score' => 3,
        'comments' => 'Perbaiki metodologi.',
        'recommendation' => 'diterima_revisi_minor',
    ]);

    $article->refresh();
    $registration = $article->eventRegistration;
    $registration->refresh();
    expect($article->status)->toBe('revisi_minor');
    expect($registration->status)->toBe('perlu_revisi');

    Storage::fake('public');
    $response = $this->actingAs($registration->user)->post("/participant/articles/{$article->id}/revisions", [
        'file' => UploadedFile::fake()->create('revisi.pdf', 100, 'application/pdf'),
        'response_to_reviewer' => 'Metodologi telah diperbaiki.',
    ]);

    $response->assertRedirect();
    $article->refresh();
    expect($article->status)->toBe('sedang_direview');
    expect($article->revisions)->toHaveCount(1);
    expect($article->revisions->first()->version_number)->toBe(1);
});

test('a reviewer cannot review an article assigned to someone else', function () {
    $article = makeReviewableArticle();
    $actualReviewer = User::factory()->create();
    $actualReviewer->assignRole('reviewer');
    $otherReviewer = User::factory()->create();
    $otherReviewer->assignRole('reviewer');
    $assignment = ArticleReviewer::create([
        'article_id' => $article->id,
        'reviewer_id' => $actualReviewer->id,
        'status' => 'ditugaskan',
    ]);

    $response = $this->actingAs($otherReviewer)->post("/reviewer/articles/{$assignment->id}/review", [
        'theme_suitability_score' => 5,
        'novelty_score' => 5,
        'methodology_score' => 5,
        'results_discussion_score' => 5,
        'reference_quality_score' => 5,
        'language_grammar_score' => 5,
        'recommendation' => 'diterima_tanpa_revisi',
    ]);

    $response->assertForbidden();
});
