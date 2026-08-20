<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Akun {{ $seminarName }} Berhasil Dibuat</title>
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
        .credentials {
            background: #f3f6fb;
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 0 0 16px;
        }
        .credentials-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
        }
        .credentials-label {
            color: #52514e;
        }
        .credentials-value {
            font-weight: 600;
            font-family: 'Courier New', monospace;
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
                <p>Halo {{ $user->name }},</p>
                <p>Akun Anda untuk mengikuti {{ $seminarName }} telah berhasil dibuat. Berikut rincian akun Anda:</p>

                <div class="credentials">
                    <div class="credentials-row">
                        <span class="credentials-label">Email</span>
                        <span class="credentials-value">{{ $user->email }}</span>
                    </div>
                    <div class="credentials-row">
                        <span class="credentials-label">Kata Sandi</span>
                        <span class="credentials-value">{{ $plainPassword }}</span>
                    </div>
                </div>

                <div class="warning">
                    Jaga kerahasiaan kata sandi ini. Jangan bagikan kepada siapa pun, dan segera ganti melalui menu Pengaturan setelah masuk.
                </div>

                <p>Gunakan email dan kata sandi di atas untuk masuk ke akun Anda:</p>
                <p><a class="button" href="{{ $loginUrl }}">Masuk ke Akun</a></p>
            </div>
        </div>
        <p class="footer">Email ini dikirim otomatis, mohon tidak membalas ke alamat ini.</p>
    </div>
</body>
</html>
