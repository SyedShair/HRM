<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Staff Rota</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #111827;
        }

        .logo {
            display: block;
            max-width: 100px;
            max-height: 50px;
            margin-bottom: 8px;
        }

        h2 {
            text-align: center;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #06b6d4;
            color: white;
            padding: 4px;
            border: 1px solid #d1d5db;
        }

        td {
            padding: 4px;
            border: 1px solid #d1d5db;
            text-align: center;
        }

        .staff {
            text-align: left;
            font-weight: bold;
            white-space: nowrap;
        }

        .off { background: #f3f4f6; color: #6b7280; }
        .morning { background: #fcd34d; }
        .day { background: #7dd3fc; }
        .evening { background: #c084fc; color: white; }
        .night { background: #334155; color: white; }
    </style>
</head>
<body>

@php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';

    // Use public_path() rather than an asset() URL - dompdf reads local
    // files far more reliably than it fetches remote/HTTP URLs.
    $appLogo = !empty($appSettings->app_logo)
        ? public_path('storage/'.$appSettings->app_logo)
        : public_path('assets/images/img/logo.png');
@endphp
<img class="logo" src="{{ $appLogo }}" alt="{{ $appName }} logo">

<h2>Monthly Staff Rota - {{ $monthStart->format('F Y') }}</h2>

<table>
    <thead>
        <tr>
            <th class="staff">Staff</th>
            @foreach($dates as $date)
                <th>{{ $date->format('D d') }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($employees as $employee)
            @php
                $fullname = strtoupper($employee->lastname.', '.$employee->firstname);
            @endphp
            <tr>
                <td class="staff">{{ $fullname }} ({{ $employee->idno }})</td>

                @foreach($dates as $date)
                    @php
                        $dayName = $date->format('l');
                        $shift = $weeklyShifts
                            ->where('reference', $employee->reference)
                            ->where('day', $dayName)
                            ->first();

                        $class = 'off';
                        $label = 'OFF';

                        if ($shift && $shift->is_off == 0) {
                            $timeIn = date('H:i', strtotime($shift->time_in));
                            $timeOut = date('H:i', strtotime($shift->time_out));
                            $hour = (int) date('H', strtotime($shift->time_in));

                            if ($hour < 12) {
                                $class = 'morning';
                            } elseif ($hour < 16) {
                                $class = 'day';
                            } elseif ($hour < 20) {
                                $class = 'evening';
                            } else {
                                $class = 'night';
                            }

                            $label = $timeIn.'-'.$timeOut;
                        }
                    @endphp
                    <td class="{{ $class }}">{{ $label }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>