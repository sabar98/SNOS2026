<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Pembayaran</title>
    <style>
        body {
            margin: 0;
            padding: 24px 28px;
            font-family: 'DejaVu Sans', sans-serif;
            color: #1b1b18;
            font-size: 10px;
        }
        h1 {
            font-size: 16px;
            margin: 0 0 2px;
        }
        .meta {
            color: #52514e;
            font-size: 10px;
            margin-bottom: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #d9d9d4;
            padding: 5px 7px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #FEF3C7;
            font-weight: bold;
        }
        td.num {
            text-align: right;
            white-space: nowrap;
        }
        td.empty {
            text-align: center;
            color: #9a998f;
            padding: 16px;
        }
    </style>
</head>
<body>
    <h1>Daftar Pembayaran — {{ $seminarName }}</h1>
    <p class="meta">
        Dicetak: {{ $generatedAt->locale('id')->translatedFormat('d F Y, H:i') }} WIB
        @if($search) &middot; Pencarian: &ldquo;{{ $search }}&rdquo; @endif
        &middot; Total: {{ $payments->count() }} transaksi
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Referensi</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Tipe</th>
                <th>Kategori</th>
                <th>Nominal</th>
                <th>Status Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $index => $payment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $payment->reference ?? '-' }}</td>
                    <td>{{ $payment->participant_name ?? '-' }}</td>
                    <td>{{ $payment->participant_email ?? '-' }}</td>
                    <td>{{ \App\Models\Payment::TYPE_LABELS[$payment->type] ?? $payment->type }}</td>
                    <td>{{ $payment->participant_category ?? '-' }}</td>
                    <td class="num">Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                    <td>{{ \App\Models\Payment::STATUS_LABELS[$payment->status] ?? $payment->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty">Tidak ada pembayaran yang cocok.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
