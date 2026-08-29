<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationFee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationFeeController extends Controller
{
    public const PARTICIPANT_TYPES = ['presenter_luring', 'presenter_daring', 'peserta_umum', 'peserta_mahasiswa'];

    public const ATTENDANCE_METHODS = ['luring', 'daring'];

    public function index(): Response
    {
        return Inertia::render('Admin/RegistrationFees', [
            'registrationFees' => RegistrationFee::orderBy('participant_type')->orderBy('attendance_method')->get(),
            'participantTypes' => self::PARTICIPANT_TYPES,
            'attendanceMethods' => self::ATTENDANCE_METHODS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'participant_type' => [
                'required',
                Rule::in(self::PARTICIPANT_TYPES),
                Rule::unique('registration_fees')->where(fn ($query) => $query->where('attendance_method', $request->attendance_method)),
            ],
            'attendance_method' => ['required', Rule::in(self::ATTENDANCE_METHODS)],
            'amount' => ['required', 'integer', 'min:0'],
        ]);

        RegistrationFee::create($validated);

        return back()->with('status', 'registration-fee-created');
    }

    public function update(Request $request, RegistrationFee $registrationFee): RedirectResponse
    {
        $validated = $request->validate([
            'participant_type' => [
                'required',
                Rule::in(self::PARTICIPANT_TYPES),
                Rule::unique('registration_fees')
                    ->where(fn ($query) => $query->where('attendance_method', $request->attendance_method))
                    ->ignore($registrationFee->id),
            ],
            'attendance_method' => ['required', Rule::in(self::ATTENDANCE_METHODS)],
            'amount' => ['required', 'integer', 'min:0'],
        ]);

        $registrationFee->update($validated);

        return back()->with('status', 'registration-fee-updated');
    }

    public function destroy(RegistrationFee $registrationFee): RedirectResponse
    {
        $registrationFee->delete();

        return back()->with('status', 'registration-fee-deleted');
    }
}
