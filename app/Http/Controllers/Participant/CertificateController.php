<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $certificates = $user->certificates()->with('eventRegistration')->get();

        $registrations = $user->eventRegistrations()->with(['payments', 'evaluation', 'attendances', 'articles'])->get();

        $eligibility = $registrations->mapWithKeys(function ($registration) {
            $isPaid = $registration->payments->where('type', 'registrasi')->contains(fn ($payment) => $payment->status === 'terverifikasi');
            $isPresent = $registration->attendances->contains(fn ($attendance) => $attendance->status === 'hadir');
            $hasEvaluated = (bool) $registration->evaluation;
            $presentationDone = ! $registration->isPresenter() || $registration->articles->contains(fn ($article) => $article->status === 'diterima');

            return [$registration->id => [
                'is_paid' => $isPaid,
                'is_present' => $isPresent,
                'has_evaluated' => $hasEvaluated,
                'presentation_done' => $presentationDone,
                'is_eligible' => $isPaid && $isPresent && $hasEvaluated && $presentationDone,
            ]];
        });

        return Inertia::render('Participant/Certificates', [
            'certificates' => $certificates,
            'registrations' => $registrations,
            'eligibility' => $eligibility,
        ]);
    }
}
