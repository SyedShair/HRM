<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body{ font-family: DejaVu Sans, sans-serif; font-size:9px; color:#111; }
    h2{ margin:0 0 4px; font-size:16px; }
    p.sub{ margin:0 0 14px; color:#555; }
    table{ border-collapse:collapse; width:100%; }
    th, td{ border:1px solid #ccc; padding:3px; text-align:center; }
    th{ background:#3E5B54; color:#fff; font-size:8px; }
    td.emp{ text-align:left; font-weight:bold; white-space:nowrap; }
    td.emp small{ display:block; font-weight:normal; color:#666; }
    .off{ background:#f1f5f9; color:#94a3b8; }
    .morning{ background:#C9A227; color:#fff; }
    .day{ background:#607570; color:#fff; }
    .evening{ background:#7C948E; color:#fff; }
    .night{ background:#2B3D37; color:#fff; }
    .blank{ color:#ccc; }
</style>
</head>
<body>

    <h2>Monthly Rota &mdash; {{ $monthStart->format('F Y') }}</h2>
    <p class="sub">Generated {{ now()->format('d M Y, H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                @foreach($days as $d)
                    <th>{{ $d->format('D') }}<br>{{ $d->format('d') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
                @php
                    $fullname = strtoupper($employee->lastname . ', ' . $employee->firstname);
                    $empShifts = $weeklyShifts->where('reference', $employee->reference)->keyBy('day');
                    $scheduleFrom = $employee->datefrom ? \Carbon\Carbon::parse($employee->datefrom) : null;
                    $scheduleTo = $employee->dateto ? \Carbon\Carbon::parse($employee->dateto) : null;
                @endphp
                <tr>
                    <td class="emp">
                        {{ $fullname }}
                        <small>ID: {{ $employee->idno }}</small>
                    </td>

                    @foreach($days as $d)
                        @php
                            $inRange = (!$scheduleFrom || $d->gte($scheduleFrom))
                                && (!$scheduleTo || $d->lte($scheduleTo));

                            $dayName = $d->format('l');
                            $shift = $empShifts->get($dayName);

                            $class = 'blank';
                            $label = '-';

                            if ($inRange && $shift) {
                                if ((int) $shift->is_off === 1 || !$shift->time_in) {
                                    $class = 'off';
                                    $label = 'OFF';
                                } else {
                                    $hour = (int) date('H', strtotime($shift->time_in));
                                    $class = $hour < 12 ? 'morning' : ($hour < 16 ? 'day' : ($hour < 20 ? 'evening' : 'night'));
                                    $label = date('H:i', strtotime($shift->time_in)) . '-' . date('H:i', strtotime($shift->time_out));
                                }
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