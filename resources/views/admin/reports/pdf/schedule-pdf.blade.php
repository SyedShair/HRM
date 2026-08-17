<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Schedule Report</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 2px 0; }
        .subtitle { font-size: 11px; color: #6b7280; margin-bottom: 14px; }
        table.data { border-collapse: collapse; width: 100%; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; }
        table.data thead th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
        .status-present { color: #16a34a; font-weight: bold; }
        .status-past { color: #0d9488; }
    </style>
</head>
<body>
    <h1>Employee Schedule Report</h1>
    <div class="subtitle">
        @if($employeeName) Employee: {{ $employeeName }} &nbsp;&nbsp; @endif
        Generated: {{ now()->format('d M Y, H:i') }}
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Start Time</th>
                <th>Off Time</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Hours</th>
                <th>Rest Days</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->employee }}</td>
                    <td>@if($row->intime) {{ $tf == 1 ? date('h:i A', strtotime($row->intime)) : date('H:i', strtotime($row->intime)) }} @endif</td>
                    <td>@if($row->outime) {{ $tf == 1 ? date('h:i A', strtotime($row->outime)) : date('H:i', strtotime($row->outime)) }} @endif</td>
                    <td>{{ date('D, M j, Y', strtotime($row->datefrom)) }}</td>
                    <td>{{ date('D, M j, Y', strtotime($row->dateto)) }}</td>
                    <td>{{ $row->hours }}</td>
                    <td>{{ $row->restday }}</td>
                    <td class="{{ $row->archive == '0' ? 'status-present' : 'status-past' }}">
                        {{ $row->archive == '0' ? 'Present Schedule' : 'Past Schedule' }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No records found for this filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>