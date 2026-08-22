@extends('layouts.default')
 @php
            // Branding: pulled from the Settings page (App name / logo).
            // Falls back to existing static defaults if nothing has been
            // configured yet, so this is safe even before anyone touches
            // the new fields.
            $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
            $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
        @endphp
@section('meta')
    <title>Weekly Shifts | {{ $appName }}</title>
    <meta name="description" content="Company-wide weekly staff rota dashboard">
@endsection

@section('styles')
    <style>
        .box.box-success {
            border-radius: 8px;
            border-top: 3px solid #16a34a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .box-header.with-border {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.02em;
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            background-color: #fafafa;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .box-body {
            padding: 20px;
            overflow-x: auto;
        }

        .week-range {
            font-size: 13px;
            font-weight: 400;
            color: #6b7280;
        }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar select {
            min-width: 180px;
        }

        #weekly-rota-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        #weekly-rota-table th {
            background: #06b6d4;
            color: #fff;
            padding: 10px;
            border: 1px solid #d1d5db;
            white-space: nowrap;
        }

        #weekly-rota-table td {
            padding: 8px;
            border: 1px solid #d1d5db;
            text-align: center;
            white-space: nowrap;
        }

        #weekly-rota-table .staff {
            text-align: left;
            font-weight: 600;
            white-space: normal;
        }

        #weekly-rota-table .staff small {
            display: block;
            font-weight: 400;
            color: #6b7280;
        }

        #weekly-rota-table .off {
            background: #f3f4f6;
            color: #6b7280;
        }

        #weekly-rota-table .morning {
            background: #fcd34d;
        }

        #weekly-rota-table .day {
            background: #7dd3fc;
        }

        #weekly-rota-table .evening {
            background: #c084fc;
            color: #fff;
        }

        #weekly-rota-table .night {
            background: #334155;
            color: #fff;
        }

        #weekly-rota-table .total {
            font-weight: 700;
            background: #dbeafe;
        }

        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 16px;
            font-size: 12px;
            color: #374151;
        }

        .legend .swatch {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 3px;
            margin-right: 5px;
            vertical-align: middle;
        }

        .empty-state {
            text-align: center;
            color: #6b7280;
            padding: 30px 0;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title">{{ __('Weekly Shifts') }}</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="ui positive message">
            <i class="close icon"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <div>
                        {{ __('Weekly Staff Rota') }}
                        <span class="week-range">
                            ({{ $weekStart->format('d M') }} - {{ $weekEnd->format('d M Y') }})
                        </span>
                    </div>

                    <div class="toolbar">
                        {{-- Simple GET-based company filter, matching how
                             /staff-rota's own ?company_id= already works -
                             no separate AJAX endpoint needed since this
                             page is cheap to reload in full. --}}
                   <form method="GET" action="{{ route('rota.weekly.dashboard') }}" id="company-filter-form">
    <select name="company_id" class="ui dropdown" onchange="document.getElementById('company-filter-form').submit()">
        @foreach($companies as $c)
            <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->company }}</option>
        @endforeach
    </select>
</form>

<a href="{{ route('rota.weekly.pdf', $companyId ? ['company_id' => $companyId] : []) }}"
   target="_blank" class="ui green small button">
    <i class="ui print icon"></i>{{ __('Export PDF') }}
</a>

                    </div>
                </div>

                <div class="box-body">
                    <div class="legend">
                        <span><span class="swatch" style="background:#fcd34d"></span>{{ __('Morning') }}</span>
                        <span><span class="swatch" style="background:#7dd3fc"></span>{{ __('Day') }}</span>
                        <span><span class="swatch" style="background:#c084fc"></span>{{ __('Evening') }}</span>
                        <span><span class="swatch" style="background:#334155"></span>{{ __('Night') }}</span>
                        <span><span class="swatch" style="background:#f3f4f6;border:1px solid #d1d5db"></span>{{ __('Off') }}</span>
                    </div>

                    @if($employees->isEmpty())
                        <p class="empty-state">{{ __('No active employees have a schedule in effect for this company right now.') }}</p>
                    @else
                        <table id="weekly-rota-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Staff') }}</th>
                                    @foreach($days as $day)
                                        <th>{{ __($day) }}</th>
                                    @endforeach
                                    <th>{{ __('Total Hours') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                    @php
                                        $totalHours = 0;
                                        $fullname = mb_strtoupper($employee->lastname.', '.$employee->firstname);
                                    @endphp
                                    <tr>
                                        <td class="staff">
                                            {{ $fullname }}
                                            <small>ID: {{ $employee->idno }}</small>
                                        </td>

                                        @foreach($days as $day)
                                            @php
                                                $shift = $weeklyShifts
                                                    ->where('reference', $employee->reference)
                                                    ->where('day', $day)
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

                                                    $label = $timeIn.' - '.$timeOut;

                                                    // Overnight shift (time_out earlier than time_in on
                                                    // the clock) crosses midnight - add a day back so it
                                                    // contributes positive hours instead of subtracting
                                                    // from the weekly total.
                                                    $shiftSeconds = strtotime($shift->time_out) - strtotime($shift->time_in);
                                                    if ($shiftSeconds < 0) {
                                                        $shiftSeconds += 24 * 3600;
                                                    }
                                                    $totalHours += $shiftSeconds / 3600;
                                                }
                                            @endphp
                                            <td class="{{ $class }}">{{ $label }}</td>
                                        @endforeach

                                        <td class="total">{{ number_format($totalHours, 1) }} hrs</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $('.toolbar .ui.dropdown').dropdown();
</script>
@endsection