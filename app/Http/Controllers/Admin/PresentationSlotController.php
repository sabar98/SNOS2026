<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PresentationSlot;
use App\Models\ScheduleSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PresentationSlotController extends Controller
{
    public function store(Request $request, ScheduleSession $scheduleSession): RedirectResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:articles,id'],
            'order' => ['required', 'integer', 'min:1'],
        ]);

        $slot = $scheduleSession->presentationSlots()->create($validated + ['status' => 'dijadwalkan']);

        $slot->article->eventRegistration->update(['status' => 'jadwal_ditetapkan']);

        return back()->with('status', 'slot-created');
    }

    public function destroy(PresentationSlot $presentationSlot): RedirectResponse
    {
        $presentationSlot->delete();

        return back()->with('status', 'slot-deleted');
    }
}
