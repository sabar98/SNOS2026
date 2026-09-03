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

        // Bundled with the app (not admin-uploadable) — the panitia's official kop surat.
        $letterheadFile = resource_path('images/loa-letterhead.png');
        $letterheadBase64 = base64_encode(file_get_contents($letterheadFile));

        $pdf = Pdf::loadView('loa.pdf', [
            'loa' => $loa,
            'article' => $article,
            'seminarName' => config('seminar.name'),
            'participantName' => $article->eventRegistration->user->name,
            'journalName' => $article->journal?->name,
            'signerName' => config('seminar.certificate_signer.name'),
            'signerTitle' => config('seminar.certificate_signer.title'),
            'signatureBase64' => $signatureBase64,
            'signatureMime' => $signatureMime,
            'letterheadBase64' => $letterheadBase64,
        ])->setPaper('a4', 'portrait');

        $path = "loa/{$loa->loa_number}.pdf";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
