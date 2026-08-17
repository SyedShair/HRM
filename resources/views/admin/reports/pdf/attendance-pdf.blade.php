<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
        }
        .report-header {
            border-bottom: 2px solid #16a34a;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .report-header h1 {
            font-size: 18px;
            margin: 0 0 4px 0;
            color: #111827;
        }
        .report-header .meta {
            font-size: 11px;
            color: #6b7280;
        }

        .employee-panel {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .employee-panel .emp-name {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 6px;
            color: #111827;
        }
        .employee-panel table {
            width: 100%;
            border-collapse: collapse;
        }
        .employee-panel td {
            padding: 2px 0;
            font-size: 10.5px;
        }
        .employee-panel .label {
            color: #6b7280;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            padding-right: 8px;
            white-space: nowrap;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.data-table thead th {
            background-color: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.03em;
            color: #6b7280;
            text-align: left;
            padding: 6px 8px;
        }
        table.data-table tbody td {
            border-bottom: 1px solid #f1f1f1;
            padding: 6px 8px;
        }
        table.data-table tbody tr:nth-child(even) {
            background-color: #fcfcfd;
        }

        tfoot td {
            background-color: #f9fafb;
            font-weight: bold;
            padding: 8px;
            border-top: 2px solid #e5e7eb;
        }

        .no-records {
            text-align: center;
            color: #9ca3af;
            padding: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="report-header">
        <h1>Employee Attendance Report</h1>
        <div class="meta">
            Generated {{ date('d M Y, H:i') }}
            @if($datefrom && $dateto)
                &nbsp;&middot;&nbsp; Period: {{ $datefrom }} to {{ $dateto }}
            @endif
        </div>
    </div>

    @if($selectedEmployee)
        <div class="employee-panel">
            <div class="emp-name">{{ $selectedEmployee['name'] }}</div>
            <table>
                <tr>
                    <td class="label">ID No</td>
                    <td>{{ $selectedEmployee['idno'] }}</td>
                    <td class="label">Company</td>
                    <td>{{ $selectedEmployee['company'] ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Department</td>
                    <td>{{ $selectedEmployee['department'] ?: '—' }}</td>
                    <td class="label">Job Title</td>
                    <td>{{ $selectedEmployee['jobposition'] ?: '—' }}</td>
                </tr>
            </table>
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Employee</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Hours</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $row->date }}</td>
                    <td>{{ $row->employee }}</td>
                    {{-- Plain 24-hour "HH:MM" - no AM/PM, matching the on-screen report exactly. --}}
                    <td>{{ $row->timein_display ?: '' }}</td>
                    <td>{{ $row->timeout_display ?: '' }}</td>
                    <td>{{ $row->hours !== null ? number_format($row->hours, 2) . ' hrs' : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="no-records">No attendance records found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if($data->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="4">Total Hours</td>
                    <td>{{ number_format($totalHours, 2) }}</td>
                </tr>
                @if($ratePerHour !== null)
                    <tr>
                        <td colspan="4">Per Hour Pay</td>
                        <td>&pound;{{ number_format($ratePerHour, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4">Total Pay</td>
                        <td>&pound;{{ number_format($totalPay, 2) }}</td>
                    </tr>
                @endif
            </tfoot>
        @endif
    </table>

</body>
</html>