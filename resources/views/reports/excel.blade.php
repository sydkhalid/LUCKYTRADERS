@php
    $formatValue = fn ($value, $type) => \App\Http\Controllers\AdvancedReportController::formatForView($value, $type);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportData['title'] }}</title>
</head>
<body>
    <table>
        <tr>
            <th colspan="{{ max(count($reportData['columns']), 2) }}">{{ $reportData['title'] }}</th>
        </tr>
        <tr>
            <td>From Date</td>
            <td>{{ $filters['from_date'] }}</td>
            <td>To Date</td>
            <td>{{ $filters['to_date'] }}</td>
        </tr>
    </table>

    <table>
        @foreach ($reportData['summaryCards'] as $card)
            <tr>
                <td>{{ $card['label'] }}</td>
                <td>{{ $formatValue($card['value'], $card['type']) }}</td>
            </tr>
        @endforeach
    </table>

    <table border="1">
        <thead>
            <tr>
                @foreach ($reportData['columns'] as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData['rows'] as $row)
                <tr>
                    @foreach ($reportData['columns'] as $column)
                        <td>{{ $formatValue(data_get($row, $column['key']), $column['type']) }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
