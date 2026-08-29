<?php

namespace App\Services;

use App\Models\LetterOfAcceptance;
use App\Models\LoaSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class LoaPdfGenerator
{
    public function generate(LetterOfAcceptance $loa): string
    {
        $loa->loadMissing('article.eventRegistration.user', 'article.journal');

        $article = $loa->article;

        $signaturePath = LoaSetting::current()->signature_path;
        $signatureBase64 = null;
        $signatureMime = null;

        if ($signaturePath && Storage::disk('public')->exists($signaturePath)) {
            $signatureBase64 = base64_encode(Storage::disk('public')->get($signaturePath));
            $signatureMime = Storage::disk('public')->mimeType($signaturePath);
        }

        $pdf = Pdf::loadView('loa.pdf', [
            'loa' => $loa,
            'article' => $article,
            'seminarName' => config('seminar.name'),
            'seminarDateRange' => config('seminar.date_range'),
            'seminarLocation' => config('seminar.location'),
            'participantName' => $article->eventRegistration->user->name,
            'journalName' => $article->journal?->name,
            'signerName' => config('seminar.certificate_signer.name'),
            'signerTitle' => config('seminar.certificate_signer.title'),
            'signatureBase64' => $signatureBase64,
            'signatureMime' => $signatureMime,
        ])->setPaper('a4', 'portrait');

        $path = "loa/{$loa->loa_number}.pdf";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
