<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leaves Report</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 2px 0; }
        .subtitle { font-size: 11px; color: #6b7280; margin-bottom: 14px; }
        table.data { border-collapse: collapse; width: 100%; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; }
        table.data thead th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>Employee Leaves Report</h1>
    <div class="subtitle">
        @if($employeeName) Employee: {{ $employeeName }} &nbsp;&nbsp; @endif
        @if($datefrom && $dateto) Period: {{ $datefrom }} to {{ $dateto }} @else Period: All Records @endif
        &nbsp;&nbsp; Generated: {{ now()->format('d M Y, H:i') }}
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Type</th>
                <th>Leave From</th>
                <th>Leave To</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->employee }}</td>
                    <td>{{ $row->type }}</td>
                    <td>{{ $row->leavefrom }}</td>
                    <td>{{ $row->leaveto }}</td>
                    <td>{{ $row->reason }}</td>
                    <td>{{ $row->status }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No records found for this filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>