<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\PresentationSlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(): Response
    {
        $sessions = Auth::user()->moderatedSessions()
            ->with(['presentationSlots.article.eventRegistration.user', 'presentationSlots.assessments'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return Inertia::render('Moderator/Sessions', [
            'sessions' => $sessions,
        ]);
    }

    public function recordAttendance(Request $request, PresentationSlot $slot): RedirectResponse
    {
        $this->authorize('update', $slot);

        $validated = $request->validate([
            'status' => ['required', 'in:hadir,tidak_hadir,dijadwalkan_ulang,selesai'],
            'execution_notes' => ['nullable', 'string'],
        ]);

        $slot->update($validated);

        return back()->with('status', 'attendance-recorded');
    }

    public function storeAssessment(Request $request, PresentationSlot $slot): RedirectResponse
    {
        $this->authorize('update', $slot);

        $validated = $request->validate([
            'mastery_score' => ['required', 'integer', 'min:1', 'max:5'],
            'presentation_quality_score' => ['required', 'integer', 'min:1', 'max:5'],
            'timeliness_score' => ['required', 'integer', 'min:1', 'max:5'],
            'qa_score' => ['required', 'integer', 'min:1', 'max:5'],
            'content_alignment_score' => ['required', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string'],
        ]);

        $slot->assessments()->updateOrCreate(
            ['assessed_by' => Auth::id()],
            $validated
        );

        return back()->with('status', 'assessment-recorded');
    }
}
