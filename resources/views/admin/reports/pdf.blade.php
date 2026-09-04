<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pedalya - {{ ucfirst($reportType) }} Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 18px; margin: 0 0 2px; color: #14532d; }
        .muted { color: #666; font-size: 10px; }
        .header { border-bottom: 3px solid #14532d; padding-bottom: 8px; margin-bottom: 12px; }
        .summary { margin-bottom: 14px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 6px 8px; border: 1px solid #ddd; font-size: 11px; }
        .summary .key { background: #f0fdf4; font-weight: bold; color: #14532d; width: 25%; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #14532d; color: #fff; padding: 6px 6px; text-align: left; font-size: 9px; }
        table.data td { padding: 5px 6px; border: 1px solid #ddd; }
        table.data tr:nth-child(even) td { background: #f9fafb; }
        .footer { margin-top: 18px; text-align: center; color: #888; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pedalya Bicycle Rental — {{ ucfirst($reportType) }} Report</h1>
        <div class="muted">
            Generated: {{ now()->format('M d, Y h:i A') }} |
            Period: {{ $filters['start_date'] ?? 'All time' }} → {{ $filters['end_date'] ?? 'Present' }} |
            Report ID: {{ $report['reportId'] }}
        </div>
    </div>

    @if(isset($report['summary']))
    <div class="summary">
        <table>
            @foreach($report['summary'] as $key => $value)
                <tr>
                    <td class="key">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                    <td>
                        @if(is_numeric($value) && str_contains(strtolower($key), 'revenue'))
                            ₱{{ number_format((float) $value, 2) }}
                        @elseif(is_float($value))
                            ₱{{ number_format($value, 2) }}
                        @else
                            {{ $value }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
    @endif

    @php
        $headers = [];
        $rows = [];
        if ($reportType === 'customer') {
            $headers = ['Name', 'Student ID', 'Email', 'Phone', 'Status', 'Verified', 'Rentals', 'Total Spent', 'Joined'];
            foreach ($report['data'] as $c) {
                $joined = $c->created_at ? $c->created_at->format('M d, Y') : '—';
                $rows[] = [
                    $c->name, $c->studentId ?? '—', $c->email, $c->phoneNumber ?? '—',
                    $c->status, $c->verified ? 'Yes' : 'No',
                    $c->rentals_count ?? $c->totalRentals ?? 0,
                    '₱' . number_format((float) ($c->totalSpent ?? 0), 2),
                    $joined,
                ];
            }
        } elseif ($reportType === 'revenue') {
            $headers = ['Period', 'Rentals', 'Total Revenue', 'Avg Revenue', 'Duration (min)'];
            foreach ($report['data'] as $r) {
                $rows[] = [$r->period, (int) $r->total_rentals, '₱'.number_format((float) $r->total_revenue, 2), '₱'.number_format((float) $r->avg_revenue, 2), (int) $r->total_duration_minutes];
            }
        } elseif ($reportType === 'accident') {
            $headers = ['Accident ID', 'Rider', 'Bicycle', 'Location', 'Date/Time', 'Severity', 'Status', 'Acked', 'Action Taken'];
            foreach ($report['data'] as $a) {
                $loc = is_array($a->gpsLocation) ? $a->gpsLocation : (is_array($a->location) ? $a->location : []);
                $rows[] = [
                    $a->id,
                    $a->rider?->name ?? $a->reportedBy ?? '—',
                    $a->bicycle?->name ?? $a->bicycleId ?? '—',
                    isset($loc['lat'], $loc['lng']) ? $loc['lat'].', '.$loc['lng'] : '—',
                    $a->created_at?->format('M d, Y H:i'),
                    $a->severity, $a->status,
                    $a->acknowledged ? 'Yes' : 'No',
                    $a->actionTaken ?? '—',
                ];
            }
        } elseif ($reportType === 'incident') {
            $headers = ['ID', 'Type', 'Severity', 'Bicycle', 'Description', 'Location', 'Ack', 'Timestamp'];
            foreach ($report['data'] as $a) {
                $loc = is_array($a->gpsLocation) ? $a->gpsLocation : [];
                $rows[] = [
                    $a->id, $a->type, $a->severity,
                    $a->bicycle?->name ?? $a->bicycleId,
                    $a->description ?? '—',
                    isset($loc['lat'], $loc['lng']) ? $loc['lat'].', '.$loc['lng'] : '—',
                    $a->acknowledged ? 'Yes' : 'No',
                    $a->created_at?->format('M d, Y H:i'),
                ];
            }
        } else {
            $headers = ['Rental ID', 'Rider', 'Bicycle', 'Start', 'End', 'Duration (min)', 'Rate/Hr', 'Fee', 'Payment Method', 'Payment', 'Status'];
            foreach ($report['data'] as $r) {
                $rows[] = [
                    $r->rentalId, $r->rider?->name ?? $r->riderName,
                    $r->bicycle?->name ?? $r->bicycleName,
                    $r->startTime?->format('M d, Y H:i'), $r->endTime?->format('M d, Y H:i'),
                    $r->durationMinutes ?? 0,
                    '₱'.number_format((float) ($r->ratePerHour ?? 0), 2),
                    '₱'.number_format((float) $r->totalFee, 2),
                    $r->paymentMethod ?? '—',
                    $r->paymentStatus, $r->status,
                ];
            }
        }
    @endphp

    <table class="data">
        <thead>
            <tr>
                @foreach($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell === null ? '—' : $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Pedalya IoT Bicycle Rental Management System • Azuela Cove, Davao City • {{ now()->year }}
    </div>
</body>
</html>
