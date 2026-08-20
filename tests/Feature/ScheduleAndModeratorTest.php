<?php

use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\PresentationSlot;
use App\Models\ScheduleSession;
use App\Models\User;

function makeAcceptedArticle(): Article
{
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create([
        'participant_type' => 'presenter_luring',
        'status' => 'artikel_diterima',
    ]);

    return Article::factory()->for($registration, 'eventRegistration')->create(['status' => 'diterima']);
}

test('an admin can create a schedule session and add an accepted article as a slot', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');
    $article = makeAcceptedArticle();

    $sessionResponse = $this->actingAs($admin)->post('/admin/schedule-sessions', [
        'session_number' => 'A1',
        'room' => 'Ruang 101',
        'moderator_id' => $moderator->id,
        'date' => '2026-11-12',
        'start_time' => '09:00',
        'end_time' => '09:30',
        'zoom_link' => 'https://zoom.us/j/123456789',
        'zoom_meeting_id' => '123 456 789',
        'zoom_password' => 'snos2026',
    ]);
    $sessionResponse->assertRedirect();

    $session = ScheduleSession::first();
    expect($session->session_number)->toBe('A1');
    expect($session->zoom_meeting_id)->toBe('123 456 789');
    expect($session->zoom_password)->toBe('snos2026');

    $slotResponse = $this->actingAs($admin)->post("/admin/schedule-sessions/{$session->id}/slots", [
        'article_id' => $article->id,
        'order' => 1,
    ]);
    $slotResponse->assertRedirect();

    $slot = PresentationSlot::where('article_id', $article->id)->first();
    expect($slot)->not->toBeNull();
    $article->eventRegistration->refresh();
    expect($article->eventRegistration->status)->toBe('jadwal_ditetapkan');
});

test('an admin can update a schedule session', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $session = ScheduleSession::factory()->create(['session_number' => 'A1', 'room' => 'Ruang Lama']);

    $response = $this->actingAs($admin)->put("/admin/schedule-sessions/{$session->id}", [
        'session_number' => 'A2',
        'room' => 'Ruang Baru',
        'date' => '2026-11-13',
        'start_time' => '10:00',
        'end_time' => '10:30',
    ]);

    $response->assertRedirect();
    $session->refresh();
    expect($session->session_number)->toBe('A2');
    expect($session->room)->toBe('Ruang Baru');
});

test('an admin can delete a schedule session', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $session = ScheduleSession::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/schedule-sessions/{$session->id}");

    $response->assertRedirect();
    expect(ScheduleSession::find($session->id))->toBeNull();
});

test('an admin can remove a presentation slot from a session', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $article = makeAcceptedArticle();
    $session = ScheduleSession::factory()->create();
    $slot = PresentationSlot::factory()->create([
        'schedule_session_id' => $session->id,
        'article_id' => $article->id,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/slots/{$slot->id}");

    $response->assertRedirect();
    expect(PresentationSlot::find($slot->id))->toBeNull();
});

test('a non-admin cannot manage schedule sessions', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $session = ScheduleSession::factory()->create();

    $this->actingAs($participant)->post('/admin/schedule-sessions', [
        'session_number' => 'X1',
        'date' => '2026-11-12',
        'start_time' => '09:00',
        'end_time' => '09:30',
    ])->assertForbidden();

    $this->actingAs($participant)->put("/admin/schedule-sessions/{$session->id}", [
        'session_number' => 'X1',
        'date' => '2026-11-12',
        'start_time' => '09:00',
        'end_time' => '09:30',
    ])->assertForbidden();

    $this->actingAs($participant)->delete("/admin/schedule-sessions/{$session->id}")->assertForbidden();
});

test('a moderator can record attendance and submit an assessment for their own session', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');
    $article = makeAcceptedArticle();
    $session = ScheduleSession::factory()->create(['moderator_id' => $moderator->id]);
    $slot = PresentationSlot::factory()->create([
        'schedule_session_id' => $session->id,
        'article_id' => $article->id,
        'status' => 'dijadwalkan',
    ]);

    $attendanceResponse = $this->actingAs($moderator)->putJson("/moderator/slots/{$slot->id}/attendance", [
        'status' => 'hadir',
    ]);
    $attendanceResponse->assertRedirect();
    $slot->refresh();
    expect($slot->status)->toBe('hadir');

    $assessmentResponse = $this->actingAs($moderator)->post("/moderator/slots/{$slot->id}/assessment", [
        'mastery_score' => 5,
        'presentation_quality_score' => 5,
        'timeliness_score' => 4,
        'qa_score' => 5,
        'content_alignment_score' => 5,
    ]);
    $assessmentResponse->assertRedirect();
    expect($slot->assessments()->count())->toBe(1);
});

test('a moderator can record execution notes alongside attendance status', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');
    $article = makeAcceptedArticle();
    $session = ScheduleSession::factory()->create(['moderator_id' => $moderator->id]);
    $slot = PresentationSlot::factory()->create([
        'schedule_session_id' => $session->id,
        'article_id' => $article->id,
        'status' => 'dijadwalkan',
    ]);

    $response = $this->actingAs($moderator)->putJson("/moderator/slots/{$slot->id}/attendance", [
        'status' => 'selesai',
        'execution_notes' => 'Presentasi berjalan lancar, tidak ada kendala teknis.',
    ]);

    $response->assertRedirect();
    $slot->refresh();
    expect($slot->status)->toBe('selesai');
    expect($slot->execution_notes)->toBe('Presentasi berjalan lancar, tidak ada kendala teknis.');
});

test('a moderator cannot act on a session moderated by someone else', function () {
    $moderator = User::factory()->create();
    $moderator->assignRole('moderator');
    $otherModerator = User::factory()->create();
    $otherModerator->assignRole('moderator');
    $article = makeAcceptedArticle();
    $session = ScheduleSession::factory()->create(['moderator_id' => $otherModerator->id]);
    $slot = PresentationSlot::factory()->create([
        'schedule_session_id' => $session->id,
        'article_id' => $article->id,
    ]);

    $response = $this->actingAs($moderator)->putJson("/moderator/slots/{$slot->id}/attendance", [
        'status' => 'hadir',
    ]);

    $response->assertForbidden();
});
