@extends('layouts.default')
@php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
    $isEdit = isset($schedule);
@endphp
@section('meta')
    <title>{{ $isEdit ? 'Edit Schedule' : 'New Schedule' }} | {{ $appName }}</title>
@endsection

@section('styles')
<link href="{{ asset('/assets/vendor/air-datepicker/dist/css/datepicker.min.css') }}" rel="stylesheet">
<style>
    .box.box-success {
        border-radius: 10px;
        border-top: 3px solid #16a34a;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .box-header.with-border {
        font-size: 15px;
        font-weight: 600;
        padding: 14px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fafafa;
    }
    .box-body { padding: 18px; }

    .page-title { font-weight: 700; color: #111827; margin-bottom: 18px; }

    /* ================= EMPLOYEE BANNER ================= */

    .employee-banner {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
        padding: 14px 16px;
        background: #f9fafb;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        flex-wrap: wrap;
    }
    .employee-banner img {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e5e7eb;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        flex-shrink: 0;
    }
    .employee-banner .name { font-size: 16px; font-weight: 700; color: #111827; }
    .employee-banner .meta { font-size: 13px; color: #6b7280; margin-top: 2px; }

    /* ================= HOURS ROW ================= */

    .hours-row {
        display: flex;
        align-items: flex-end;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 8px;
    }
    .hours-row .field { margin-bottom: 0; min-width: 200px; flex: 1 1 200px; }
    #auto-fill-btn {
        height: 38px;
        white-space: nowrap;
        border-radius: 7px !important;
    }

    .field-hint {
        color: #6b7280;
        font-size: 12px;
        margin-top: 6px;
        line-height: 1.5;
    }

    /* ================= ROTA GRID (desktop table) ================= */

    .rota-grid-wrap { overflow-x: auto; }

    .rota-grid { width: 100%; border-collapse: collapse; margin-top: 6px; min-width: 480px; }
    .rota-grid th {
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        padding: 9px 10px;
        border-bottom: 2px solid #e5e7eb;
        white-space: nowrap;
    }
    .rota-grid td {
        padding: 11px 10px;
        border-bottom: 1px solid #f0f1f3;
        vertical-align: middle;
    }
    .rota-grid tr.day-off td { opacity: .55; }
    .rota-grid .day-name { font-weight: 700; color: #111827; }
    .rota-grid input[type="time"] {
        padding: 7px 9px;
        border-radius: 7px;
        border: 1px solid #d1d5db;
        width: 100%;
        max-width: 150px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .rota-grid input[type="time"]:focus {
        outline: none;
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22,163,74,0.12);
    }
    .rota-grid input[type="time"]:disabled {
        background: #f3f4f6;
        cursor: not-allowed;
    }
    .day-off-toggle { display: flex; align-items: center; gap: 6px; font-size: 13px; }
    .day-off-toggle input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }

    #hours-summary {
        font-size: 14px;
        font-weight: 700;
        margin-top: 16px;
        padding: 12px 16px;
        border-radius: 8px;
    }
    #hours-summary.ok { background: #dcfce7; color: #166534; }
    #hours-summary.over { background: #fee2e2; color: #991b1b; }

    .action.align-right { text-align: right; padding: 18px 0 4px; }

    /* ================= RESPONSIVE ================= */

    @media (max-width: 767px) {
        .box-body { padding: 14px; }

        .employee-banner { padding: 12px; }
        .employee-banner img { width: 48px; height: 48px; }

        .hours-row { flex-direction: column; align-items: stretch; }
        .hours-row .field { width: 100%; min-width: 0; }
        #auto-fill-btn { width: 100%; justify-content: center; }

        .two.fields { flex-direction: column; gap: 12px; }

        /* Convert the weekly rota grid into stacked day cards */
        .rota-grid-wrap { overflow-x: visible; }

        .rota-grid, .rota-grid thead, .rota-grid tbody,
        .rota-grid tr, .rota-grid th, .rota-grid td {
            display: block;
            width: 100%;
        }
        .rota-grid { min-width: 0; }
        .rota-grid thead { display: none; }

        .rota-grid tr {
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 14px;
            background: #ffffff;
        }
        .rota-grid tr.day-off { background: #fafafa; }

        .rota-grid td {
            border: none;
            padding: 6px 0;
        }
        .rota-grid td[data-label]::before {
            content: attr(data-label);
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9ca3af;
            margin-bottom: 3px;
        }
        .rota-grid .day-name { font-size: 14px; margin-bottom: 6px; }
        .rota-grid input[type="time"] { max-width: none; }

        .action.align-right { text-align: stretch; }
        .action.align-right .ui.button {
            display: block;
            width: 100%;
            margin: 0 0 8px 0 !important;
            text-align: center;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title">{{ $isEdit ? __('Edit Weekly Schedule') : __('New Weekly Schedule') }}</h2>
        </div>
    </div>

    @if ($errors->any())
        <div class="ui error message">
            <i class="close icon"></i>
            <div class="header">{{ __('There were some errors with your submission') }}</div>
            <ul class="list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('error'))
        <div class="ui negative message"><i class="close icon"></i>{{ session('error') }}</div>
    @endif

    <div class="employee-banner">
        <img src="{{ $employee->avatar ? asset('storage/'.$employee->avatar) : asset('/assets/images/default.png') }}" alt="">
        <div>
            <div class="name">{{ $employee->lastname }}, {{ $employee->firstname }}</div>
            <div class="meta">
                {{ $companyData->jobposition ?? '' }}
                @if($companyData->company ?? false) &middot; {{ $companyData->company }} @endif
            </div>
        </div>
    </div>

    <form action="{{ $isEdit ? url('schedules/update') : url('schedules/add') }}" method="post" class="ui form" id="rota-form">
        @csrf
        @if($isEdit)
            <input type="hidden" name="id" value="{{ $schedule->id }}">
        @else
            <input type="hidden" name="reference" value="{{ $employee->id }}">
        @endif

        <div class="box box-success">
            <div class="box-header with-border">{{ __('Schedule Period & Hours') }}</div>
            <div class="box-body">
                <div class="two fields">
                    <div class="field">
                        <label>{{ __('From') }}</label>
                        <input type="text" name="datefrom" class="airdatepicker" placeholder="Date" value="{{ old('datefrom', $schedule->datefrom ?? '') }}">
                    </div>
                    <div class="field">
                        <label>{{ __('To') }}</label>
                        <input type="text" name="dateto" class="airdatepicker" placeholder="Date" value="{{ old('dateto', $schedule->dateto ?? '') }}">
                    </div>
                </div>

                <div class="hours-row">
                    <div class="field">
                        <label>{{ __('Weekly Hours Allowed') }}</label>
                        <input type="number" step="0.25" min="0" max="168" id="weekly_hours" name="weekly_hours"
                               value="{{ old('weekly_hours', $schedule->hours ?? '') }}" placeholder="e.g. 40">
                    </div>
                    <button type="button" id="auto-fill-btn" class="ui button basic small">
                        <i class="ui magic icon"></i>{{ __('Auto-fill from weekly hours') }}
                    </button>
                </div>
                <p class="field-hint">
                    {{ __('Auto-fill splits the entered hours evenly across the days marked as working days below, starting at 09:00. You can still adjust individual days afterward. The rota cannot be saved if the total scheduled hours go above the Weekly Hours Allowed figure.') }}
                </p>
            </div>
        </div>

        <div class="box box-success">
            <div class="box-header with-border">{{ __('Weekly Rota') }}</div>
            <div class="box-body">
                <div class="rota-grid-wrap">
                <table class="rota-grid" id="rota-grid">
                    <thead>
                        <tr>
                            <th style="width:16%">{{ __('Day') }}</th>
                            <th style="width:20%">{{ __('Day Off') }}</th>
                            <th style="width:32%">{{ __('Time In') }}</th>
                            <th style="width:32%">{{ __('Time Out') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                            @php
                                $shift = $shifts[$day] ?? null;
                                $dayOff = $shift ? (bool) $shift->is_off : false;
                                $timeIn = $shift && $shift->time_in ? substr($shift->time_in, 0, 5) : '';
                                $timeOut = $shift && $shift->time_out ? substr($shift->time_out, 0, 5) : '';
                            @endphp
                            <tr class="{{ $dayOff ? 'day-off' : '' }}" data-day-row data-day="{{ $day }}">
                                <td class="day-name" data-label="{{ __('Day') }}">{{ $day }}</td>
                                <td data-label="{{ __('Day Off') }}">
                                    <div class="day-off-toggle">
                                        <input type="checkbox" class="day-off-checkbox" name="is_off[]" value="{{ $day }}" @checked($dayOff)>
                                        <span>{{ __('Off') }}</span>
                                    </div>
                                </td>
                                <td data-label="{{ __('Time In') }}">
                                    <input type="time" name="time_in[{{ $day }}]" value="{{ $timeIn }}" class="time-in-input" @disabled($dayOff)>
                                </td>
                                <td data-label="{{ __('Time Out') }}">
                                    <input type="time" name="time_out[{{ $day }}]" value="{{ $timeOut }}" class="time-out-input" @disabled($dayOff)>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                <div id="hours-summary" class="ok"></div>
            </div>
        </div>

        <div class="action align-right">
            <button type="submit" class="ui green button small" id="submit-btn"><i class="ui checkmark icon"></i>{{ $isEdit ? __('Update Schedule') : __('Save Schedule') }}</button>
            <a href="{{ url('staff-rota') }}" class="ui grey button small"><i class="ui times icon"></i>{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="{{ asset('/assets/vendor/air-datepicker/dist/js/datepicker.min.js') }}"></script>
<script src="{{ asset('/assets/vendor/air-datepicker/dist/js/i18n/datepicker.en.js') }}"></script>
<script>
$('.airdatepicker').datepicker({ language: 'en', dateFormat: 'yyyy-mm-dd', autoClose: true });

document.querySelectorAll('.day-off-checkbox').forEach(function (cb) {
    cb.addEventListener('change', function () {
        var row = this.closest('[data-day-row]');
        var timeIn = row.querySelector('.time-in-input');
        var timeOut = row.querySelector('.time-out-input');
        timeIn.disabled = this.checked;
        timeOut.disabled = this.checked;
        row.classList.toggle('day-off', this.checked);
        if (this.checked) { timeIn.value = ''; timeOut.value = ''; }
        calculateAndValidateHours();
    });
});

document.querySelectorAll('.time-in-input, .time-out-input').forEach(function (el) {
    el.addEventListener('change', calculateAndValidateHours);
});

document.getElementById('weekly_hours').addEventListener('input', calculateAndValidateHours);

document.getElementById('auto-fill-btn').addEventListener('click', function () {
    var weeklyHours = parseFloat(document.getElementById('weekly_hours').value);
    if (!weeklyHours || weeklyHours <= 0) {
        alert('{{ __("Enter Weekly Hours Allowed first.") }}');
        return;
    }

    var workingRows = Array.from(document.querySelectorAll('[data-day-row]')).filter(function (row) {
        return !row.querySelector('.day-off-checkbox').checked;
    });

    if (workingRows.length === 0) {
        alert('{{ __("Mark at least one day as a working day first.") }}');
        return;
    }

    var perDayHours = Math.floor((weeklyHours / workingRows.length) * 4) / 4;
    var perDayMinutes = Math.round(perDayHours * 60);

    workingRows.forEach(function (row) {
        var startMinutes = 9 * 60;
        var endMinutes = startMinutes + perDayMinutes;
        row.querySelector('.time-in-input').value = minutesToTimeString(startMinutes);
        row.querySelector('.time-out-input').value = minutesToTimeString(endMinutes % (24 * 60));
    });

    calculateAndValidateHours();
});

function minutesToTimeString(totalMinutes) {
    var h = Math.floor(totalMinutes / 60) % 24;
    var m = totalMinutes % 60;
    return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
}

function calculateAndValidateHours() {
    var totalMinutes = 0;
    document.querySelectorAll('[data-day-row]').forEach(function (row) {
        var off = row.querySelector('.day-off-checkbox').checked;
        if (off) return;
        var timeIn = row.querySelector('.time-in-input').value;
        var timeOut = row.querySelector('.time-out-input').value;
        if (!timeIn || !timeOut) return;
        var p1 = timeIn.split(':').map(Number);
        var p2 = timeOut.split(':').map(Number);
        var minutes = (p2[0] * 60 + p2[1]) - (p1[0] * 60 + p1[1]);
        if (minutes < 0) minutes += 24 * 60;
        totalMinutes += minutes;
    });

    var totalHours = totalMinutes / 60;
    var weeklyHours = parseFloat(document.getElementById('weekly_hours').value) || 0;
    var summary = document.getElementById('hours-summary');
    var submitBtn = document.getElementById('submit-btn');
    var totalStr = totalHours.toFixed(2);
    var allowedStr = weeklyHours.toFixed(2);

    if (weeklyHours > 0 && totalHours > weeklyHours + 0.001) {
        summary.className = 'over';
        summary.textContent = '{{ __("Scheduled") }}: ' + totalStr + ' {{ __("hrs") }} — {{ __("exceeds the weekly limit of") }} ' + allowedStr + ' {{ __("hrs. Reduce the daily times before saving.") }}';
        submitBtn.disabled = true;
    } else {
        summary.className = 'ok';
        summary.textContent = '{{ __("Scheduled") }}: ' + totalStr + ' {{ __("hrs") }}' + (weeklyHours > 0 ? ' / ' + allowedStr + ' {{ __("hrs allowed") }}' : '');
        submitBtn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', calculateAndValidateHours);
</script>
@endsection