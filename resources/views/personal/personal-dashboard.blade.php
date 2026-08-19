@extends('layouts.personal')
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
        <title>My Dashboard | {{ $appName }}</title>
        <meta name="description" content="Workday my dashboard, view recent attendance, view recent leave of absence, and view previous schedules">
    @endsection

    @section('content')

@php
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Auth;
    use Carbon\Carbon;
    use App\Classes\table;

    $employee = DB::table('tbl_company_data')
        ->where('reference', Auth::user()->reference)
        ->first();

    $visaEnd = null;
    $remainingDays = null;
    $visaExpired = false;
    $visaMonthsPart = 0;
    $visaDaysPart = 0;

    if ($employee && $employee->visaend) {
        // Use the org's configured timezone
        $orgTz = table::settings()->value('timezone')
            ?: config('app.timezone', 'UTC');

        // Compare whole calendar days
        $today = Carbon::now($orgTz)->startOfDay();
        $visaEnd = Carbon::parse($employee->visaend, $orgTz)->startOfDay();

        // Positive = remaining, negative = expired, 0 = today
        $remainingDays = $today->diffInDays($visaEnd, false);
        $visaExpired = $remainingDays < 0;

        // Break the gap down into whole months + leftover days for
        // display (e.g. "3 Months, 12 Days") instead of a raw day count.
        // diff() is always taken from the earlier date to the later one
        // so the resulting interval is never inverted/negative.
        $earlier = $visaExpired ? $visaEnd : $today;
        $later = $visaExpired ? $today : $visaEnd;
        $interval = $earlier->diff($later);

        $visaMonthsPart = ($interval->y * 12) + $interval->m;
        $visaDaysPart = $interval->d;
    }
@endphp

@if($employee && $visaEnd)

    @php
        if ($remainingDays > 180) {
            $visaTone = ['accent' => '#1f9d55', 'soft' => '#e9f8ef', 'label' => __('Valid')];
        } elseif ($remainingDays > 75) {
            $visaTone = ['accent' => '#c07a12', 'soft' => '#fdf3e2', 'label' => __('Expiring Soon')];
        } elseif ($remainingDays >= 0) {
            $visaTone = ['accent' => '#c0392b', 'soft' => '#fbeceb', 'label' => __('Urgent')];
        } else {
            $visaTone = ['accent' => '#4b5563', 'soft' => '#eceef0', 'label' => __('Expired')];
        }

        $visaUrgentPulse = $remainingDays >= 0 && $remainingDays <= 14;
    @endphp

    <style>
        /* !important on every positioning property here defeats any
           conflicting rule the layout's own stylesheet might define for
           this id (that mismatch is what put the box over the content
           instead of in a clear corner). The JS at the bottom of this
           file also re-parents the element straight onto <body> on load,
           which is the real fix if the actual cause turns out to be an
           ancestor with a CSS transform - a transformed ancestor quietly
           changes what position:fixed measures itself against, so the
           box ends up "fixed" to a scrolling content wrapper instead of
           the browser viewport. Re-parenting to body sidesteps that
           regardless of which layout wrapper is doing it. */
        #visa-floating-box {
            position: fixed !important;
            right: 22px !important;
            bottom: 22px !important;
            left: auto !important;
            top: auto !important;
            margin: 0 !important;
            z-index: 99999 !important;
            width: 300px;
            max-width: calc(100vw - 32px);
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, sans-serif;
            overflow: hidden;
            transition: width .2s ease, box-shadow .2s ease;
        }

        #visa-floating-box::before {
            content: "";
            position: absolute;
            top: 0; left: 0; bottom: 0;
            width: 5px;
            background: {{ $visaTone['accent'] }};
        }

        #visa-floating-box.closed {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.22), 0 0 0 4px {{ $visaTone['soft'] }};
            animation: visaClosedGlow 2.2s ease-in-out infinite;
        }

        /* A persistent, gentle highlight on the collapsed circle so it
           doesn't just vanish into a plain grey dot once dismissed -
           it should still read as "there's something here". */
        @keyframes visaClosedGlow {
            0%, 100% { box-shadow: 0 6px 20px rgba(15, 23, 42, 0.22), 0 0 0 4px {{ $visaTone['soft'] }}; }
            50% { box-shadow: 0 6px 20px rgba(15, 23, 42, 0.22), 0 0 0 8px {{ $visaTone['soft'] }}; }
        }

        #visa-floating-box.closed::before { display: none; }

        .visa-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px 12px 18px;
            border-bottom: 1px solid #f1f5f9;
        }

        #visa-floating-box.closed .visa-header {
            border-bottom: none;
            padding: 0;
            justify-content: center;
            height: 58px;
        }

        .visa-icon-badge {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            border-radius: 8px;
            background: {{ $visaTone['soft'] }};
            color: {{ $visaTone['accent'] }};
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
        }

        .visa-header-text {
            flex: 1;
            min-width: 0;
        }

        .visa-header-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: .01em;
        }

        .visa-status-chip {
            display: inline-block;
            margin-top: 2px;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: {{ $visaTone['accent'] }};
            background: {{ $visaTone['soft'] }};
            padding: 2px 8px;
            border-radius: 20px;
        }

        .toggle-btn {
            width: 22px;
            height: 22px;
            border: none;
            border-radius: 6px;
            background: #f1f5f9;
            color: #475569;
            font-size: 15px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .toggle-btn:hover { background: #e2e8f0; }

        #visa-floating-box.closed .toggle-btn {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: {{ $visaTone['accent'] }};
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.25);
        }

        #visa-floating-box.closed .toggle-btn:hover {
            filter: brightness(1.08);
        }

        .visa-content {
            padding: 14px 18px 16px;
        }

        #visa-floating-box.closed .visa-content { display: none; }

        .visa-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 10px;
            padding: 5px 0;
            font-size: 12.5px;
            border-bottom: 1px dashed #f1f5f9;
        }

        .visa-row:last-of-type { border-bottom: none; }

        .visa-row-label {
            color: #64748b;
            font-weight: 500;
        }

        .visa-row-value {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
        }

        .visa-remaining-block {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            background: {{ $visaTone['soft'] }};
            text-align: center;
        }

        .visa-remaining-figure {
            font-size: 19px;
            font-weight: 800;
            color: {{ $visaTone['accent'] }};
            letter-spacing: -.01em;
        }

        .visa-remaining-caption {
            margin-top: 2px;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: .03em;
            text-transform: uppercase;
            color: {{ $visaTone['accent'] }};
            opacity: .8;
        }

        @if($visaUrgentPulse)
        .visa-icon-badge {
            animation: visaPulse 1.6s ease-in-out infinite;
        }

        @keyframes visaPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(192, 57, 43, 0.25); }
            50% { box-shadow: 0 0 0 6px rgba(192, 57, 43, 0); }
        }
        @endif

        @media (max-width: 480px) {
            #visa-floating-box { right: 14px !important; bottom: 14px !important; width: calc(100vw - 28px); }
        }

        @media (prefers-reduced-motion: reduce) {
            #visa-floating-box.closed { animation: none; }
            .visa-icon-badge { animation: none !important; }
        }
    </style>

    <div id="visa-floating-box">

        <div class="visa-header">
            <div class="visa-icon-badge">V</div>
            <div class="visa-header-text">
                <div class="visa-header-title">{{ __('Visa Status') }}</div>
                <span class="visa-status-chip">{{ $visaTone['label'] }}</span>
            </div>
            <button type="button" class="toggle-btn" onclick="toggleVisaBox()">&minus;</button>
        </div>

        <div class="visa-content">

            <div class="visa-row">
                <span class="visa-row-label">{{ __('Name') }}</span>
                <span class="visa-row-value">{{ Auth::user()->name }}</span>
            </div>

            <div class="visa-row">
                <span class="visa-row-label">{{ __('Visa Type') }}</span>
                <span class="visa-row-value">{{ $employee->visastatus ?? 'N/A' }}</span>
            </div>

            <div class="visa-row">
                <span class="visa-row-label">{{ __('Expiry Date') }}</span>
                <span class="visa-row-value">{{ $visaEnd->format('d M Y') }}</span>
            </div>

            <div class="visa-remaining-block">
                @if($remainingDays == 0)
                    <div class="visa-remaining-figure">{{ __('Expires Today') }}</div>
                @elseif($visaMonthsPart > 0 && $visaDaysPart > 0)
                    <div class="visa-remaining-figure">
                        {{ $visaMonthsPart }} {{ __('Months') }}, {{ $visaDaysPart }} {{ __('Days') }}
                    </div>
                    <div class="visa-remaining-caption">{{ $visaExpired ? __('Overdue') : __('Remaining') }}</div>
                @elseif($visaMonthsPart > 0)
                    <div class="visa-remaining-figure">{{ $visaMonthsPart }} {{ __('Months') }}</div>
                    <div class="visa-remaining-caption">{{ $visaExpired ? __('Overdue') : __('Remaining') }}</div>
                @else
                    <div class="visa-remaining-figure">{{ $visaDaysPart }} {{ __('Days') }}</div>
                    <div class="visa-remaining-caption">{{ $visaExpired ? __('Overdue') : __('Remaining') }}</div>
                @endif
            </div>

        </div>
    </div>

    <script>
    function toggleVisaBox() {
        const box = document.getElementById('visa-floating-box');
        const btn = box.querySelector('.toggle-btn');

        box.classList.toggle('closed');

        const isClosed = box.classList.contains('closed');
        btn.innerHTML = isClosed ? '&#9679;' : '&minus;';

        // Remember the open/closed state across page loads, so
        // dismissing it on the dashboard keeps it dismissed when
        // navigating to another page instead of popping back open
        // every time.
        try {
            localStorage.setItem('visaBoxClosed', isClosed ? '1' : '0');
        } catch (e) {
            // localStorage unavailable (private mode, etc.) - state just
            // won't persist, no need to break anything over it.
        }
    }

    // Move the widget to be a direct child of <body>. If any ancestor
    // in the page (a page-transition wrapper, a scroll container, etc.)
    // has a CSS transform on it, position:fixed on a descendant stops
    // being relative to the browser viewport and becomes relative to
    // that transformed ancestor instead - which is what made the box
    // render inline over the dashboard content instead of pinned to the
    // corner of the screen. Re-parenting straight onto <body> guarantees
    // it's fixed to the real viewport regardless of what's above it.
    (function () {
        var box = document.getElementById('visa-floating-box');
        if (!box) return;

        if (box.parentElement !== document.body) {
            document.body.appendChild(box);
        }

        // Restore whichever state (open or closed) the person left it in.
        try {
            if (localStorage.getItem('visaBoxClosed') === '1') {
                box.classList.add('closed');
                var btn = box.querySelector('.toggle-btn');
                if (btn) btn.innerHTML = '&#9679;';
            }
        } catch (e) {
            // ignore - just opens by default
        }
    })();
    </script>

@endif

    <div class="pd-dashboard container-fluid">

        <style>
            .pd-dashboard {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Inter, sans-serif;
            }

            .pd-page-head {
                display: flex;
                align-items: flex-end;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 22px;
            }

            .pd-page-head h2 {
                font-size: 22px;
                font-weight: 800;
                color: #0f172a;
                letter-spacing: -.01em;
                margin: 0;
            }

            .pd-page-head p {
                margin: 4px 0 0;
                font-size: 13px;
                color: #64748b;
            }

            /* ---- Stat cards ---- */
            .pd-row { margin-left: -10px; margin-right: -10px; }
            .pd-row > [class*="col-"] { padding-left: 10px; padding-right: 10px; margin-bottom: 20px; }

            .pd-stat-card {
                background: #fff;
                border: 1px solid #edf0f4;
                border-radius: 14px;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
                padding: 18px 20px;
                height: 100%;
                transition: box-shadow .2s ease, transform .2s ease;
            }

            .pd-stat-card:hover {
                box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
                transform: translateY(-2px);
            }

            .pd-stat-head {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 14px;
            }

            .pd-stat-icon {
                width: 40px;
                height: 40px;
                border-radius: 11px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 17px;
                flex-shrink: 0;
            }

            .pd-stat-icon.tone-green  { background: #e9f8ef; color: #1f9d55; }
            .pd-stat-icon.tone-blue   { background: #e8f1fd; color: #2563eb; }
            .pd-stat-icon.tone-orange { background: #fdf3e2; color: #c07a12; }

            .pd-stat-title {
                font-size: 11.5px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #64748b;
            }

            .pd-stat-rows { display: flex; flex-direction: column; }

            .pd-stat-line {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                padding: 8px 0;
                border-top: 1px solid #f1f5f9;
                font-size: 13px;
            }

            .pd-stat-line:first-child { border-top: none; }

            .pd-stat-line-label { color: #64748b; }

            .pd-stat-line-value {
                font-weight: 700;
                color: #0f172a;
                font-size: 15px;
            }

            /* ---- Data cards ---- */
            .pd-data-card {
                background: #fff;
                border: 1px solid #edf0f4;
                border-radius: 14px;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
                height: 100%;
                overflow: hidden;
            }

            .pd-data-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 16px 20px;
                border-bottom: 1px solid #f1f5f9;
            }

            .pd-data-head h3 {
                margin: 0;
                font-size: 14px;
                font-weight: 700;
                color: #0f172a;
            }

            .pd-data-body { padding: 6px 4px 10px; }

            .pd-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 12.5px;
            }

            .pd-table thead th {
                text-align: left;
                padding: 8px 16px;
                font-size: 10.5px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #94a3b8;
            }

            .pd-table tbody td {
                padding: 9px 16px;
                color: #1e293b;
                border-top: 1px solid #f8fafc;
            }

            .pd-table tbody tr:hover td { background: #f8fafc; }

            .pd-badge {
                display: inline-block;
                padding: 2px 9px;
                border-radius: 20px;
                font-size: 10.5px;
                font-weight: 700;
                letter-spacing: .02em;
            }

            .pd-badge.in  { background: #e9f8ef; color: #1f9d55; }
            .pd-badge.out { background: #e8f1fd; color: #2563eb; }

            .pd-empty {
                padding: 26px 16px;
                text-align: center;
                color: #94a3b8;
                font-size: 12.5px;
            }

            @media (max-width: 991px) {
                .pd-stat-card, .pd-data-card { margin-bottom: 0; }
            }
        </style>

        <div class="pd-page-head">
            <div>
                <h2>{{ __("Dashboard") }}</h2>
                <p>{{ __("Here's a snapshot of your attendance, schedule, and leave activity.") }}</p>
            </div>
        </div>

        <div class="row pd-row">

            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="pd-stat-card">
                    <div class="pd-stat-head">
                        <span class="pd-stat-icon tone-green"><i class="ui icon clock outline"></i></span>
                        <span class="pd-stat-title">{{ __("Attendance (Current Month)") }}</span>
                    </div>
                    <div class="pd-stat-rows">
                        <div class="pd-stat-line">
                            <span class="pd-stat-line-label">{{ __("Late Arrivals") }}</span>
                            <span class="pd-stat-line-value">{{ $la ?? 0 }}</span>
                        </div>
                        <div class="pd-stat-line">
                            <span class="pd-stat-line-label">{{ __("Early Departures") }}</span>
                            <span class="pd-stat-line-value">{{ $ed ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="pd-stat-card">
                    <div class="pd-stat-head">
                        <span class="pd-stat-icon tone-blue"><i class="ui icon user circle"></i></span>
                        <span class="pd-stat-title">{{ __("Present Schedule") }}</span>
                    </div>
                    <div class="pd-stat-rows">
                        <div class="pd-stat-line">
                            <span class="pd-stat-line-label">{{ __("Time") }}</span>
                            <span class="pd-stat-line-value">
                                @isset($cs)
                                    @php
                                        if ($cs->intime != null && $cs->outime != null) {
                                            if ($tf == 1) {
                                                echo e(date("h:i A", strtotime($cs->intime)));
                                                echo e(" - ");
                                                echo e(date("h:i A", strtotime($cs->outime)));
                                            } else {
                                                echo e(date("H:i", strtotime($cs->intime)));
                                                echo e(" - ");
                                                echo e(date("H:i", strtotime($cs->outime)));
                                            }
                                        }
                                    @endphp
                                @endisset
                            </span>
                        </div>
                        <div class="pd-stat-line">
                            <span class="pd-stat-line-label">{{ __("Rest Days") }}</span>
                            <span class="pd-stat-line-value">@isset($cs->restday) {{ $cs->restday }} @endisset</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="pd-stat-card">
                    <div class="pd-stat-head">
                        <span class="pd-stat-icon tone-orange"><i class="ui icon home"></i></span>
                        <span class="pd-stat-title">{{ __("Leaves of Absence") }}</span>
                    </div>
                    <div class="pd-stat-rows">
                        <div class="pd-stat-line">
                            <span class="pd-stat-line-label">{{ __("Approved") }}</span>
                            <span class="pd-stat-line-value">{{ $al ?? 0 }}</span>
                        </div>
                        <div class="pd-stat-line">
                            <span class="pd-stat-line-label">{{ __("Pending") }}</span>
                            <span class="pd-stat-line-value">{{ $pl ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row pd-row">

            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="pd-data-card">
                    <div class="pd-data-head">
                        <h3>{{ __("Recent Attendances") }}</h3>
                    </div>
                    <div class="pd-data-body">
                        @if(isset($a) && count($a))
                            <table class="pd-table">
                                <thead>
                                    <tr>
                                        <th>{{ __("Date") }}</th>
                                        <th>{{ __("Time") }}</th>
                                        <th>{{ __("Type") }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($a as $v)

                                    @if($v->timein != '' && $v->timeout == '')
                                    <tr>
                                        <td>{{ date('M d, Y', strtotime($v->date)) }}</td>
                                        <td>{{ $tf == 1 ? date("h:i:s A", strtotime($v->timein)) : date("H:i:s", strtotime($v->timein)) }}</td>
                                        <td><span class="pd-badge in">{{ __('Time-In') }}</span></td>
                                    </tr>
                                    @endif

                                    @if($v->timein != '' && $v->timeout != '')
                                    <tr>
                                        <td>{{ date('M d, Y', strtotime($v->date)) }}</td>
                                        <td>{{ $tf == 1 ? date("h:i:s A", strtotime($v->timeout)) : date("H:i:s", strtotime($v->timeout)) }}</td>
                                        <td><span class="pd-badge out">{{ __('Time-Out') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{ date('M d, Y', strtotime($v->date)) }}</td>
                                        <td>{{ $tf == 1 ? date("h:i:s A", strtotime($v->timein)) : date("H:i:s", strtotime($v->timein)) }}</td>
                                        <td><span class="pd-badge in">{{ __('Time-In') }}</span></td>
                                    </tr>
                                    @endif

                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="pd-empty">{{ __("No attendance records yet.") }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="pd-data-card">
                    <div class="pd-data-head">
                        <h3>{{ __("Previous Schedules") }}</h3>
                    </div>
                    <div class="pd-data-body">
                        @if(isset($ps) && count($ps))
                            <table class="pd-table">
                                <thead>
                                    <tr>
                                        <th>{{ __("Time") }}</th>
                                        <th>{{ __("From Date / Until") }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ps as $s)
                                    <tr>
                                        <td>
                                            @if($s->intime != null && $s->outime != null)
                                                {{ $tf == 1
                                                    ? date("h:i A", strtotime($s->intime)) . ' - ' . date("h:i A", strtotime($s->outime))
                                                    : date("H:i", strtotime($s->intime)) . ' - ' . date("H:i", strtotime($s->outime)) }}
                                            @endif
                                        </td>
                                        <td>{{ date('M d', strtotime($s->datefrom)) . ' - ' . date('M d, Y', strtotime($s->dateto)) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="pd-empty">{{ __("No previous schedules on file.") }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-md-6 col-lg-4">
                <div class="pd-data-card">
                    <div class="pd-data-head">
                        <h3>{{ __("Recent Leaves of Absence") }}</h3>
                    </div>
                    <div class="pd-data-body">
                        @if(isset($ald) && count($ald))
                            <table class="pd-table">
                                <thead>
                                    <tr>
                                        <th>{{ __("Description") }}</th>
                                        <th>{{ __("Date") }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ald as $l)
                                    <tr>
                                        <td>{{ $l->type }}</td>
                                        <td>
                                            @php
                                                $fd = date('M', strtotime($l->leavefrom));
                                                $td = date('M', strtotime($l->leaveto));

                                                $var = ($fd == $td)
                                                    ? date('M d', strtotime($l->leavefrom)) . ' - ' . date('d, Y', strtotime($l->leaveto))
                                                    : date('M d', strtotime($l->leavefrom)) . ' - ' . date('M d, Y', strtotime($l->leaveto));
                                            @endphp
                                            {{ $var }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="pd-empty">{{ __("No leave records yet.") }}</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

    @endsection