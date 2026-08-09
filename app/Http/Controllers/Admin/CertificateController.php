<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\EventRegistration;
use App\Services\CertificatePdfGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Certificates', [
            'certificates' => Certificate::with(['user', 'eventRegistration'])->latest()->paginate(20)->withQueryString(),
            'eligibleRegistrations' => EventRegistration::where('status', 'selesai')
                ->whereDoesntHave('certificates')
                ->with('user')
                ->get(),
            'certificateTemplates' => CertificateTemplate::all()->keyBy('role'),
        ]);
    }

    public function store(Request $request, CertificatePdfGenerator $pdfGenerator): RedirectResponse
    {
        $validated = $request->validate([
            'event_registration_id' => ['required', 'exists:event_registrations,id'],
            'role' => ['required', 'in:peserta,presenter,moderator,reviewer,narasumber,panitia'],
            'jp_hours' => ['nullable', 'integer', 'min:0'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $registration = EventRegistration::findOrFail($validated['event_registration_id']);

        $certificate = DB::transaction(function () use ($registration, $validated) {
            $certificate = Certificate::create([
                'user_id' => $registration->user_id,
                'event_registration_id' => $registration->id,
                'certificate_number' => 'CERT-SNOS2026-'.Str::upper(Str::random(8)),
                'role' => $validated['role'],
                'jp_hours' => $validated['jp_hours'] ?? null,
                'verification_token' => Str::uuid()->toString(),
                'signed_at' => now(),
            ]);

            $registration->update(['status' => 'sertifikat_terbit']);

            return $certificate;
        });

        if ($request->hasFile('certificate_file')) {
            $filePath = $request->file('certificate_file')->storeAs(
                'certificates',
                "{$certificate->certificate_number}.pdf",
                'public',
            );
        } else {
            $filePath = $pdfGenerator->generate($certificate);
        }

        $certificate->update(['file_path' => $filePath]);

        return back()->with('status', 'certificate-issued');
    }

    public function preview(Request $request, CertificatePdfGenerator $pdfGenerator): HttpResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:peserta,presenter,moderator,reviewer,narasumber,panitia'],
            'event_registration_id' => ['nullable', 'exists:event_registrations,id'],
            'jp_hours' => ['nullable', 'integer', 'min:0'],
        ]);

        $participantName = 'Nama Peserta Contoh';

        if (! empty($validated['event_registration_id'])) {
            $registration = EventRegistration::with('user')->find($validated['event_registration_id']);
            $participantName = $registration?->user->name ?? $participantName;
        }

        $pdfBinary = $pdfGenerator->preview($validated['role'], $participantName, $validated['jp_hours'] ?? null);

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="pratinjau-sertifikat.pdf"',
        ]);
    }
}
