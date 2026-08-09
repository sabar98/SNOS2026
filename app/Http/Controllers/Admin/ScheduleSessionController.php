<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ScheduleSession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleSessionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ScheduleSessions', [
            'sessions' => ScheduleSession::with(['moderator', 'presentationSlots.article.eventRegistration.user'])
                ->orderBy('date')
                ->orderBy('start_time')
                ->get(),
            'moderators' => User::role('moderator')->get(['id', 'name']),
            'acceptedArticles' => Article::where('status', 'diterima')
                ->whereDoesntHave('presentationSlots')
                ->with('eventRegistration.user')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'session_number' => ['required', 'string', 'max:50'],
            'room' => ['nullable', 'string', 'max:255'],
            'moderator_id' => ['nullable', 'exists:users,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'zoom_link' => ['nullable', 'url', 'max:255'],
            'zoom_meeting_id' => ['nullable', 'string', 'max:100'],
            'zoom_password' => ['nullable', 'string', 'max:100'],
        ]);

        ScheduleSession::create($validated);

        return back()->with('status', 'session-created');
    }

    public function update(Request $request, ScheduleSession $scheduleSession): RedirectResponse
    {
        $validated = $request->validate([
            'session_number' => ['required', 'string', 'max:50'],
            'room' => ['nullable', 'string', 'max:255'],
            'moderator_id' => ['nullable', 'exists:users,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'zoom_link' => ['nullable', 'url', 'max:255'],
            'zoom_meeting_id' => ['nullable', 'string', 'max:100'],
            'zoom_password' => ['nullable', 'string', 'max:100'],
        ]);

        $scheduleSession->update($validated);

        return back()->with('status', 'session-updated');
    }

    public function destroy(ScheduleSession $scheduleSession): RedirectResponse
    {
        $scheduleSession->delete();

        return back()->with('status', 'session-deleted');
    }
}
