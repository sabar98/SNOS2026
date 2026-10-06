<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Letter of Acceptance {{ $loa->loa_number }}</title>
    <style>
        @page {
            margin: 0 0 20mm 0;
        }
        body {
            margin: 0;
            font-family: 'DejaVu Sans', sans-serif;
            color: #1b1b18;
            font-size: 13px;
            line-height: 1.6;
        }
        .content {
            padding: 0 25mm;
        }

        /* Kop surat, built in HTML/CSS. Colours follow the panitia's official letterhead. */
        .kop {
            padding: 10mm 25mm 0 25mm;
        }
        .kop-table {
            width: 100%;
            border-collapse: collapse;
        }
        .kop-table td {
            vertical-align: middle;
            padding: 0;
        }
        .kop-logo-cell {
            width: 34mm;
            padding-right: 5mm !important;
            border-right: 0.6px dotted #8a8a8a;
        }
        .kop-logo {
            width: 26mm;
            height: 26mm;
        }
        .kop-text-cell {
            padding-left: 5mm !important;
            border-right: 0.6px dotted #8a8a8a;
            padding-right: 5mm !important;
        }
        .kop-panitia {
            color: #f2a51a;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
            font-weight: bold;
            letter-spacing: 0.4px;
        }
        .kop-title {
            color: #0c5a9c;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 17px;
            font-weight: bold;
            line-height: 1.15;
        }
        .kop-title-small {
            font-size: 14px;
        }
        .kop-lldikti {
            color: #0c5a9c;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            font-weight: bold;
            margin-top: 1px;
        }
        .kop-address {
            text-align: center;
            font-family: 'Times', serif;
            font-size: 9.5px;
            line-height: 1.3;
            margin-top: 3mm;
        }
        .kop-address span {
            text-decoration: underline;
        }
        .kop-rule-thick {
            height: 1.6mm;
            background: #0c5a9c;
            margin-top: 2.5mm;
        }
        .kop-rule-thin {
            height: 0.5mm;
            background: #7fb2dd;
            margin-top: 0.8mm;
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
    <div class="kop">
        <table class="kop-table">
            <tr>
                <td class="kop-logo-cell">
                    @if($logoBase64)
                        <img class="kop-logo" style="width: 26mm; height: 26mm;" src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" alt="Logo {{ $seminarName }}">
                    @endif
                </td>
                <td class="kop-text-cell">
                    <div class="kop-panitia">PANITIA PELAKSANA</div>
                    <div class="kop-title">SEMINAR NASIONAL</div>
                    <div class="kop-title kop-title-small">OMNI SCIENTIA (SNOS) 2026</div>
                    <div class="kop-lldikti">LLDIKTI WILAYAH XIII</div>
                </td>
            </tr>
        </table>

        <div class="kop-address">
            <span>Sekretariat</span>: Jalan Alue Naga, Desa Tibang, Kec. Syiah Kuala, Kota Banda Aceh 23114<br>
            Telp. (0651) <span>31130</span> | Email: info.lldikti13@kemdiktisaintek.go.id | Website: semnas.serambimekkah.ac.id
        </div>

        <div class="kop-rule-thick"></div>
        <div class="kop-rule-thin"></div>
    </div>

    <div class="content">
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
            <td>{{ $loa->issued_at?->locale('id')->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <p>
        Jadwal presentasi, ruang atau tautan Zoom, serta informasi pembayaran publikasi (jika ada) dapat dilihat
        pada dashboard peserta.
    </p>

    <div class="signature-block">
        @if($signatureBase64)
            <img class="signature-image" src="data:{{ $signatureMime }};base64,{{ $signatureBase64 }}" alt="Tanda tangan">
        @endif
        <div class="signature-line"></div>
        <div class="signature-name">{{ $signerName }}</div>
        <div class="signature-title">{{ $signerTitle }}</div>
    </div>
    </div>
</body>
</html>
