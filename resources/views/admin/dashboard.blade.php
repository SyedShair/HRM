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
        <title>Dashboard | {{ $appName }}</title>
        <meta name="description" content="Workday dashboard, view recent attendance, recent leaves of absence, and newest employees">
    @endsection
@section('styles')
<style>

/* ================= BIRTHDAY NOTICE ================= */
/* Simplified to a clean, professional banner — no fireworks gif,
   no floating balloon spam, no pulsing/scaling animation. */

.birthday-overlay{
    position:fixed;
    inset:0;
    background: rgba(0,0,0,0.45);
    z-index:999999;
    display:none;
    align-items:center;
    justify-content:center;
}

.celebration-box{
    background:#fff;
    color:#1f2937;
    border-radius:10px;
    padding:32px 40px;
    text-align:center;
    box-shadow:0 10px 30px rgba(0,0,0,0.25);
    max-width:420px;
    width:90%;
}

.title{
    font-size:24px;
    font-weight:600;
    margin-bottom:4px;
}

.subtitle{
    font-size:14px;
    color:#6b7280;
}

.emp-card{
    margin-top:20px;
}

.emp-card img{
    width:96px;
    height:96px;
    border-radius:50%;
    border:3px solid #e5e7eb;
    object-fit:cover;
}

.age{
    font-size:14px;
    margin-top:10px;
    color:#374151;
}

.close-btn{
    margin-top:20px;
    padding:8px 20px;
    border:none;
    background:#28a745;
    color:#fff;
    border-radius:6px;
    cursor:pointer;
    font-size:14px;
}

.close-btn:hover{
    background:#218838;
}

body{
    overflow:auto !important;
}

/* ================= RESPONSIVE ================= */

.table-responsive-wrap{
    width:100%;
    overflow-x:auto;
    -webkit-overflow-scrolling:touch;
}

/* Visa badges: allow wrapping instead of being clipped by the column,
   and keep them from stretching wider than the cell. */
.stats_d table td .badge,
td .badge{
    white-space:normal;
    display:inline-block;
    max-width:100%;
}

@media (max-width: 991px){
    .info-box{
        margin-bottom:15px;
    }
}

@media (max-width: 767px){
    .page-title{
        font-size:20px;
    }

    .box-title{
        font-size:16px;
    }

    .stats_d table td{
        font-size:13px;
    }

    .name-title{
        white-space:nowrap;
    }

    .celebration-box{
        padding:24px 20px;
        width:92%;
    }

    .title{
        font-size:20px;
    }

    .emp-card img{
        width:80px;
        height:80px;
    }
}

@media (max-width: 480px){
    .title{
        font-size:18px;
    }

    .subtitle{
        font-size:13px;
    }

    .close-btn{
        width:100%;
    }
}

</style>
@endsection
    @section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
            <h2 class="page-title">{{ __('Dashboard') }}</h2>
            </div>    
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="ui icon user circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text uppercase">{{ __('Employees') }}</span>
                        <div class="progress-group">
                            <div class="progress sm">
                                <div class="progress-bar progress-bar-aqua" style="width: 100%"></div>
                            </div>
                            <div class="stats_d">
                                <table style="width: 100%;">
                                    <tbody>
                                        <tr>
                                            <td>{{ __('Regular') }}</td>
                                            <td>@isset($emp_typeR) {{ $emp_typeR }} @endisset</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Part Time') }}</td>
                                            <td>@isset($emp_typeT) {{ $emp_typeT }} @endisset </td>
                                        
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="ui icon clock outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Attendances') }}</span>
                        <div class="progress-group">
                            <div class="progress sm">
                                <div class="progress-bar progress-bar-green" style="width: 100%"></div>
                            </div>
                            <div class="stats_d">
                                <table style="width: 100%;">
                                    <tbody>
                                        <tr>
                                            <td>{{ __('Online') }}</td>
                                            <td>@isset($is_online_now) {{ $is_online_now }} @endisset</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Offline') }}</td>
                                            <td>@isset($is_offline_now) {{ $is_offline_now }} @endisset</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="info-box">
                    <span class="info-box-icon bg-orange"><i class="ui icon home"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text uppercase">{{ __('Leaves of Absence') }}</span>
                        <div class="progress-group">
                            <div class="progress sm">
                                <div class="progress-bar progress-bar-orange" style="width: 100%"></div>
                            </div>
                            <div class="stats_d">
                                <table style="width: 100%;">
                                    <tbody>
                                        <tr>
                                            <td>{{ __('Approved') }}</td>
                                            <td>@isset($emp_leaves_approve) {{ $emp_leaves_approve }} @endisset</td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Pending') }}</td>
                                            <td>@isset($emp_leaves_pending) {{ $emp_leaves_pending }} @endisset</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('Newest Employees') }}</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                    <div class="table-responsive-wrap">
                    <table class="table responsive nobordertop">
                        <thead>
                            <tr>
                                <th class="text-left">{{ __('Name') }}</th>
                                <th class="text-left">{{ __('Position') }}</th>
                                <th class="text-left">{{ __('Start Date') }}</th>
                                 <th class="text-left">{{ __('Visa Validity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($emp_all_type)
                                @foreach ($emp_all_type as $data)
                                @php
                                    $visaEnd = $data->visaend ? \Carbon\Carbon::parse($data->visaend) : null;
                                    // Carbon 3 (Laravel 12) changed diffInMonths() to return a
                                    // float with full decimal precision by default (e.g.
                                    // 6.9997523619382), unlike Carbon 2 (Laravel 6/7) which
                                    // returned a truncated integer. Round to whole months to
                                    // restore the original behaviour and stop the badge text
                                    // from overflowing.
                                    $monthsRemaining = $visaEnd ? (int) round(\Carbon\Carbon::now()->diffInMonths($visaEnd, false)) : null;

                                    if ($visaEnd === null) {
                                        $visaBadgeClass = null; // British citizen, no visa on file
                                    } elseif ($visaEnd->isPast()) {
                                        $visaBadgeClass = 'badge bg-dark'; // expired
                                    } elseif ($monthsRemaining > 6) {
                                        $visaBadgeClass = 'badge bg-success'; // green
                                    } elseif ($monthsRemaining > 3) {
                                        $visaBadgeClass = 'badge bg-warning'; // yellow
                                    } else {
                                        $visaBadgeClass = 'badge bg-danger'; // red
                                    }
                                @endphp
                                <tr>
                                    <td class="text-left name-title">{{ $data->lastname }}, {{ $data->firstname }}</td>
                                    <td class="text-left">{{ $data->jobposition }}</td>
                                    <td class="text-left">{{ date('M d, Y', strtotime($data->startdate)) }}</td>
                                    <td>
                                        <p class="uppercase">
                                            @if($visaEnd)
                                                {{ $visaEnd->format('d F Y') }}
                                                <br>
                                                @if($visaEnd->isPast())
                                                    <span class="badge bg-dark" style="color:#fff;">Expired</span>
                                                @else
                                                    <span class="{{ $visaBadgeClass }}" style="color:#fff;">
                                                        {{ $monthsRemaining }} {{ Str::plural('month', $monthsRemaining) }} remaining
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-success" style="color:#fff;">British Citizen</span>
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                                @endforeach
                            @endisset
                        </tbody>
                    </table>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('Recent Attendances') }}</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                    <div class="table-responsive-wrap">
                        <table class="table responsive nobordertop">
                        <thead>
                            <tr>
                                <th class="text-left">{{ __('Name') }}</th>
                                <th class="text-left">{{ __('Type') }}</th>
                                <th class="text-left">{{ __('Time') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($a)
                                @foreach($a as $v)
                                @if($v->timein != null && $v->timeout == null)
                                <tr>
                                    <td class="name-title">{{ $v->employee }}</td>
                                    <td>Time-In</td>
                                    <td>
                                        {{ $tf == 1 ? date('h:i:s A', strtotime($v->timein)) : date('H:i:s', strtotime($v->timein)) }}
                                    </td>
                                </tr>
                                @elseif($v->timein != null && $v->timeout != null)
                                <tr>
                                    <td class="name-title">{{ $v->employee }}</td>
                                    <td>Time-Out</td>
                                    <td>
                                        {{ $tf == 1 ? date('h:i:s A', strtotime($v->timeout)) : date('H:i:s', strtotime($v->timeout)) }}
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            @endisset
                        </tbody>
                        </table>
                    </div>
                    </div>
                </div>
            </div>
        
            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('Recent Leaves of Absence') }}</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                    <div class="table-responsive-wrap">
                    <table class="table responsive nobordertop">
                        <thead>
                            <tr>
                                <th class="text-left">{{ __('Name') }}</th>
                                <th class="text-left">{{ __('Date') }}</th>
                            </tr>
                        </thead>
                            <tbody>
                                @isset($emp_approved_leave)
                                    @foreach ($emp_approved_leave as $leaves)
                                    <tr>
                                        <td class="text-left name-title">{{ $leaves->employee }}</td>
                                        <td class="text-left">{{ date('M d, Y', strtotime($leaves->leavefrom)) }}</td>
                                    </tr>
                                    @endforeach
                                @endisset
                            </tbody>
                    </table>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
 @if($birthdays->count() > 0)

<div id="birthdayCelebration" class="birthday-overlay">

    <div class="celebration-box">

        <h1 class="title">🎉 Happy Birthday</h1>

        <p class="subtitle">Today we celebrate our team member{{ $birthdays->count() > 1 ? 's' : '' }}</p>

        @foreach($birthdays as $emp)
            <div class="emp-card">
                <img src="{{ asset($emp->avatar ?? 'assets/images/default.png') }}">
                
                <h2 style="font-size:18px; margin:8px 0 0;">
                    {{ $emp->firstname }} {{ $emp->lastname }}
                </h2>

                <p class="age">
                    Age: {{ \Carbon\Carbon::parse($emp->birthday)->age }}
                </p>
            </div>
        @endforeach

        <button class="close-btn" onclick="closeBirthday()">
            Close
        </button>

    </div>

</div>

@endif

    @endsection
    
   @section('scripts')
<script>
$(document).ready(function () {

    @if($birthdays->count() > 0)

        let shown = sessionStorage.getItem('birthday_shown');

        if(!shown){

            $('#birthdayCelebration').css('display', 'flex').hide().fadeIn(300);

            sessionStorage.setItem('birthday_shown', '1');

            setTimeout(function(){
                $('#birthdayCelebration').fadeOut(500);
            }, 6000);

        }

    @endif

});

function closeBirthday(){
    $('#birthdayCelebration').fadeOut(400);
}
</script>
@endsection