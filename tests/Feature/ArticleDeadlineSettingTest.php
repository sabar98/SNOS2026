<?php

use App\Models\ArticleDeadlineSetting;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Support\Carbon;

function makeDeadlineAdmin(): User
{
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

test('an admin can view the deadline settings page, seeded from the config defaults', function () {
    $admin = makeDeadlineAdmin();

    $response = $this->actingAs($admin)->get('/admin/deadline-settings');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/DeadlineSettings')
        ->where(
            'setting.article_submission_deadline',
            Carbon::parse(config('seminar.article_submission_deadline'))->format('Y-m-d\TH:i'),
        )
        ->where(
            'setting.presentation_material_deadline',
            Carbon::parse(config('seminar.presentation_material_deadline'))->format('Y-m-d\TH:i'),
        )
    );
});

test('an admin can change the article and presentation material deadlines', function () {
    $admin = makeDeadlineAdmin();

    $response = $this->actingAs($admin)->put('/admin/deadline-settings', [
        'article_submission_deadline' => '2026-12-01T23:59',
        'presentation_material_deadline' => '2026-12-15T23:59',
    ]);

    $response->assertRedirect();
    $setting = ArticleDeadlineSetting::current();
    expect($setting->article_submission_deadline->format('Y-m-d H:i'))->toBe('2026-12-01 23:59');
    expect($setting->presentation_material_deadline->format('Y-m-d H:i'))->toBe('2026-12-15 23:59');
});

test('both deadline fields are required', function () {
    $admin = makeDeadlineAdmin();

    $response = $this->actingAs($admin)->put('/admin/deadline-settings', [
        'article_submission_deadline' => '',
        'presentation_material_deadline' => '',
    ]);

    $response->assertSessionHasErrors(['article_submission_deadline', 'presentation_material_deadline']);
});

test('a non-admin cannot view or change the deadline settings', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');

    $this->actingAs($participant)->get('/admin/deadline-settings')->assertForbidden();
    $this->actingAs($participant)->put('/admin/deadline-settings', [
        'article_submission_deadline' => '2026-12-01T23:59',
        'presentation_material_deadline' => '2026-12-15T23:59',
    ])->assertForbidden();
});

test('changing the article submission deadline in the admin form actually moves the participant-facing deadline', function () {
    $admin = makeDeadlineAdmin();

    // Close submissions as of yesterday.
    $this->actingAs($admin)->put('/admin/deadline-settings', [
        'article_submission_deadline' => now()->subDay()->format('Y-m-d\TH:i'),
        'presentation_material_deadline' => now()->addMonth()->format('Y-m-d\TH:i'),
    ]);

    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create();

    $response = $this->actingAs($participant)->get("/participant/registrations/{$registration->id}/articles/create");

    $response->assertInertia(fn ($page) => $page->where('deadlinePassed', true));
});
