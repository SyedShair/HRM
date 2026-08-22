<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Weekly Staff Rota</title>
    <style>
        @page { margin: 24px 28px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 11px;
        }

        .pdf-header {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .pdf-header-logo {
            display: table-cell;
            vertical-align: middle;
        }
        .pdf-header-logo img {
            height: 36px;
        }

        .pdf-title {
            text-align: center;
            font-size: 20px;
            font-weight: 700;
            margin: 4px 0 4px;
        }
        .pdf-subtitle {
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 12px;
        }

        .legend {
            text-align: center;
            font-size: 9px;
            color: #374151;
            margin-bottom: 14px;
        }
        .legend .swatch {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 2px;
            margin: 0 4px 0 14px;
            vertical-align: middle;
        }
        .legend .swatch:first-child { margin-left: 0; }

        table.rota-pdf-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.rota-pdf-table th {
            background-color: #06b6d4;
            color: #ffffff;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #ffffff;
        }
        table.rota-pdf-table th:first-child {
            text-align: left;
            padding-left: 10px;
        }
        table.rota-pdf-table td {
            padding: 7px 6px;
            text-align: center;
            font-size: 10px;
            border: 1px solid #d1d5db;
            vertical-align: middle;
        }

        .staff-cell {
            text-align: left !important;
            padding-left: 10px !important;
        }
        .staff-name {
            font-weight: 700;
            font-size: 10.5px;
        }
        .staff-id {
            font-size: 8.5px;
            color: #6b7280;
        }

        /* Same time-of-day color coding as the on-screen dashboard */
        .off-cell     { background-color: #f3f4f6; color: #6b7280; }
        .morning-cell { background-color: #fcd34d; color: #1f2937; }
        .day-cell     { background-color: #7dd3fc; color: #1f2937; }
        .evening-cell { background-color: #c084fc; color: #ffffff; }
        .night-cell   { background-color: #334155; color: #ffffff; }

        .total-cell {
            background-color: #dbeafe !important;
            font-weight: 700;
            color: #1e3a8a;
        }

        .pdf-footer {
            margin-top: 18px;
            font-size: 8.5px;
            color: #9ca3af;
            text-align: right;
        }
    </style>
</head>
<body>

    @php
        $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
        $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
        $appLogoPath = !empty($appSettings->app_logo) ? storage_path('app/public/'.$appSettings->app_logo) : null;
        $weekStart = \Carbon\Carbon::now(config('app.timezone'))->startOfWeek(\Carbon\Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
    @endphp

    <div class="pdf-header">
        <div class="pdf-header-logo">
            @if($appLogoPath && file_exists($appLogoPath))
                <img src="{{ $appLogoPath }}" alt="{{ $appName }}">
            @else
                <strong>{{ $appName }}</strong>
            @endif
        </div>
    </div>

    <div class="pdf-title">Weekly Staff Rota</div>
    <div class="pdf-subtitle">{{ $weekStart->format('d M') }} &ndash; {{ $weekEnd->format('d M Y') }}</div>

    <div class="legend">
        <span class="swatch" style="background:#fcd34d"></span>Morning
        <span class="swatch" style="background:#7dd3fc"></span>Day
        <span class="swatch" style="background:#c084fc"></span>Evening
        <span class="swatch" style="background:#334155"></span>Night
        <span class="swatch" style="background:#f3f4f6;border:1px solid #d1d5db"></span>Off
    </div>

    <table class="rota-pdf-table">
        <thead>
            <tr>
                <th style="width:16%;">Staff</th>
                @foreach($days as $day)
                    <th>{{ $day }}</th>
                @endforeach
                <th style="width:9%;">Total Hours</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                @php
                    $totalHours = 0;
                    $fullname = mb_strtoupper($employee->lastname.', '.$employee->firstname);
                @endphp
                <tr>
                    <td class="staff-cell">
                        <div class="staff-name">{{ $fullname }}</div>
                        <div class="staff-id">ID: {{ $employee->idno }}</div>
                    </td>
                    @foreach($days as $day)
                        @php
                            $shift = $weeklyShifts
                                ->where('reference', $employee->reference)
                                ->where('day', $day)
                                ->first();

                            $class = 'off-cell';
                            $label = 'OFF';

                            if ($shift && $shift->is_off == 0) {
                                $timeIn = date('H:i', strtotime($shift->time_in));
                                $timeOut = date('H:i', strtotime($shift->time_out));
                                $hour = (int) date('H', strtotime($shift->time_in));

                                if ($hour < 12) {
                                    $class = 'morning-cell';
                                } elseif ($hour < 16) {
                                    $class = 'day-cell';
                                } elseif ($hour < 20) {
                                    $class = 'evening-cell';
                                } else {
                                    $class = 'night-cell';
                                }

                                $label = $timeIn.' - '.$timeOut;

                                // Overnight shift crosses midnight - add a day
                                // back so it contributes positive hours.
                                $shiftSeconds = strtotime($shift->time_out) - strtotime($shift->time_in);
                                if ($shiftSeconds < 0) {
                                    $shiftSeconds += 24 * 3600;
                                }
                                $totalHours += $shiftSeconds / 3600;
                            }
                        @endphp
                        <td class="{{ $class }}">{{ $label }}</td>
                    @endforeach
                    <td class="total-cell">{{ number_format($totalHours, 1) }} hrs</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($days) + 2 }}" style="text-align:center; color:#9ca3af; padding:20px;">
                        No staff currently have an active schedule.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pdf-footer">
        Generated {{ \Carbon\Carbon::now(config('app.timezone'))->format('d M Y H:i') }}
    </div>

</body>
</html>