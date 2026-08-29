<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\StoreEventRegistrationRequest;
use App\Http\Requests\Participant\UpdateEventRegistrationRequest;
use App\Models\BankAccount;
use App\Models\EventRegistration;
use App\Models\ParticipantCategory;
use App\Models\RegistrationFee;
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
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('bank_name')->get(),
            'registrationFees' => RegistrationFee::all(['participant_type', 'attendance_method', 'amount']),
            'participantCategories' => ParticipantCategory::where('is_active', true)->orderBy('id')->get(['key', 'label', 'is_presenter']),
        ]);
    }

    public function store(StoreEventRegistrationRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validated();
        $bankAccount = BankAccount::findOrFail($validated['bank_account_id']);

        $registration = DB::transaction(function () use ($user, $validated, $bankAccount) {
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
                'amount' => RegistrationFee::amountFor($registration->participant_type, $registration->attendance_method),
                'bank_account' => "{$bankAccount->bank_name} {$bankAccount->account_number} a.n. {$bankAccount->account_holder}",
                'bank_account_id' => $bankAccount->id,
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
            'isPresenter' => $registration->isPresenter(),
        ]);
    }

    public function edit(EventRegistration $registration): Response
    {
        $this->authorize('update', $registration);

        return Inertia::render('Participant/RegistrationEdit', [
            'registration' => $registration,
            'registrationFees' => RegistrationFee::all(['participant_type', 'attendance_method', 'amount']),
            'participantCategories' => ParticipantCategory::where('is_active', true)
                ->orWhere('key', $registration->participant_type)
                ->orderBy('id')
                ->get(['key', 'label', 'is_presenter']),
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
                $registrationPayment->update([
                    'amount' => RegistrationFee::amountFor($validated['participant_type'], $validated['attendance_method']),
                ]);
            }
        }

        $registration->update($data);

        return redirect()->route('participant.registrations.show', $registration)
            ->with('status', 'registration-updated');
    }
}
