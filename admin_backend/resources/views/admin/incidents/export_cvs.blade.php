<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f0f2f7; }
    </style>
</head>
<body>
    <h2>SREA — Incident Report</h2>
    <div class="subtitle">MDRRMO San Rafael, Bulacan — Generated {{ now()->format('F j, Y — g:i A') }}</div>
    <table>
        <thead>
            <tr><th>ID</th><th>Type</th><th>Barangay</th><th>Reporter</th><th>Status</th><th>Time</th></tr>
        </thead>
        <tbody>
            @foreach ($incidents as $incident)
                <tr>
                    <td>{{ $incident->id }}</td>
                    <td>{{ $incident->type }}</td>
                    <td>{{ $incident->barangay }}</td>
                    <td>{{ $incident->reporter_name ?? 'Anonymous' }}</td>
                    <td>{{ $incident->status }}</td>
                    <td>{{ $incident->reported_at->format('M j, Y g:i A') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>