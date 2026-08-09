<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat {{ $certificate->certificate_number }}</title>
    <style>
        @page { margin: 0; }
        body {
            margin: 0;
            font-family: 'DejaVu Sans', sans-serif;
            color: #1b1b18;
        }
        .frame {
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            padding: 40px;
            border: 10px solid #2a78d6;
        }
        .inner {
            border: 1px solid #c3c2b7;
            padding: 50px 60px;
            text-align: center;
        }
        .eyebrow {
            letter-spacing: 4px;
            font-size: 12px;
            color: #52514e;
            text-transform: uppercase;
        }
        .seminar-name {
            font-size: 22px;
            font-weight: bold;
            margin-top: 8px;
        }
        .title {
            font-size: 16px;
            margin-top: 30px;
            color: #52514e;
        }
        .participant-name {
            font-size: 30px;
            font-weight: bold;
            margin-top: 10px;
            border-bottom: 1px solid #c3c2b7;
            display: inline-block;
            padding-bottom: 8px;
        }
        .role {
            font-size: 15px;
            margin-top: 16px;
        }
        .meta {
            margin-top: 40px;
            font-size: 11px;
            color: #52514e;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .footer td {
            vertical-align: bottom;
            font-size: 10px;
            color: #52514e;
        }
        .qr {
            width: 90px;
            height: 90px;
        }
        .cert-number {
            font-family: monospace;
            font-size: 11px;
            margin-top: 4px;
        }
        .signature-block {
            margin-top: 36px;
            display: inline-block;
            text-align: center;
        }
        .signature-mark {
            font-family: 'DejaVu Sans', sans-serif;
            font-style: italic;
            font-weight: bold;
            font-size: 26px;
            color: #2a78d6;
        }
        .signature-line {
            width: 220px;
            border-bottom: 1px solid #1b1b18;
            margin: 4px auto 0;
        }
        .signature-name {
            font-size: 13px;
            font-weight: bold;
            margin-top: 6px;
        }
        .signature-title {
            font-size: 11px;
            color: #52514e;
        }
        .signature-caption {
            font-size: 9px;
            color: #898781;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        @if($templateImageBase64)
        .template-page {
            position: relative;
            width: 100%;
            height: 100%;
        }
        .template-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .template-participant-name {
            position: absolute;
            top: 46%;
            left: 5%;
            width: 90%;
            text-align: center;
            font-size: 34px;
            font-weight: bold;
            color: #1b1b18;
        }
        .template-role {
            position: absolute;
            top: 58%;
            left: 5%;
            width: 90%;
            text-align: center;
            font-size: 16px;
            color: #1b1b18;
        }
        .template-meta {
            position: absolute;
            bottom: 6%;
            left: 6%;
            font-size: 10px;
            color: #1b1b18;
        }
        .template-qr {
            position: absolute;
            bottom: 5%;
            right: 6%;
            width: 80px;
            height: 80px;
        }
        @endif
    </style>
</head>
<body>
    @if($templateImageBase64)
        <div class="template-page">
            <img class="template-background" src="data:{{ $templateMime }};base64,{{ $templateImageBase64 }}" alt="Template Sertifikat">

            <div class="template-participant-name">{{ $certificate->user->name }}</div>

            <div class="template-role">
                Sebagai <strong>{{ $roleLabel }}</strong>
                @if($certificate->jp_hours)
                    &mdash; {{ $certificate->jp_hours }} JP
                @endif
            </div>

            <div class="template-meta">
                Nomor Sertifikat: {{ $certificate->certificate_number }}<br>
                Diterbitkan {{ $certificate->signed_at?->translatedFormat('d F Y') }}<br>
                Verifikasi: {{ $verificationUrl }}
            </div>

            <img class="template-qr" src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" alt="QR Verifikasi">
        </div>
    @else
        <div class="frame">
            <div class="inner">
                <div class="eyebrow">Sertifikat Elektronik</div>
                <div class="seminar-name">{{ $seminarName }}</div>

                <div class="title">Diberikan kepada</div>
                <div class="participant-name">{{ $certificate->user->name }}</div>

                <div class="role">
                    Sebagai <strong>{{ $roleLabel }}</strong>
                    @if($certificate->jp_hours)
                        &mdash; {{ $certificate->jp_hours }} JP
                    @endif
                </div>

                <div class="meta">
                    Diterbitkan {{ $certificate->signed_at?->translatedFormat('d F Y') }}
                </div>

                <div class="signature-block">
                    <div class="signature-mark">{{ $signerName }}</div>
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $signerName }}</div>
                    <div class="signature-title">{{ $signerTitle }}</div>
                    <div class="signature-caption">Ditandatangani secara elektronik</div>
                </div>

                <table class="footer">
                    <tr>
                        <td style="text-align: left;">
                            Nomor Sertifikat<br>
                            <span class="cert-number">{{ $certificate->certificate_number }}</span><br><br>
                            Verifikasi keaslian di:<br>
                            {{ $verificationUrl }}
                        </td>
                        <td style="text-align: right;">
                            <img class="qr" src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" alt="QR Verifikasi">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @endif
</body>
</html>
