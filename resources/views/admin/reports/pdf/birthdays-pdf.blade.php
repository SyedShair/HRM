<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Birthdays Report</title>
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
    <h1>Employee Birthdays Report</h1>
    <div class="subtitle">Generated: {{ now()->format('d M Y, H:i') }}</div>

    <table class="data">
        <thead>
            <tr>
                <th>Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Birthday</th>
                <th>Contact Number</th>
            </tr>
        </thead>
        <tbody>
            @forelse($empBday as $v)
                <tr>
                    <td>{{ $v->lastname }}, {{ $v->firstname }} {{ $v->mi }}</td>
                    <td>{{ $v->department }}</td>
                    <td>{{ $v->jobposition }}</td>
                    <td>{{ $v->birthday ? date('D, M d Y', strtotime($v->birthday)) : '' }}</td>
                    <td>{{ $v->mobileno }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>