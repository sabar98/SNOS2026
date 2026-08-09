<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantController extends Controller
{
    public function index(): Response
    {
        $registrations = EventRegistration::with(['user', 'payments'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Participants', [
            'registrations' => $registrations,
            'participantsByType' => EventRegistration::query()
                ->selectRaw('participant_type, count(*) as total')
                ->groupBy('participant_type')
                ->pluck('total', 'participant_type'),
            'participantsByStatus' => EventRegistration::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function show(EventRegistration $registration): Response
    {
        $registration->load(['user.profile', 'payments', 'articles.journal', 'attendances', 'evaluation', 'certificates']);

        return Inertia::render('Admin/ParticipantShow', [
            'registration' => $registration,
        ]);
    }
}
