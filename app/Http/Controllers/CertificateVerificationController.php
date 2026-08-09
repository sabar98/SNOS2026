<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Inertia\Inertia;
use Inertia\Response;

class CertificateVerificationController extends Controller
{
    public function show(string $token): Response
    {
        $certificate = Certificate::with('user')
            ->where('verification_token', $token)
            ->first();

        return Inertia::render('CertificateVerify', [
            'certificate' => $certificate,
            'seminarName' => config('seminar.name'),
        ]);
    }
}
