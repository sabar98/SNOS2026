<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificatePdfGenerator
{
    private const ROLE_LABELS = [
        'peserta' => 'Peserta',
        'presenter' => 'Presenter',
        'moderator' => 'Moderator',
        'reviewer' => 'Reviewer',
        'narasumber' => 'Narasumber',
        'panitia' => 'Panitia',
    ];

    public function generate(Certificate $certificate): string
    {
        $certificate->loadMissing('user');

        $path = "certificates/{$certificate->certificate_number}.pdf";

        Storage::disk('public')->put($path, $this->render($certificate)->output());

        return $path;
    }

    public function preview(string $role, string $participantName, ?int $jpHours = null): string
    {
        $certificate = new Certificate([
            'role' => $role,
            'jp_hours' => $jpHours,
            'certificate_number' => 'PREVIEW-'.strtoupper($role),
            'verification_token' => 'preview',
            'signed_at' => now(),
        ]);
        $certificate->setRelation('user', new User(['name' => $participantName]));

        return $this->render($certificate)->output();
    }

    private function render(Certificate $certificate): PdfDocument
    {
        $verificationUrl = route('certificates.verify', $certificate->verification_token);

        $qrCodeSvg = (string) QrCode::format('svg')->size(200)->margin(0)->generate($verificationUrl);

        $template = CertificateTemplate::where('role', $certificate->role)->first();
        $templateImageBase64 = null;
        $templateMime = null;

        if ($template) {
            $templateImageBase64 = base64_encode(Storage::disk('public')->get($template->file_path));
            $templateMime = Storage::disk('public')->mimeType($template->file_path);
        }

        return Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'seminarName' => config('seminar.name'),
            'roleLabel' => self::ROLE_LABELS[$certificate->role] ?? $certificate->role,
            'verificationUrl' => $verificationUrl,
            'qrCodeBase64' => base64_encode($qrCodeSvg),
            'signerName' => config('seminar.certificate_signer.name'),
            'signerTitle' => config('seminar.certificate_signer.title'),
            'templateImageBase64' => $templateImageBase64,
            'templateMime' => $templateMime,
        ])->setPaper('a4', 'landscape');
    }
}
