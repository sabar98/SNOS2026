<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\StoreEventRegistrationRequest;
use App\Http\Requests\Participant\UpdateEventRegistrationRequest;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EventRegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Participant/RegistrationCreate', [
            'seminar' => config('seminar'),
        ]);
    }

    public function store(StoreEventRegistrationRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        $registration = DB::transaction(function () use ($user, $validated) {
            $registration = EventRegistration::create([
                'registration_number' => 'SNOS2026-'.Str::upper(Str::random(8)),
                'user_id' => $user->id,
                'participant_type' => $validated['participant_type'],
                'attendance_method' => $validated['attendance_method'],
                'article_scope' => $validated['article_scope'] ?? null,
                'institution' => $validated['institution'],
                'special_needs' => $validated['special_needs'] ?? null,
                'join_gala_dinner' => $validated['join_gala_dinner'] ?? false,
                'terms_accepted_at' => now(),
                'status' => 'menunggu_pembayaran',
                'payment_due_at' => now()->addDays(7),
            ]);

            $registration->payments()->create([
                'type' => 'registrasi',
                'amount' => config("seminar.fees.{$registration->participant_type}", 0),
                'bank_account' => '1234567890 a.n. Panitia SNOS 2026 (Bank Contoh)',
                'payment_code' => 'PAY-'.Str::upper(Str::random(10)),
                'due_at' => $registration->payment_due_at,
                'status' => 'belum_bayar',
            ]);

            return $registration;
        });

        return redirect()->route('participant.registrations.show', $registration)
            ->with('status', 'registration-created');
    }

    public function show(EventRegistration $registration): Response
    {
        $this->authorize('view', $registration);

        $registration->load(['payments', 'articles.journal', 'articles.authors', 'evaluation']);

        return Inertia::render('Participant/RegistrationShow', [
            'registration' => $registration,
            'feeLocked' => $registration->isFeeLocked(),
        ]);
    }

    public function edit(EventRegistration $registration): Response
    {
        $this->authorize('update', $registration);

        return Inertia::render('Participant/RegistrationEdit', [
            'registration' => $registration,
            'seminar' => config('seminar'),
            'feeLocked' => $registration->isFeeLocked(),
        ]);
    }

    public function update(UpdateEventRegistrationRequest $request, EventRegistration $registration): RedirectResponse
    {
        $this->authorize('update', $registration);

        $validated = $request->validated();

        $data = [
            'attendance_method' => $validated['attendance_method'],
            'article_scope' => $validated['article_scope'] ?? null,
            'institution' => $validated['institution'],
            'special_needs' => $validated['special_needs'] ?? null,
            'join_gala_dinner' => $validated['join_gala_dinner'] ?? false,
        ];

        if (! $registration->isFeeLocked()) {
            $data['participant_type'] = $validated['participant_type'];

            $registrationPayment = $registration->payments()->where('type', 'registrasi')->first();
            if ($registrationPayment && $registrationPayment->status !== 'terverifikasi') {
                $registrationPayment->update(['amount' => config("seminar.fees.{$validated['participant_type']}", 0)]);
            }
        }

        $registration->update($data);

        return redirect()->route('participant.registrations.show', $registration)
            ->with('status', 'registration-updated');
    }
}
