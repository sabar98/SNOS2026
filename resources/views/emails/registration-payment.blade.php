<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Info Pembayaran Pendaftaran {{ $registrationNumber }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f3f6fb;
            font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
            color: #1b1b18;
        }
        .wrapper {
            max-width: 480px;
            margin: 0 auto;
            padding: 32px 16px;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: #1c5cab;
            color: #ffffff;
            padding: 24px 28px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }
        .body {
            padding: 28px;
        }
        .body p {
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 16px;
        }
        .details {
            background: #f3f6fb;
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 0 0 16px;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 6px 0;
            font-size: 14px;
        }
        .details-label {
            color: #52514e;
        }
        .details-value {
            font-weight: 600;
            text-align: right;
        }
        .amount {
            background: #eaf6ee;
            border: 1px solid #bfe6cc;
            border-radius: 8px;
            padding: 14px 20px;
            margin: 0 0 16px;
            text-align: center;
        }
        .amount-label {
            font-size: 12px;
            color: #226b3b;
            margin: 0 0 4px;
        }
        .amount-value {
            font-size: 22px;
            font-weight: 700;
            color: #1c7a3f;
        }
        .warning {
            font-size: 12px;
            color: #8a5300;
            background: #fff6e5;
            border: 1px solid #f3d98b;
            border-radius: 8px;
            padding: 10px 14px;
            margin: 0 0 20px;
        }
        .button {
            display: inline-block;
            background: #1c5cab;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #898781;
            padding: 20px 0 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <h1>{{ $seminarName }}</h1>
            </div>
            <div class="body">
                <p>Halo {{ $userName }},</p>
                <p>Pendaftaran Anda dengan nomor <strong>{{ $registrationNumber }}</strong> untuk kegiatan <strong>{{ $seminarName }}</strong> telah berhasil dibuat. Berikut rincian pembayaran yang perlu diselesaikan:</p>

                <div class="details">
                    <div class="details-row">
                        <span class="details-label">Kegiatan</span>
                        <span class="details-value">{{ $seminarName }}</span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Jenis Kepesertaan</span>
                        <span class="details-value">{{ $categoryLabel }}</span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Kode Pembayaran</span>
                        <span class="details-value">{{ $paymentCode }}</span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Rekening Tujuan</span>
                        <span class="details-value">{{ $bankAccount }}</span>
                    </div>
                    @if ($dueAt)
                        <div class="details-row">
                            <span class="details-label">Batas Waktu</span>
                            <span class="details-value">{{ $dueAt }} WIB</span>
                        </div>
                    @endif
                </div>

                <div class="amount">
                    <p class="amount-label">Jumlah yang harus dibayar</p>
                    <p class="amount-value">Rp{{ number_format($amount, 0, ',', '.') }}</p>
                </div>

                <div class="warning">
                    Segera lakukan pembayaran ke rekening di atas, lalu unggah bukti transfer melalui dashboard Anda agar pendaftaran dapat diverifikasi panitia.
                </div>

                <p><a class="button" href="{{ $detailUrl }}">Lihat Detail &amp; Unggah Bukti Bayar</a></p>
            </div>
        </div>
        <p class="footer">Email ini dikirim otomatis, mohon tidak membalas ke alamat ini.</p>
    </div>
</body>
</html>
