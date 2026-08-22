@extends('layouts.default')
 @php
            $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
            $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
        @endphp
@section('meta')
    <title>Monthly Shifts | {{ $appName }}</title>
    <meta name="description" content="Company-wide monthly staff rota calendar">
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

        .toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar select {
            min-width: 180px;
        }

        .month-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .month-nav a {
            color: #16a34a;
        }

        #monthly-rota-table {
            border-collapse: collapse;
            font-size: 12px;
        }

        #monthly-rota-table th {
            background: #06b6d4;
            color: #fff;
            padding: 6px;
            border: 1px solid #d1d5db;
            white-space: nowrap;
            position: sticky;
            top: 0;
        }

        #monthly-rota-table td {
            padding: 6px;
            border: 1px solid #d1d5db;
            text-align: center;
            white-space: nowrap;
        }

        #monthly-rota-table .staff {
            text-align: left;
            font-weight: 600;
            white-space: normal;
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 1;
        }

        #monthly-rota-table .staff small {
            display: block;
            font-weight: 400;
            color: #6b7280;
        }

        #monthly-rota-table .off { background: #f3f4f6; color: #6b7280; }
        #monthly-rota-table .morning { background: #fcd34d; }
        #monthly-rota-table .day { background: #7dd3fc; }
        #monthly-rota-table .evening { background: #c084fc; color: #fff; }
        #monthly-rota-table .night { background: #334155; color: #fff; }

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
            <h2 class="page-title">{{ __('Monthly Shifts') }}</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <div class="month-nav">
                        <a href="{{ route('monthly.rota', array_filter(['month' => $monthStart->copy()->subMonth()->format('Y-m'), 'company_id' => $companyId])) }}">
                            <i class="ui chevron left icon"></i>
                        </a>
                        <strong>{{ $monthStart->format('F Y') }}</strong>
                        <a href="{{ route('monthly.rota', array_filter(['month' => $monthStart->copy()->addMonth()->format('Y-m'), 'company_id' => $companyId])) }}">
                            <i class="ui chevron right icon"></i>
                        </a>
                    </div>

                    <div class="toolbar">
                        <form method="GET" action="{{ route('monthly.rota') }}" id="company-filter-form">
                            <input type="hidden" name="month" value="{{ $monthStart->format('Y-m') }}">
                            <select name="company_id" class="ui dropdown" onchange="document.getElementById('company-filter-form').submit()">
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}" @selected($companyId == $c->id)>{{ $c->company }}</option>
                                @endforeach
                            </select>
                        </form>

                        <a href="{{ route('rota.weekly.dashboard').($companyId ? '?company_id='.$companyId : '') }}" class="ui basic small button">
                            <i class="ui calendar outline icon"></i>{{ __('Weekly View') }}
                        </a>

                        <a href="{{ route('monthly.rota.pdf', array_filter(['month' => $monthStart->format('Y-m'), 'company_id' => $companyId])) }}"
                           target="_blank" class="ui green small button">
                            <i class="ui print icon"></i>{{ __('Export PDF') }}
                        </a>
                    </div>
                </div>

                <div class="box-body">
                    @if($employees->isEmpty())
                        <p class="empty-state">{{ __('No active employees have a schedule in effect for this company right now.') }}</p>
                    @else
                        <table id="monthly-rota-table">
                            <thead>
                                <tr>
                                    <th class="staff">{{ __('Staff') }}</th>
                                    @foreach($dates as $date)
                                        <th>{{ $date->format('D d') }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $employee)
                                    <tr>
                                        <td class="staff">
                                            {{ mb_strtoupper($employee->lastname.', '.$employee->firstname) }}
                                            <small>ID: {{ $employee->idno }}</small>
                                        </td>

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