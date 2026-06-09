<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; background: white; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { font-size: 10px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        thead th { background: #f3f4f6; border: 1px solid #d1d5db; padding: 6px 8px; font-size: 10px; text-align: left; }
        tbody td { border: 1px solid #e5e7eb; padding: 5px 8px; vertical-align: top; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .badge-green { color: #16a34a; font-weight: 600; }
        .badge-red   { color: #dc2626; font-weight: 600; }
        .badge-yellow{ color: #d97706; font-weight: 600; }
        .footer { margin-top: 20px; font-size: 9px; color: #999; text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $sharedExam->label ?? 'Examen partagé' }}</h1>
    <div class="meta">
        Module : {{ $sharedExam->module->title }}
        &nbsp;·&nbsp; {{ $attempts->count() }} participant(s)
        &nbsp;·&nbsp; {{ now()->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Participant</th>
                <th>Score</th>
                <th>/20</th>
                <th>%</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attempts as $attempt)
                @php $pct = $attempt->percentage; @endphp
                <tr>
                    <td>{{ $attempt->guest_name }}</td>
                    <td>{{ $attempt->score }} / {{ $attempt->total }}</td>
                    <td>{{ $attempt->grade }}</td>
                    <td class="{{ $pct >= 70 ? 'badge-green' : ($pct >= 50 ? 'badge-yellow' : 'badge-red') }}">{{ $pct }}%</td>
                    <td>{{ $attempt->finished_at ? $attempt->finished_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">Généré par Mnemo · {{ now()->format('d/m/Y à H:i') }}</div>
</body>
</html>
