<?php

use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

function makeAcceptedArticleForMaterial(): Article
{
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create([
        'participant_type' => 'presenter_luring',
    ]);

    return Article::factory()->for($registration, 'eventRegistration')->create(['status' => 'diterima']);
}

test('a presenter can upload presentation material for an accepted article', function () {
    Storage::fake('public');
    $article = makeAcceptedArticleForMaterial();

    $response = $this->actingAs($article->eventRegistration->user)->post("/participant/articles/{$article->id}/presentation-material", [
        'slide' => UploadedFile::fake()->create('slide.pptx', 500),
        'short_bio' => 'Peneliti bidang teknologi informasi.',
        'official_photo' => UploadedFile::fake()->image('foto.jpg'),
        'consent' => true,
    ]);

    $response->assertRedirect();
    $article->refresh();
    expect($article->presentationMaterial)->not->toBeNull();
    expect($article->presentationMaterial->short_bio)->toBe('Peneliti bidang teknologi informasi.');
    expect($article->presentationMaterial->consent_confirmed_at)->not->toBeNull();
    Storage::disk('public')->assertExists($article->presentationMaterial->slide_path);
    Storage::disk('public')->assertExists($article->presentationMaterial->official_photo_path);
});

test('updating presentation material keeps the existing slide file if a new one is not uploaded', function () {
    Storage::fake('public');
    $article = makeAcceptedArticleForMaterial();
    $user = $article->eventRegistration->user;

    $this->actingAs($user)->post("/participant/articles/{$article->id}/presentation-material", [
        'slide' => UploadedFile::fake()->create('slide.pptx', 500),
        'short_bio' => 'Bio awal',
        'official_photo' => UploadedFile::fake()->image('foto.jpg'),
        'consent' => true,
    ]);
    $article->refresh();
    $originalSlidePath = $article->presentationMaterial->slide_path;

    $this->actingAs($user)->post("/participant/articles/{$article->id}/presentation-material", [
        'short_bio' => 'Bio diperbarui',
        'consent' => true,
    ]);

    $article->refresh();
    expect($article->presentationMaterial->slide_path)->toBe($originalSlidePath);
    expect($article->presentationMaterial->short_bio)->toBe('Bio diperbarui');
});

test('a presenter cannot upload presentation material for someone else\'s article', function () {
    $article = makeAcceptedArticleForMaterial();
    $intruder = User::factory()->create();
    $intruder->assignRole('peserta');

    $response = $this->actingAs($intruder)->post("/participant/articles/{$article->id}/presentation-material", [
        'short_bio' => 'Bio',
        'consent' => true,
    ]);

    $response->assertForbidden();
});

test('a presenter cannot upload presentation material after the deadline has passed', function () {
    $article = makeAcceptedArticleForMaterial();
    $this->travelTo(Carbon::parse(config('seminar.presentation_material_deadline'))->addDay());

    $response = $this->actingAs($article->eventRegistration->user)->post("/participant/articles/{$article->id}/presentation-material", [
        'short_bio' => 'Bio',
        'consent' => true,
    ]);

    $response->assertStatus(422);
    expect($article->fresh()->presentationMaterial)->toBeNull();

    $this->travelBack();
});

test('presentation material requires a slide file and official photo on first submission', function () {
    $article = makeAcceptedArticleForMaterial();

    $response = $this->actingAs($article->eventRegistration->user)->post("/participant/articles/{$article->id}/presentation-material", [
        'short_bio' => 'Bio',
        'consent' => true,
    ]);

    $response->assertSessionHasErrors(['slide', 'official_photo']);
});

test('a presenter can upload each presentation material file independently across separate requests', function () {
    Storage::fake('public');
    $article = makeAcceptedArticleForMaterial();
    $user = $article->eventRegistration->user;
    $endpoint = "/participant/articles/{$article->id}/presentation-material";

    $this->actingAs($user)->post($endpoint, ['slide' => UploadedFile::fake()->create('slide.pptx', 500)])->assertRedirect();
    $this->actingAs($user)->post($endpoint, ['video' => UploadedFile::fake()->create('video.mp4', 1000, 'video/mp4')])->assertRedirect();
    $this->actingAs($user)->post($endpoint, ['official_photo' => UploadedFile::fake()->image('foto.jpg')])->assertRedirect();
    $this->actingAs($user)->post($endpoint, ['short_bio' => 'Bio bertahap', 'consent' => true])->assertRedirect();

    $article->refresh();
    $material = $article->presentationMaterial;
    expect($material)->not->toBeNull();
    expect($material->short_bio)->toBe('Bio bertahap');
    expect($material->consent_confirmed_at)->not->toBeNull();
    Storage::disk('public')->assertExists($material->slide_path);
    Storage::disk('public')->assertExists($material->video_path);
    Storage::disk('public')->assertExists($material->official_photo_path);
});

test('a presenter cannot confirm consent without a short bio', function () {
    Storage::fake('public');
    $article = makeAcceptedArticleForMaterial();
    $user = $article->eventRegistration->user;
    $endpoint = "/participant/articles/{$article->id}/presentation-material";

    $this->actingAs($user)->post($endpoint, ['slide' => UploadedFile::fake()->create('slide.pptx', 500)]);
    $this->actingAs($user)->post($endpoint, ['official_photo' => UploadedFile::fake()->image('foto.jpg')]);

    $response = $this->actingAs($user)->post($endpoint, ['consent' => true]);

    $response->assertSessionHasErrors(['short_bio']);
    expect($article->fresh()->presentationMaterial->consent_confirmed_at)->toBeNull();
});
