@php
$days = $days ?? collect();
@endphp

@extends('layouts.default')
@php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
@endphp
@section('meta')
    <title>Monthly Rota | {{ $appName }}</title>
@endsection

@section('content')
<style>
:root{
    --primary:#607570;
    --primary-dark:#3E5B54;
    --bg:#EEF3F1;
    --border:#D7E1DC;
    --morning:#C9A227;
    --day:#607570;
    --evening:#7C948E;
    --night:#2B3D37;
}

.monthly-rota-wrap{ max-width:1500px; margin:auto; padding:10px 0 30px; }

.rota-head{
    background:linear-gradient(135deg,#3E5B54,#607570);
    color:white; padding:24px 28px; border-radius:18px;
    display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;
    margin-bottom:20px; box-shadow:0 10px 30px rgba(62,91,84,0.25);
}
.rota-head h2{ font-size:26px; font-weight:800; margin:0; }
.rota-head p{ opacity:0.85; font-size:13px; margin-top:4px; }

.month-nav{ display:flex; align-items:center; gap:10px; }
.month-nav a, .month-nav .download-btn{
    background:rgba(255,255,255,0.15); color:white; padding:8px 14px;
    border-radius:8px; text-decoration:none; font-weight:600; font-size:13px;
}
.month-nav a:hover, .month-nav .download-btn:hover{ background:rgba(255,255,255,0.28); }
.month-label{ font-weight:700; font-size:15px; min-width:130px; text-align:center; }

.rota-panel{
    background:#fff; border-radius:16px; overflow:auto;
    border:1px solid var(--border); box-shadow:0 8px 24px rgba(10,6,36,0.06);
}

table.month-grid{ border-collapse:collapse; width:100%; min-width:1400px; }
table.month-grid th, table.month-grid td{
    border:1px solid var(--border); padding:8px; font-size:12px; text-align:center; vertical-align:top;
}
table.month-grid thead th{
    background:linear-gradient(135deg,#f8fafc,var(--border)); font-weight:700; position:sticky; top:0; z-index:2;
}
table.month-grid td.emp-cell{
    text-align:left; font-weight:700; background:#fafcfb; position:sticky; left:0; z-index:1; min-width:170px;
}
table.month-grid td.emp-cell small{ display:block; font-weight:400; color:#6b7280; }

.cell-shift{ border-radius:6px; padding:4px 2px; font-size:10.5px; font-weight:700; color:white; }
.cell-off{ background:#f1f5f9; color:#94a3b8; }
.cell-morning{ background:var(--morning); }
.cell-day{ background:var(--day); }
.cell-evening{ background:var(--evening); }
.cell-night{ background:var(--night); }
.cell-blank{ color:#d1d5db; }

.weekend-col{ background:#fbfbfb; }
</style>

<div class="monthly-rota-wrap">

    <div class="rota-head">
        <div>
            <h2>&#128197; Monthly Rota</h2>
            <p>{{ $monthStart->format('F Y') }} &mdash; calendar view of the weekly shift pattern</p>
        </div>

        <div class="month-nav">
            <a href="{{ url('/monthly-rota') }}?month={{ $monthStart->copy()->subMonth()->format('Y-m') }}">&larr; Prev</a>
            <span class="month-label">{{ $monthStart->format('F Y') }}</span>
            <a href="{{ url('/monthly-rota') }}?month={{ $monthStart->copy()->addMonth()->format('Y-m') }}">Next &rarr;</a>
            <a class="download-btn" href="{{ route('monthly.rota.pdf') }}?month={{ $month }}" target="_blank">Download PDF</a>
        </div>
    </div>

    <div class="rota-panel">
        <table class="month-grid">
            <thead>
                <tr>
                    <th style="position:sticky; left:0; z-index:3;">Employee</th>
                    @foreach($days as $d)
                        <th class="{{ $d->isWeekend() ? 'weekend-col' : '' }}">
                            {{ $d->format('D') }}<br>{{ $d->format('d') }}
                        </th>
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
                        <td class="emp-cell">
                            {{ $fullname }}
                            <small>ID: {{ $employee->idno }}</small>
                        </td>

                        @foreach($days as $d)
                            @php
                                $inRange = (!$scheduleFrom || $d->gte($scheduleFrom))
                                    && (!$scheduleTo || $d->lte($scheduleTo));

                                $dayName = $d->format('l');
                                $shift = $empShifts->get($dayName);

                                $class = 'cell-blank';
                                $label = '&mdash;';

                                if ($inRange && $shift) {
                                    if ((int) $shift->is_off === 1 || !$shift->time_in) {
                                        $class = 'cell-off';
                                        $label = 'OFF';
                                    } else {
                                        $hour = (int) date('H', strtotime($shift->time_in));
                                        $class = $hour < 12 ? 'cell-morning' : ($hour < 16 ? 'cell-day' : ($hour < 20 ? 'cell-evening' : 'cell-night'));
                                        $label = date('H:i', strtotime($shift->time_in)) . '&ndash;' . date('H:i', strtotime($shift->time_out));
                                    }
                                }
                            @endphp
                            <td class="{{ $d->isWeekend() ? 'weekend-col' : '' }}">
                                <div class="cell-shift {{ $class }}">{!! $label !!}</div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection