<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckInController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/CheckIn', [
            'status' => session('status'),
            'checkedInName' => session('checkedInName'),
            'recentCheckIns' => EventRegistration::query()
                ->whereHas('attendances', fn ($query) => $query->where('type', 'registrasi_ulang'))
                ->with(['user', 'attendances' => fn ($query) => $query->where('type', 'registrasi_ulang')->latest('checked_in_at')])
                ->latest('updated_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_number' => ['required', 'string'],
        ]);

        $registration = EventRegistration::where('registration_number', trim($validated['registration_number']))->first();

        if (! $registration) {
            return back()->withErrors(['registration_number' => 'Nomor registrasi tidak ditemukan.']);
        }

        $registration->attendances()->updateOrCreate(
            ['type' => 'registrasi_ulang', 'schedule_session_id' => null],
            ['method' => 'qr_code', 'status' => 'hadir', 'checked_in_at' => now()],
        );

        $registration->update(['status' => 'hadir']);
        $registration->markCompletedIfEligible();

        return back()->with('status', 'checked-in')->with('checkedInName', $registration->load('user')->user->name);
    }
}
