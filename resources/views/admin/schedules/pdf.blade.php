<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Weekly Schedule</title>
@php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';
    $appLogoPath = !empty($appSettings->app_logo)
        ? public_path('storage/'.$appSettings->app_logo)
        : public_path('assets/images/img/logo.png');
@endphp
<style>
    * { box-sizing: border-box; }
    body { font-family: Helvetica, Arial, sans-serif; color: #1f2937; font-size: 12px; margin: 0; padding: 34px; }

    table.layout { width: 100%; border-collapse: collapse; }

    /* ===== HEADER ===== */
    .header-table { width: 100%; border-collapse: collapse; margin-bottom: 22px; border-bottom: 2px solid #16a34a; padding-bottom: 0; }
    .header-table td { padding-bottom: 14px; vertical-align: middle; }
    .header-table .logo-cell { width: 56px; }
    .header-table .logo-cell img { width: 48px; height: 48px; object-fit: contain; }
    .header-table .title-cell { padding-left: 12px; }
    .app-name { font-size: 15px; font-weight: 700; color: #16a34a; }
    .doc-title { font-size: 21px; font-weight: 700; color: #111827; margin-top: 2px; }
    .header-table .status-cell { text-align: right; vertical-align: middle; }
    .status-pill { display: inline-block; padding: 5px 14px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
    .status-active { background: #dcfce7; color: #166534; }
    .status-archived { background: #fee2e2; color: #991b1b; }

    /* ===== EMPLOYEE BOX ===== */
    .employee-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px 16px; margin-bottom: 18px; }
    .employee-name { font-size: 15px; font-weight: 700; margin-bottom: 3px; color: #111827; }
    .employee-meta { color: #6b7280; font-size: 11px; }

    /* ===== INFO TABLE ===== */
    table.info { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table.info td { padding: 5px 0; font-size: 12px; }
    table.info td.label { color: #6b7280; width: 180px; }
    table.info td.value { font-weight: 700; color: #111827; }

    /* ===== ROTA TABLE ===== */
    table.rota { width: 100%; border-collapse: collapse; margin-top: 4px; }
    table.rota th { background: #16a34a; color: #ffffff; text-align: left; padding: 9px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: .03em; }
    table.rota td { padding: 9px 10px; border-bottom: 1px solid #e5e7eb; font-size: 12px; color: #1f2937; }
    table.rota tr.alt td { background: #f9fafb; }
    .off-badge { color: #dc2626; font-weight: 700; }
    .work-time { font-weight: 700; }

    .footer { margin-top: 34px; font-size: 10px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 10px; }
</style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if(file_exists($appLogoPath))
                    <img src="{{ $appLogoPath }}" alt="">
                @endif
            </td>
            <td class="title-cell">
                <div class="app-name">{{ $companyData->company  }}</div>
                <div class="doc-title">Weekly Schedule</div>
            </td>
            <td class="status-cell">
                @if($schedule->archive == '1')
                    <span class="status-pill status-archived">Archived</span>
                @else
                    <span class="status-pill status-active">Active</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="employee-box">
        <div class="employee-name">{{ mb_strtoupper($employee->lastname.', '.$employee->firstname) }}</div>
        <div class="employee-meta">
            {{ $companyData->jobposition ?? '' }}
            @if($companyData->company ?? false) &middot; {{ $companyData->company }} @endif
            @if($companyData->idno ?? false) &middot; ID: {{ $companyData->idno }} @endif
        </div>
    </div>

    <table class="info">
        <tr>
            <td class="label">Schedule Period</td>
            <td class="value">{{ date('M d, Y', strtotime($schedule->datefrom)) }} &ndash; {{ date('M d, Y', strtotime($schedule->dateto)) }}</td>
        </tr>
        <tr>
            <td class="label">Weekly Hours Allowed</td>
            <td class="value">{{ $schedule->hours }} hrs</td>
        </tr>
    </table>

    <table class="rota">
        <thead>
            <tr>
                <th style="width:22%;">Day</th>
                <th style="width:26%;">Status</th>
                <th style="width:26%;">Time In</th>
                <th style="width:26%;">Time Out</th>
            </tr>
        </thead>
        <tbody>
            @foreach($days as $index => $day)
                @php
                    $shift = $shifts[$day] ?? null;
                    $dayOff = $shift ? (bool) $shift->is_off : false;
                    $timeIn = $shift && $shift->time_in ? substr($shift->time_in, 0, 5) : '';
                    $timeOut = $shift && $shift->time_out ? substr($shift->time_out, 0, 5) : '';
                @endphp
                <tr class="{{ $index % 2 === 1 ? 'alt' : '' }}">
                    <td>{{ $day }}</td>
                    <td>
                        @if($dayOff)
                            <span class="off-badge">Day Off</span>
                        @else
                            Working Day
                        @endif
                    </td>
                    <td class="work-time">{{ $timeIn ?: '—' }}</td>
                    <td class="work-time">{{ $timeOut ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ date('M d, Y h:i A') }} &middot; {{ $companyData->company  }}
    </div>

</body>
</html>