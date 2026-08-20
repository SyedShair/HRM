@extends('layouts.default')
@php
    // Branding: pulled from the Settings page (App name / logo).
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
.stats_d table td .badge,
td .badge{
    white-space:normal;
    display:inline-block;
    max-width:100%;
}

/* ================= COMPANY FILTER + PRELOADER ================= */
.dashboard-preloader{
    display:flex;
    align-items:center;
    justify-content:center;
    padding:60px 0;
}
.dashboard-preloader .spinner{
    width:40px;
    height:40px;
    border-radius:50%;
    border:4px solid #e5e7eb;
    border-top-color:#28a745;
    animation:dash-spin .8s linear infinite;
}
@keyframes dash-spin{
    to{ transform:rotate(360deg); }
}
#dashboardContent{
    opacity:1;
    transition:opacity .15s ease;
}
#dashboardContent.loading{
    opacity:.4;
    pointer-events:none;
}
.company-filter-wrap{
    display:flex;
    justify-content:flex-end;
}
.company-filter-wrap .form-group{
    min-width:240px;
    margin-bottom:0;
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
    .company-filter-wrap{
        justify-content:flex-start;
        margin-top:10px;
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
        <div class="col-md-8">
            <h2 class="page-title">{{ __('Dashboard') }}</h2>
        </div>
        <div class="col-md-4">
            <div class="company-filter-wrap">
                <div class="form-group">
                    <label for="companySelect" class="sr-only">{{ __('Company') }}</label>
                    <select id="companySelect" class="form-control">
                        @forelse($companies as $company)


                        
                            <option value="{{ $company->id }}" @selected($company->id == $defaultCompanyId)>
                                {{ $company->company }}
                            </option>
                        @empty
                            <option value="">{{ __('No companies found') }}</option>
                        @endforelse

                       
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div id="dashboardPreloader" class="dashboard-preloader">
        <div class="spinner"></div>
    </div>

    <div id="dashboardContent" style="display:none;">

        <div id="infoBoxes"></div>

        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('Newest Employees') }}</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body" id="newestEmployees"></div>
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
                    <div class="box-body" id="recentAttendance"></div>
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
                    <div class="box-body" id="recentLeaves"></div>
                </div>
            </div>
        </div>

    </div>

    <div id="birthdayContainer"></div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function () {
    var initialCompanyId = $('#companySelect').val();
    if (initialCompanyId) {
        loadDashboard(initialCompanyId);
    } else {
        $('#dashboardPreloader').hide();
        $('#dashboardContent').show();
    }

    $('#companySelect').on('change', function () {
        loadDashboard($(this).val());
    });
});

function loadDashboard(companyId) {
    if (!companyId) return;

    $('#dashboardPreloader').show();
    $('#dashboardContent').hide().addClass('loading');

    $.ajax({
        url: "{{ route('dashboard.ajaxData') }}",
        method: 'GET',
        data: { company_id: companyId },
        dataType: 'json',
        success: function (res) {
            $('#infoBoxes').html(res.info_boxes);
            $('#newestEmployees').html(res.newest_employees);
            $('#recentAttendance').html(res.recent_attendance);
            $('#recentLeaves').html(res.recent_leaves);
            $('#birthdayContainer').html(res.birthday);

            var shown = sessionStorage.getItem('birthday_shown');
            if (!shown && $('#birthdayCelebration').length) {
                $('#birthdayCelebration').css('display', 'flex').hide().fadeIn(300);
                sessionStorage.setItem('birthday_shown', '1');
                setTimeout(function () {
                    $('#birthdayCelebration').fadeOut(500);
                }, 6000);
            }
        },
        error: function () {
            $('#dashboardContent').html('<p class="text-danger">{{ __("Failed to load dashboard data.") }}</p>');
        },
        complete: function () {
            $('#dashboardPreloader').hide();
            $('#dashboardContent').show().removeClass('loading');
        }
    });
}

function closeBirthday(){
    $('#birthdayCelebration').fadeOut(400);
}
</script>
@endsection