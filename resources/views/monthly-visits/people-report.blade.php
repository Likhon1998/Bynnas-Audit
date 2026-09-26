<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Who visits where · {{ $scopeTitle }} · {{ $monthLabel }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 13px; margin: 16px 0 6px; color: #1e3a5f; }
        p { margin: 0 0 8px; color: #475569; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a5f; color: #fff; border: 1px solid #16324f; padding: 6px 5px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { border: 1px solid #cbd5e1; padding: 5px; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        .center { text-align: center; }
        .type { display: inline-block; background: #ecfdf5; color: #065f46; border-radius: 8px; padding: 1px 6px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Who visits where</h1>
    <p>FY {{ $plan->fy_label }} · {{ $monthLabel }} · {{ $scopeTitle }}</p>
    <p>Last audit is the month that place was visited before this schedule.</p>

    <h2>{{ $monthLabel }}</h2>
    <table>
        <thead>
        <tr>
            <th class="center">#</th>
            <th>Shakha / place</th>
            <th>Type</th>
            <th>Person</th>
            <th>Position</th>
            <th>From</th>
            <th>To</th>
            <th class="center">Days</th>
            <th>Last audit</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($shakhaRows as $row)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td><strong>{{ $row['place'] }}</strong></td>
                <td><span class="type">{{ $row['type'] }}</span></td>
                <td>
                    @forelse ($row['people'] as $person)
                        {{ $person['name'] }}@if (! $loop->last)<br>@endif
                    @empty
                        —
                    @endforelse
                </td>
                <td>
                    @forelse ($row['people'] as $person)
                        {{ $person['title'] !== '' ? $person['title'] : '—' }}@if (! $loop->last)<br>@endif
                    @empty
                        —
                    @endforelse
                </td>
                <td>{{ $row['from'] }}</td>
                <td>{{ $row['to'] }}</td>
                <td class="center">{{ $row['days'] > 0 ? $row['days'] : '—' }}</td>
                <td>{{ $row['last_audit'] }}</td>
            </tr>
        @empty
            <tr><td colspan="9">No visits in this month.</td></tr>
        @endforelse
    </tbody>
    </table>
</body>
</html>
