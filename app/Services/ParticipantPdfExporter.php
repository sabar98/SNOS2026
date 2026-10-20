<?php

namespace App\Services;

use App\Models\EventRegistration;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Collection;

class ParticipantPdfExporter
{
    /**
     * @param  Collection<int, EventRegistration>  $registrations
     */
    public function generate(Collection $registrations, ?string $search = null): DomPdf
    {
        return Pdf::loadView('admin.participants.pdf', [
            'registrations' => $registrations,
            'search' => $search,
            'generatedAt' => now(),
            'seminarName' => config('seminar.name'),
        ])->setPaper('a4', 'landscape');
    }
}
