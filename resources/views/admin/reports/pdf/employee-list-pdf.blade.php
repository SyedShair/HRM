<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee List Report</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 2px 0; }
        .subtitle { font-size: 11px; color: #6b7280; margin-bottom: 14px; }
        table.data { border-collapse: collapse; width: 100%; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 5px 7px; text-align: left; }
        table.data thead th { background: #f3f4f6; font-size: 8px; text-transform: uppercase; }
    </style>
</head>
<body>
    <h1>Employee List Report</h1>
    <div class="subtitle">Generated: {{ now()->format('d M Y, H:i') }} &nbsp;&nbsp; Total: {{ count($empList) }} employees</div>

    <table class="data">
        <thead>
            <tr>
                <th>Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Civil Status</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Employment Type</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($empList as $et)
                <tr>
                    <td>{{ $et->lastname }}, {{ $et->firstname }} {{ $et->mi }}</td>
                    <td>{{ $et->age }}</td>
                    <td>{{ $et->gender }}</td>
                    <td>{{ $et->civilstatus }}</td>
                    <td>{{ $et->mobileno }}</td>
                    <td>{{ $et->emailaddress }}</td>
                    <td>{{ $et->employmenttype }}</td>
                    <td>{{ $et->employmentstatus }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No employees found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>