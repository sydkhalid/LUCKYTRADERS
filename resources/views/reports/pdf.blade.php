@php
    $formatValue = fn ($value, $type) => \App\Http\Controllers\AdvancedReportController::formatForView($value, $type);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportData['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        .header { border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 14px; }
        .title { font-size: 18px; font-weight: bold; margin: 0; }
        .muted { color: #64748b; font-size: 10px; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .summary td { border: 1px solid #e5e7eb; padding: 8px; }
        .summary .label { color: #64748b; font-size: 10px; }
        .summary .value { font-size: 13px; font-weight: bold; margin-top: 3px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #e5e7eb; padding: 6px; }
        table.data th { background: #f1f5f9; text-align: left; font-size: 9px; text-transform: uppercase; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">{{ $reportData['title'] }}</p>
        <p class="muted">
            Period: {{ \Carbon\Carbon::parse($filters['from_date'])->format('d M Y') }}
            to {{ \Carbon\Carbon::parse($filters['to_date'])->format('d M Y') }}
            | Generated: {{ $generatedAt->format('d M Y h:i A') }}
        </p>
    </div>

    <table class="summary">
        <tr>
            @foreach ($reportData['summaryCards'] as $card)
                <td>
                    <div class="label">{{ $card['label'] }}</div>
                    <div class="value">{{ $formatValue($card['value'], $card['type']) }}</div>
                </td>
                @if ($loop->iteration % 4 === 0 && ! $loop->last)
                    </tr><tr>
                @endif
            @endforeach
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                @foreach ($reportData['columns'] as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($reportData['rows'] as $row)
                <tr>
                    @foreach ($reportData['columns'] as $column)
                        <td>{{ $formatValue(data_get($row, $column['key']), $column['type']) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($reportData['columns']) }}">No report data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
