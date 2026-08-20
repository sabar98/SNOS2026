<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use App\Models\PresentationSlot;
use App\Models\ScheduleSession;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ScheduleController extends Controller
{
    public function index(): Response
    {
        $slots = PresentationSlot::query()
            ->whereHas('article.eventRegistration', fn ($query) => $query->where('user_id', Auth::id()))
            ->with(['scheduleSession', 'article'])
            ->get();

        $registration = EventRegistration::where('user_id', Auth::id())->latest()->first();

        $tickets = EventRegistration::where('user_id', Auth::id())
            ->get()
            ->map(fn (EventRegistration $registration) => [
                'registration_number' => $registration->registration_number,
                'status' => $registration->status,
                'qr_svg' => (string) QrCode::format('svg')->size(180)->margin(0)->generate($registration->registration_number),
            ]);

        $sessions = ScheduleSession::query()
            ->with('moderator')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $attendedSessionIds = $registration
            ? $registration->attendances()->where('type', 'sesi')->where('status', 'hadir')->pluck('schedule_session_id')->all()
            : [];

        return Inertia::render('Participant/Schedule', [
            'slots' => $slots,
            'tickets' => $tickets,
            'sessions' => $sessions,
            'attendedSessionIds' => $attendedSessionIds,
            'attendanceMethod' => $registration?->attendance_method,
        ]);
    }
}
