<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #202124; font-size: 11px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { color: #5f6368; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border-bottom: 1px solid #d9dce1; padding: 7px 8px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        .numeric { text-align: right; font-family: monospace; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $company->name }} · Generated {{ $generatedAt }}</p>
    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $value)
                        <td class="{{ is_string($value) && is_numeric($value) ? 'numeric' : '' }}">{{ $value ?? '-' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headings) }}">No data available for the selected scope.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
