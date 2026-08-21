@extends('layouts.default')
 @php
            // Branding: pulled from the Settings page (App name / logo).
            // Falls back to existing static defaults if nothing has been
            // configured yet, so this is safe even before anyone touches
            // the new fields.
            $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
            $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
            $appLogo = !empty($appSettings->app_logo)
                ? asset('storage/'.$appSettings->app_logo)
                : asset('/assets/images/img/logo.png');
        @endphp
@section('meta')
    <title>Today Shifts | {{ $appName }}</title>
@endsection

@section('content')

<style>
/* ========== PAGE ANIMATION ========== */
.container-fluid {
    animation: fadeIn 0.4s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ========== BOX STYLE ========== */
.box {
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    border: none;
    overflow: hidden;
}

/* ========== TABLE HOVER EFFECT ========== */
#shiftTable tbody tr {
    transition: all 0.2s ease;
}

#shiftTable tbody tr:hover {
    background: #f8fafc !important;
    transform: scale(1.01);
}

/* Running row pulse */
@keyframes pulseRow {
    0% { background-color: rgba(0,184,148,0.05); }
    50% { background-color: rgba(0,184,148,0.12); }
    100% { background-color: rgba(0,184,148,0.05); }
}

tr.running-row {
    animation: pulseRow 2s infinite;
}

/* ========== STATUS BADGES ========== */
.badge-status {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.badge-off {
    background: #ffe5e5;
    color: #d63031;
}

.badge-running {
    background: #e3fcef;
    color: #00b894;
}

.badge-upcoming {
    background: #e8f1ff;
    color: #0984e3;
}

/* ========== BUTTON ========== */
.make-attendance-btn {
    border-radius: 8px !important;
    transition: all 0.2s ease;
}

.make-attendance-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(46, 204, 113, 0.3);
}

/* ========== MODAL ========== */
.ui.modal {
    border-radius: 14px !important;
    overflow: hidden;
}

.ui.modal .header {
    background: linear-gradient(135deg, #00b894, #55efc4);
    color: white !important;
}

/* input focus */
input:focus {
    border-color: #00b894 !important;
    box-shadow: 0 0 0 2px rgba(0,184,148,0.2) !important;
}
</style>

<div class="container-fluid">

<!-- ================= MODAL ================= -->
<div class="ui modal medium add">
    <div class="header">{{ __("Add Employee Attendance") }}</div>

    <div class="content">
        <form id="add_attendance_form" action="{{ url('attendance/add-entry') }}" class="ui form add-attendance" method="post">
            @csrf

            <div class="field">
                <label>{{ __("Employee") }}</label>
                <input type="text" name="name" readonly>
            </div>

            <div class="field">
                <label>{{ __("Date") }}</label>
                <input class="airdatepicker" type="text" name="date" placeholder="0000-00-00">
            </div>

            <div class="field">
                <label>{{ __("Time IN") }}</label>
                <input class="jtimepicker" type="text" name="timein" required>
            </div>

            <div class="field">
                <label>{{ __("Time OUT") }}</label>
                <input class="jtimepicker" type="text" name="timeout">
            </div>

            <input type="hidden" name="ref">

    </div>

    <div class="actions">
        <button class="ui positive button" type="submit">
            <i class="check icon"></i> Save
        </button>

        <button class="ui cancel button" type="button">
            Cancel
        </button>
    </div>

        </form>
</div>

<!-- ================= HEADER ================= -->
<div class="row">
    <h2 class="page-title uppercase">
        {{ __('Today Shifts') }}
        <small>
            ({{ $todayDay }} - {{ \Carbon\Carbon::parse($todayDate)->format('d M Y') }})
        </small>
    </h2>
</div>

<!-- ================= TABLE ================= -->
<div class="row">
    <div class="box box-success">
        <div class="box-body">

            <table class="table table-striped table-hover" id="shiftTable">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Day') }}</th>
                        <th>{{ __('Time In') }}</th>
                        <th>{{ __('Time Out') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>

                <tbody>

                    {{--
                        $nowTime and each $shift->isPresent are now
                        computed once in SchedulesController::todayShifts()
                        rather than here. Previously this partial recomputed
                        "now" independently of the controller (a second,
                        potentially different timezone/instant) and ran a
                        fresh presence-lookup DB query for every single row
                        (N+1 queries) even though the controller had already
                        fetched $todayAttendance and never used it. Both are
                        fixed by using the values the controller now passes
                        down, so there's exactly one source of truth for
                        "now" and one query for attendance, no matter how
                        many rows are on this page.
                    --}}

                    @forelse($shifts as $index => $shift)

                        @php
                            $isOff = is_null($shift->time_in) || is_null($shift->time_out);
                            $isRunning = (!$isOff && $nowTime >= $shift->time_in && $nowTime <= $shift->time_out);
                        @endphp

                        <tr class="{{ $isRunning ? 'running-row' : '' }}">

                            <td>{{ $index + 1 }}</td>

                            <td><strong>{{ $shift->employee }}</strong></td>

                            <td>{{ $shift->day }}</td>

                            <td>
                                @if($isOff)
                                    OFF
                                @else
                                    {{ \Carbon\Carbon::parse($shift->time_in)->format('h:i A') }}
                                @endif
                            </td>

                            <td>
                                @if($isOff)
                                    OFF
                                @else
                                    {{ \Carbon\Carbon::parse($shift->time_out)->format('h:i A') }}
                                @endif
                            </td>

                            <td>
                                @if($isOff)
                                    <span class="badge-status badge-off">OFF DAY</span>
                                @else
                                    <span class="badge-status {{ $shift->isPresent ? 'badge-running' : 'badge-upcoming' }}">
                                        {{ $shift->isPresent ? 'PRESENT' : 'UPCOMING' }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($isOff)
                                    OFF
                                @else
                                <button 
                                    class="ui green small button make-attendance-btn"
                                    data-name="{{ $shift->employee }}"
                                    data-ref="{{ $shift->reference }}"
                                    data-timein="{{ $shift->time_in }}"
                                    data-timeout="{{ $shift->time_out }}"
                                >
                                    Make Attendance
                                </button>
                                @endif
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="text-center">
                                No shifts found for today
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>
    </div>
</div>

</div>

@endsection

<!-- ================= SCRIPTS ================= -->
@section('scripts')

<script>
$(document).ready(function(){

    $('#shiftTable').DataTable({
        responsive: true,
        pageLength: 10,
        lengthChange: false,
        searching: true,
        ordering: true
    });

    // ========== OPEN MODAL ==========
    $(document).on('click', '.make-attendance-btn', function(){

        let empName = $(this).data('name');
        let empRef  = $(this).data('ref');
        let timein  = $(this).data('timein');
        let timeout = $(this).data('timeout');

        let now = new Date();

// adjust to local (UK) time before ISO conversion
let date = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
            .toISOString()
            .slice(0, 10);

       // let date = now.toISOString().slice(0,10);

        $('input[name="name"]').val(empName);
        $('input[name="ref"]').val(empRef);
        $('input[name="date"]').val(date);
        $('input[name="timein"]').val(timein);
        $('input[name="timeout"]').val(timeout);

        $('.ui.modal.add')
            .transition('zoom')
            .modal({
                closable: true,
                autofocus: false
            })
            .modal('show');
    });

});
</script>

@endsection