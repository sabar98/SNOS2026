<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function store(Request $request, EventRegistration $registration): RedirectResponse
    {
        $this->authorize('update', $registration);

        $validated = $request->validate([
            'speaker_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'committee_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'material_quality_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'facility_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'zoom_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'feedback' => ['nullable', 'string'],
        ]);

        $registration->evaluation()->updateOrCreate([], $validated);
        $registration->markCompletedIfEligible();

        return back()->with('status', 'evaluation-submitted');
    }
}
