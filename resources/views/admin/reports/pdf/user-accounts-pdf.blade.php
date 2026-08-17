<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Accounts Report</title>
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
    <h1>User Accounts Report</h1>
    <div class="subtitle">Generated: {{ now()->format('d M Y, H:i') }}</div>

    <table class="data">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Account Type</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($userAccs as $v)
                <tr>
                    <td>{{ $v->name }}</td>
                    <td>{{ $v->email }}</td>
                    <td>{{ $v->acc_type == 2 ? 'Admin' : 'Employee' }}</td>
                    <td>{{ $v->status == 1 ? 'Active' : ($v->status == 0 ? 'Disabled' : '') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>