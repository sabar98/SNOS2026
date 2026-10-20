<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Artikel</title>
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
            background: #EDE9FE;
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
    <h1>Daftar Artikel — {{ $seminarName }}</h1>
    <p class="meta">
        Dicetak: {{ $generatedAt->locale('id')->translatedFormat('d F Y, H:i') }} WIB
        @if($search) &middot; Pencarian: &ldquo;{{ $search }}&rdquo; @endif
        &middot; Total: {{ $articles->count() }} artikel
    </p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Penulis</th>
                <th>Instansi</th>
                <th>Nomor HP/WhatsApp</th>
                <th>Judul Artikel</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($articles as $index => $article)
                @php($user = $article->eventRegistration->user)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->name ?? '-' }}</td>
                    <td>{{ $user->institution ?? $article->eventRegistration->institution ?? '-' }}</td>
                    <td>{{ $user->whatsapp_number ?? '-' }}</td>
                    <td>{{ $article->title }}</td>
                    <td>{{ \App\Models\Article::STATUS_LABELS[$article->status] ?? $article->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">Tidak ada artikel yang cocok.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
