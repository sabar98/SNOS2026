<?php

namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Support\Collection;

class PaymentPdfExporter
{
    /**
     * @param  Collection<int, Payment>  $payments
     */
    public function generate(Collection $payments, ?string $search = null): DomPdf
    {
        return Pdf::loadView('admin.payments.pdf', [
            'payments' => $payments,
            'search' => $search,
            'generatedAt' => now(),
            'seminarName' => config('seminar.name'),
        ])->setPaper('a4', 'landscape');
    }
}
