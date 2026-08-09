<?php

use App\Models\EventRegistration;
use App\Models\ScheduleSession;
use App\Models\User;

test('a participant sees a digital ticket with an actual QR code svg string', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create([
        'registration_number' => 'SNOS2026-TICKETTEST',
    ]);

    $response = $this->actingAs($participant)->get('/participant/schedule');

    $response->assertInertia(fn ($page) => $page
        ->component('Participant/Schedule')
        ->where('tickets.0.registration_number', 'SNOS2026-TICKETTEST')
        ->where('tickets.0.qr_svg', fn (string $svg) => str_contains($svg, '<svg'))
    );
});

test('a participant sees the full session agenda regardless of whether they are presenting', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    EventRegistration::factory()->for($participant, 'user')->create();
    $session = ScheduleSession::factory()->create(['session_number' => 'A1']);

    $response = $this->actingAs($participant)->get('/participant/schedule');

    $response->assertInertia(fn ($page) => $page
        ->component('Participant/Schedule')
        ->where('sessions.0.session_number', 'A1')
        ->where('attendedSessionIds', [])
    );
});

test('a participant can mark their attendance for a session', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    $registration = EventRegistration::factory()->for($participant, 'user')->create(['attendance_method' => 'luring']);
    $session = ScheduleSession::factory()->create();

    $response = $this->actingAs($participant)->post("/participant/sessions/{$session->id}/attendance");

    $response->assertRedirect();
    $attendance = $registration->attendances()->where('type', 'sesi')->first();
    expect($attendance)->not->toBeNull();
    expect($attendance->schedule_session_id)->toBe($session->id);
    expect($attendance->status)->toBe('hadir');
    expect($attendance->method)->toBe('qr_code');

    $response = $this->actingAs($participant)->get('/participant/schedule');
    $response->assertInertia(fn ($page) => $page->where('attendedSessionIds', [$session->id]));
});

test('an online participant marking session attendance is recorded with the zoom-code method', function () {
    $participant = User::factory()->create();
    $participant->assignRole('peserta');
    EventRegistration::factory()->for($participant, 'user')->create(['attendance_method' => 'daring']);
    $session = ScheduleSession::factory()->create();

    $this->actingAs($participant)->post("/participant/sessions/{$session->id}/attendance");

    $registration = EventRegistration::where('user_id', $participant->id)->first();
    expect($registration->attendances()->where('type', 'sesi')->first()->method)->toBe('kode_zoom');
});
