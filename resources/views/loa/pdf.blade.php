<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Letter of Acceptance {{ $loa->loa_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 50px 60px;
            font-family: 'DejaVu Sans', sans-serif;
            color: #1b1b18;
            font-size: 13px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #2a78d6;
            padding-bottom: 16px;
            margin-bottom: 30px;
        }
        .eyebrow {
            letter-spacing: 3px;
            font-size: 11px;
            color: #52514e;
            text-transform: uppercase;
        }
        .seminar-name {
            font-size: 20px;
            font-weight: bold;
            margin-top: 6px;
        }
        .meta {
            font-size: 11px;
            color: #52514e;
            margin-top: 4px;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin: 24px 0;
        }
        .loa-number {
            text-align: center;
            font-family: monospace;
            font-size: 12px;
            margin-bottom: 24px;
        }
        table.details {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table.details td {
            padding: 6px 0;
            vertical-align: top;
        }
        table.details td.label {
            width: 180px;
            color: #52514e;
        }
        table.details td.colon {
            width: 12px;
        }
        .article-title {
            font-weight: bold;
        }
        .signature-block {
            margin-top: 60px;
            width: 260px;
            margin-left: auto;
            text-align: center;
        }
        .signature-mark {
            font-style: italic;
            font-weight: bold;
            font-size: 22px;
            color: #2a78d6;
        }
        .signature-image {
            height: 70px;
            max-width: 220px;
            object-fit: contain;
            margin: 0 auto;
        }
        .signature-line {
            border-bottom: 1px solid #1b1b18;
            margin: 4px 0 6px;
        }
        .signature-name {
            font-size: 12px;
            font-weight: bold;
        }
        .signature-title {
            font-size: 10px;
            color: #52514e;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="eyebrow">Letter of Acceptance</div>
        <div class="seminar-name">{{ $seminarName }}</div>
        <div class="meta">{{ $seminarDateRange }} &middot; {{ $seminarLocation }}</div>
    </div>

    <div class="title">Surat Penerimaan Artikel</div>
    <div class="loa-number">Nomor: {{ $loa->loa_number }}</div>

    <p>
        Dengan ini panitia {{ $seminarName }} menyatakan bahwa artikel berikut telah melalui proses pemeriksaan
        administrasi dan review, serta dinyatakan <strong>diterima</strong> untuk dipresentasikan dan/atau
        dipublikasikan.
    </p>

    <table class="details">
        <tr>
            <td class="label">Judul Artikel</td>
            <td class="colon">:</td>
            <td class="article-title">{{ $article->title }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Artikel</td>
            <td class="colon">:</td>
            <td>{{ $article->article_number }}</td>
        </tr>
        <tr>
            <td class="label">Penulis</td>
            <td class="colon">:</td>
            <td>{{ $participantName }}</td>
        </tr>
        @if($journalName)
        <tr>
            <td class="label">Jurnal / Prosiding Tujuan</td>
            <td class="colon">:</td>
            <td>{{ $journalName }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">Tanggal Diterbitkan</td>
            <td class="colon">:</td>
            <td>{{ $loa->issued_at?->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <p>
        Jadwal presentasi, ruang atau tautan Zoom, serta informasi pembayaran publikasi (jika ada) dapat dilihat
        pada dashboard peserta.
    </p>

    <div class="signature-block">
        @if($signatureBase64)
            <img class="signature-image" src="data:{{ $signatureMime }};base64,{{ $signatureBase64 }}" alt="Tanda tangan">
        @else
            <div class="signature-mark">{{ $signerName }}</div>
        @endif
        <div class="signature-line"></div>
        <div class="signature-name">{{ $signerName }}</div>
        <div class="signature-title">{{ $signerTitle }}</div>
    </div>
</body>
</html>
