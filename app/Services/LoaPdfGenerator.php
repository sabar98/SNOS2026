<?php

namespace App\Services;

use App\Models\LandingSetting;
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

        $setting = LoaSetting::current();
        $signaturePath = $setting->signature_path;
        $signatureBase64 = null;
        $signatureMime = null;

        if ($signaturePath && Storage::disk('public')->exists($signaturePath)) {
            $signatureBase64 = base64_encode(Storage::disk('public')->get($signaturePath));
            $signatureMime = Storage::disk('public')->mimeType($signaturePath);
        }

        // The kop is built in HTML/CSS; only its logo is taken from the landing page branding.
        // DomPDF can only embed png/jpeg/gif, so any other upload is left out of the kop.
        $logoBase64 = null;
        $logoMime = null;
        $logoPath = LandingSetting::current()->site_logo_path;

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $mime = Storage::disk('public')->mimeType($logoPath);

            if (in_array($mime, ['image/png', 'image/jpeg', 'image/gif'], true)) {
                $logoBase64 = base64_encode(Storage::disk('public')->get($logoPath));
                $logoMime = $mime;
            }
        }

        $pdf = Pdf::loadView('loa.pdf', [
            'loa' => $loa,
            'article' => $article,
            'seminarName' => config('seminar.name'),
            'participantName' => $article->eventRegistration->user->name,
            'journalName' => $article->journal?->name,
            // Values from the LoA settings page win; config is only the fallback until they're filled in.
            'signerName' => $setting->signer_name ?: config('seminar.certificate_signer.name'),
            'signerTitle' => $setting->signer_title ?: config('seminar.certificate_signer.title'),
            'signatureBase64' => $signatureBase64,
            'signatureMime' => $signatureMime,
            'logoBase64' => $logoBase64,
            'logoMime' => $logoMime,
        ])->setPaper('a4', 'portrait');

        $path = "loa/{$loa->loa_number}.pdf";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
}
