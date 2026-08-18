<!DOCTYPE html>
<html>
<head>
    <title>Weekly Shift</title>

    <style>
        body {
            font-family: DejaVu Sans, Arial;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .watermark {
            position: fixed;
            top: 30%;
            left: 15%;
            width: 450px;
            opacity: 0.06;
            z-index: -1;
        }

        .top-bar {
            background: #1f2937;
            color: #fff;
            padding: 12px 20px;
        }

        .top-bar table { width: 100%; }

        .logo { width: 100px; }

        .company-name {
            font-size: 16px;
            font-weight: bold;
        }

        .report-title {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
        }

        .info-box {
            margin: 20px;
            padding: 15px;
            background: #f3f4f6;
            border-radius: 6px;
        }

        table.data {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
        }

        table.data th {
            background: #111827;
            color: #fff;
            padding: 10px;
            font-size: 12px;
        }

        table.data td {
            padding: 10px;
            border: 1px solid #ddd;
            font-size: 12px;
        }

        .badge-work {
            color: #16a34a;
            font-weight: bold;
        }

        .badge-off {
            color: #dc2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }

        .total-box {
            width: 90%;
            margin: 15px auto;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }
    </style>
</head>

<body>
@php
                    $app = \App\Classes\table::settings()->value('app_name');
                    $logoPath = \App\Classes\table::settings()->value('app_logo');
@endphp

<!-- HEADER -->
<div class="top-bar">
    <table>
        <tr>
            <td>
<td class="company-name">{{ $app }}</td>
            </td>
             
            
            <td class="report-title">Weekly Shift Rota</td>
        </tr>
    </table>
</div>

<!-- EMPLOYEE INFO -->
<div class="info-box">
    <strong>{{ $schedule->employee }}</strong><br>
    Period:
    {{ \Carbon\Carbon::parse($schedule->datefrom)->format('d M Y') }}
    →
    {{ \Carbon\Carbon::parse($schedule->dateto)->format('d M Y') }}
</div>

@php
    $totalHours = 0;
@endphp

<!-- TABLE -->
<table class="data">
    <thead>
        <tr>
            <th>Day</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Status</th>
            <th>Hours</th>
        </tr>
    </thead>

    <tbody>

        @foreach($shifts as $shift)

            @php
                $hours = 0;

                if (!$shift->is_off && $shift->time_in && $shift->time_out) {

                    $in = \Carbon\Carbon::parse($shift->time_in);
                    $out = \Carbon\Carbon::parse($shift->time_out);

                    if ($out->lessThan($in)) {
                        $out->addDay();
                    }

                    // raw hours
                    $minutes = $in->diffInMinutes($out);
                    $hours = $minutes / 60;

                    // 🔥 UK PAYROLL ROUNDING (0.25 system)
                    $hours = round($hours * 4) / 4;

                    $totalHours += $hours;
                }
            @endphp

            <tr>
                <td><strong>{{ $shift->day }}</strong></td>
                <td>{{ $shift->time_in ?? '-' }}</td>
                <td>{{ $shift->time_out ?? '-' }}</td>

                <td>
                    @if($shift->is_off)
                        <span class="badge-off">OFF</span>
                    @else
                        <span class="badge-work">WORKING</span>
                    @endif
                </td>

                <td>
                    {{ number_format($hours, 2) }}
                </td>
            </tr>

        @endforeach

        <!-- TOTAL ROW -->
        <tr>
            <td colspan="4" style="text-align:right; font-weight:bold;">
                Total Weekly Hours
            </td>
            <td style="font-weight:bold;">
                {{ number_format($totalHours, 2) }}
            </td>
        </tr>

    </tbody>
</table>

<!-- TOTAL BOX -->
<div class="total-box">
    Total Weekly Hours: {{ number_format($totalHours, 2) }} hrs
</div>

<!-- FOOTER -->
<div class="footer">
    Generated on {{ date('d M Y h:i A') }}
</div>

</body>
</html>