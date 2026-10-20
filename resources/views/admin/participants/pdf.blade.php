<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Peserta</title>
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
            background: #D1FAE5;
            font-weight: bold;
        }
        td.empty {
            text-align: center;
            color: #9a998f;
            padding: 16px;
        }
    </style>
</head>
<body>
    <h1>Daftar Peserta — {{ $seminarName }}</h1>
    <p class="meta">
        Dicetak: {{ $generatedAt->locale('id')->translatedFormat('d F Y, H:i') }} WIB
        @if($search) &middot; Pencarian: &ldquo;{{ $search }}&rdquo; @endif
        &middot; Total: {{ $registrations->count() }} peserta
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Nomor HP</th>
                <th>Jenis</th>
                <th>Metode Kehadiran</th>
                <th>Status Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $index => $registration)
                @php($user = $registration->user)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->name ?? '-' }}</td>
                    <td>{{ $user->email ?? '-' }}</td>
                    <td>{{ $user->whatsapp_number ?? '-' }}</td>
                    <td>{{ \App\Models\EventRegistration::PARTICIPANT_TYPE_LABELS[$registration->participant_type] ?? $registration->participant_type }}</td>
                    <td>{{ \App\Models\EventRegistration::ATTENDANCE_METHOD_LABELS[$registration->attendance_method] ?? $registration->attendance_method }}</td>
                    <td>{{ \App\Models\EventRegistration::STATUS_LABELS[$registration->status] ?? $registration->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">Tidak ada peserta yang cocok.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
