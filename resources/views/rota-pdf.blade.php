
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Weekly Staff Rota</title>

    <style>

        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:12px;
            color:#111827;
        }

        h2{
            text-align:center;
            margin-bottom:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th{
            background:#06b6d4;
            color:white;
            padding:10px;
            border:1px solid #d1d5db;
        }

        td{
            padding:8px;
            border:1px solid #d1d5db;
            text-align:center;
        }

        .staff{
            text-align:left;
            font-weight:bold;
        }

        .off{
            background:#f3f4f6;
            color:#6b7280;
        }

        .morning{
            background:#fcd34d;
        }

        .day{
            background:#7dd3fc;
        }

        .evening{
            background:#c084fc;
            color:white;
        }

        .night{
            background:#334155;
            color:white;
        }

        .total{
            font-weight:bold;
            background:#dbeafe;
        }

    </style>
</head>
<body>
<img class="logo"
                 src="https://www.jpingos.com/wp-content/uploads/2025/03/image-2-Photoroom.png">

<h2>Weekly Staff Rota</h2>

<table>

    <thead>

        <tr>

            <th>Staff</th>

            @foreach($days as $day)
                <th>{{ $day }}</th>
            @endforeach

            <th>Total Hours</th>

        </tr>

    </thead>

    <tbody>

    @foreach($employees as $employee)

        @php

            $totalHours = 0;

            $fullname =
                strtoupper($employee->lastname . ', ' . $employee->firstname);

        @endphp

        <tr>

            <td class="staff">
                {{ $fullname }}
                <br>
                ID: {{ $employee->idno }}
            </td>

            @foreach($days as $day)

                @php

                    $shift = $weeklyShifts
                        ->where('reference', $employee->reference)
                        ->where('day', $day)
                        ->first();

                    $class = 'off';
                    $label = 'OFF';

                    if($shift){

                        if($shift->is_off == 0){

                            $timeIn = date('H:i', strtotime($shift->time_in));
                            $timeOut = date('H:i', strtotime($shift->time_out));

                            $hour = date('H', strtotime($shift->time_in));

                            if($hour < 12){
                                $class = 'morning';
                            }
                            elseif($hour < 16){
                                $class = 'day';
                            }
                            elseif($hour < 20){
                                $class = 'evening';
                            }
                            else{
                                $class = 'night';
                            }

                            $label = $timeIn . ' - ' . $timeOut;

                            $totalHours +=
                                (strtotime($shift->time_out) - strtotime($shift->time_in)) / 3600;
                        }

                    }

                @endphp

                <td class="{{ $class }}">
                    {{ $label }}
                </td>

            @endforeach

            <td class="total">
                {{ number_format($totalHours,1) }} hrs
            </td>

        </tr>

    @endforeach

    </tbody>

</table>

</body>
</html>
