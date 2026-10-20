<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use App\Services\ParticipantExporter;
use App\Services\ParticipantPdfExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));

        $registrations = $this->searchableRegistrations($search)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Participants', [
            'registrations' => $registrations,
            'filters' => ['search' => $search],
            'participantsByType' => EventRegistration::query()
                ->selectRaw('participant_type, count(*) as total')
                ->groupBy('participant_type')
                ->pluck('total', 'participant_type'),
            'participantsByStatus' => EventRegistration::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function exportExcel(Request $request, ParticipantExporter $exporter): StreamedResponse
    {
        $search = trim((string) $request->string('search'));
        $registrations = $this->searchableRegistrations($search)->latest()->get();
        $filename = 'daftar-peserta-'.now()->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($exporter, $registrations) {
            $exporter->writeTo($registrations)->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf(Request $request, ParticipantPdfExporter $exporter): HttpResponse
    {
        $search = trim((string) $request->string('search'));
        $registrations = $this->searchableRegistrations($search)->latest()->get();
        $filename = 'daftar-peserta-'.now()->format('Y-m-d').'.pdf';

        return $exporter->generate($registrations, $search !== '' ? $search : null)->stream($filename);
    }

    /**
     * The same filters used by the on-screen list, the Excel export and the PDF export,
     * so "what you searched for" is always "what you get" regardless of which you use.
     */
    private function searchableRegistrations(string $search): Builder
    {
        return EventRegistration::with(['user', 'payments'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('whatsapp_number', 'like', "%{$search}%");
                });
            });
    }

    public function show(EventRegistration $registration): Response
    {
        $registration->load(['user.profile', 'payments', 'articles.journal', 'attendances', 'evaluation', 'certificates']);

        return Inertia::render('Admin/ParticipantShow', [
            'registration' => $registration,
        ]);
    }
}
