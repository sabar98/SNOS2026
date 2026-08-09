<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use App\Models\ScheduleSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_number' => ['required', 'string'],
        ]);

        $registration = EventRegistration::where('registration_number', $validated['registration_number'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $registration->attendances()->updateOrCreate(
            ['type' => 'registrasi_ulang', 'schedule_session_id' => null],
            [
                'method' => $registration->attendance_method === 'daring' ? 'link_khusus' : 'qr_code',
                'status' => 'hadir',
                'checked_in_at' => now(),
            ]
        );

        $registration->update(['status' => 'hadir']);
        $registration->markCompletedIfEligible();

        return back()->with('status', 'checked-in');
    }

    public function storeSession(ScheduleSession $scheduleSession): RedirectResponse
    {
        $registration = EventRegistration::where('user_id', Auth::id())->latest()->first();

        abort_if(! $registration, 404, 'Anda belum memiliki pendaftaran kegiatan.');

        $registration->attendances()->updateOrCreate(
            ['type' => 'sesi', 'schedule_session_id' => $scheduleSession->id],
            [
                'method' => $registration->attendance_method === 'daring' ? 'kode_zoom' : 'qr_code',
                'status' => 'hadir',
                'checked_in_at' => now(),
            ]
        );

        return back()->with('status', 'session-attendance-recorded');
    }
}
