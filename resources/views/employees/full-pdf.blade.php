<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Employee Complete Record</title>
<style>
    body {
        margin:0; padding:20px;
        font-family:'Montserrat', sans-serif;
        font-size:13px;
        line-height:1.6;
        color:#333;
        background:#f5f6fa;
    }

    .container { width:95%; margin:auto; position:relative; z-index:2; }

    .watermark {
        position:fixed;
        top:30%;
        left:15%;
        width:70%;
        text-align:center;
        opacity:0.07;
        z-index:0;
        transform:rotate(-30deg);
    }

    .watermark img { width:500px; }

    /* HEADER */
    .header {
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:30px;
        background:#fff;
        padding:20px;
        border-radius:10px;
        box-shadow:0 4px 10px rgba(0,0,0,0.08);
    }

    .header-left {
        display:flex;
        align-items:center;
        gap:12px;
        width:180px;
    }

    .header-left img.avatar {
        width:75px;
        height:75px;
        border-radius:50%;
        object-fit:cover;
        border:3px solid #D11F25;
    }

    .header-left img.logo { max-width:90px; }

    .header-center { text-align:center; flex:1; }

    .header-center .report-title {
        font-size:28px;
        font-weight:bold;
        text-transform:uppercase;
        color:#2c3e50;
    }

    .header-center .business-name {
        font-size:20px;
        font-weight:bold;
        color:#D11F25;
    }

    .header-right {
        text-align:right;
        font-size:13px;
        color:#555;
        width:120px;
    }

    h1 {
        text-align:center;
        font-size:24px;
        color:#2c3e50;
        margin-bottom:5px;
    }

    h2 { text-align:center; font-size:18px; color:#555; margin-bottom:20px; }

    h3 { font-size:16px; font-weight:bold; margin:20px 0 10px; color:#2c3e50; }

    /* CARDS */
    .card {
        background:#fff;
        border-radius:8px;
        box-shadow:0 4px 8px rgba(0,0,0,0.1);
        margin-bottom:25px;
        padding:15px;
    }

    .card-header {
        font-size:15px;
        font-weight:bold;
        background:#2f3640;
        color:#fff;
        padding:8px 12px;
        border-radius:6px 6px 0 0;
        margin:-15px -15px 15px -15px;
    }

    .card-body table {
        width:100%;
        border-collapse:collapse;
    }

    .card-body th, .card-body td {
        padding:8px;
        border-bottom:1px solid #eee;
        text-align:left;
    }

    .card-body th {
        width:35%;
        color:#2c3e50;
        text-transform:uppercase;
        font-weight:600;
        font-size:12px;
    }

    .monthly-total-row td {
        font-weight:700;
        background:#f9fafb;
        border-top:2px solid #e5e7eb;
    }

    .no-data {
        text-align:center;
        font-style:italic;
        padding:8px;
        color:#999;
    }

    a { color:#D11F25; text-decoration:none; font-weight:bold; }
    a:hover { text-decoration:underline; }

    .badge {
        display:inline-block;
        padding:3px 10px;
        border-radius:4px;
        font-size:11px;
        font-weight:700;
        color:#fff;
        text-transform:uppercase;
    }

    .badge-approved { background:#16a34a; }
    .badge-pending  { background:#d97706; }
    .badge-other    { background:#dc2626; }

    /* RESPONSIVE / PRINT */
    @media (max-width:768px){
        body { font-size:12px; }
        .header { flex-direction:column; text-align:center; gap:10px; }
        .header-left { width:auto; justify-content:center; }
        .header-right { text-align:center; width:auto; }
        .card-body table,
        .card-body thead,
        .card-body tbody,
        .card-body th,
        .card-body td,
        .card-body tr { display:block; }
        .card-body th { width:100%; }
    }

    @media print {
        body { background:#fff; }
        .card { box-shadow:none; border:1px solid #eee; }
    }
</style>
</head>
<body>

<div class="watermark">
    <img src="https://www.jpingos.com/wp-content/uploads/2025/03/image-2-Photoroom.png">
</div>

<div class="container">

    <!-- HEADER -->
    <div class="header">

        <div class="header-left">

            @if($employee->avatar != null)
                <img class="avatar"
                     src="{{ asset('/assets/faces/'.$employee->avatar) }}"
                     alt="profile photo"/>
            @else
                <img class="avatar"
                     src="{{ asset('/assets/images/faces/default_user.jpg') }}"
                     alt="profile photo"/>
            @endif

            <img class="logo"
                 src="https://www.jpingos.com/wp-content/uploads/2025/03/image-2-Photoroom.png">

        </div>

        <div class="header-center">
            <div class="report-title">Employee Profile</div>
            <div class="business-name">JPINGOS FLAME AND GRILLE</div>
        </div>

        <div class="header-right">
            {{ \Carbon\Carbon::now()->format('d M Y') }}
        </div>

    </div>

    <!-- NAME -->
    <h1>
        {{ trim(($employee->firstname ?? '') . ' ' . ($employee->mi ?? '') . ' ' . ($employee->lastname ?? '')) ?: 'N/A' }}
    </h1>

    <!-- PERSONAL INFO CARD -->
    <div class="card">
        <div class="card-header">Personal Information</div>
        <div class="card-body">
            <table>
                @if($employee)
                    <tr><th>Full Name</th><td>{{ $employee->firstname ?? 'N/A' }} {{ $employee->mi ?? 'N/A' }} {{ $employee->lastname ?? 'N/A' }}</td></tr>
                    <tr><th>Gender</th><td>{{ $employee->gender ?? 'N/A' }}</td></tr>
                    <tr><th>Email</th><td>{{ $employee->emailaddress ?? 'N/A' }}</td></tr>
                    <tr><th>Civil Status</th><td>{{ $employee->civilstatus ?? 'N/A' }}</td></tr>
                    <tr><th>Mobile</th><td>{{ $employee->mobileno ?? 'N/A' }}</td></tr>
                    <tr><th>DOB</th><td>{{ $employee->birthday ?? 'N/A' }}</td></tr>
                    <tr><th>Passport No</th><td>{{ $employee->nationalid ?? 'N/A' }}</td></tr>
                    <tr><th>NI Number</th><td>{{ $employee->NI ?? 'N/A' }}</td></tr>
                    <tr><th>Share Code</th><td>{{ $employee->sharecode ?? 'N/A' }}</td></tr>
                    <tr><th>Nationality</th><td>{{ $employee->birthplace ?? 'N/A' }}</td></tr>
                    <tr><th>Address</th><td>{{ $employee->homeaddress ?? 'N/A' }}</td></tr>
                    <tr><th>Next of Kin Name</th><td>{{ $company->kinname ?? 'N/A' }}</td></tr>
                    <tr><th>Next of Kin Number</th><td>{{ $company->kinno ?? 'N/A' }}</td></tr>
                @else
                    <tr><td colspan="2" class="no-data">No personal data found</td></tr>
                @endif
            </table>
        </div>
    </div>

    <!-- COMPANY INFO CARD -->
    <div class="card">
        <div class="card-header">Company Information</div>
        <div class="card-body">

            @if($company)
            <table>
                <tr><th>Company</th><td>{{ $company->company ?? 'N/A' }}</td></tr>
                <tr><th>Department</th><td>{{ $company->department ?? 'N/A' }}</td></tr>
                <tr><th>Job Position</th><td>{{ $company->jobposition ?? 'N/A' }}</td></tr>
                <tr><th>Job SOC</th><td>{{ $company->jobtype ?? 'N/A' }}</td></tr>
                <tr><th>Employment Type</th><td>{{ $employee->employmenttype ?? 'N/A' }}</td></tr>
                <tr><th>Job Start Date</th><td>{{ $company->startdate ?? 'N/A' }}</td></tr>
                <tr><th>Visa Start</th><td>{{ $company->visastart ?? 'N/A' }}</td></tr>
                <tr><th>Visa End</th><td>{{ $company->visaend ?? 'N/A' }}</td></tr>
                <tr><th>COS Certificate No</th><td>{{ strtoupper($company->COSCertificateNo ?? 'N/A') }}</td></tr>
                <tr><th>COS Certificate Expiry Date (Use By)</th><td>{{ strtoupper($company->cosexpiry ?? 'N/A') }}</td></tr>
                <tr><th>Right To Work Checks</th><td>{!! strtoupper($company->workchecks ?? 'N/A') !!}</td></tr>
                <tr><th>Immigration Status</th><td>{!! strtoupper($company->visastatus ?? 'N/A') !!}</td></tr>

                {{-- JOB DUTIES --}}
                <tr>
                    <th>Job Duties</th>
                    <td>
                        @if(!empty($company->jobduties))
                            @php
                                $duties = is_string($company->jobduties)
                                    ? json_decode($company->jobduties, true)
                                    : $company->jobduties;
                            @endphp

                            @if(is_array($duties))
                                <ul style="margin:0; padding-left:18px;">
                                    @foreach($duties as $duty)
                                        <li>{{ $duty }}</li>
                                    @endforeach
                                </ul>
                            @else
                                {!! $company->jobduties !!}
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            </table>
            @else
                <p class="no-data">No company data found</p>
            @endif

        </div>
    </div>

    <!-- WORK SCHEDULE CARD -->
    <div class="card">
        <div class="card-header">Work Schedule</div>
        <div class="card-body">

            @if($schedule)
            <table>
                <tr><th>Employee</th><td>{{ $schedule->employee ?? 'N/A' }}</td></tr>
                <tr><th>Rota From Date</th><td>{{ $schedule->datefrom ?? 'N/A' }}</td></tr>
                <tr><th>Rota To Date</th><td>{{ $schedule->dateto ?? 'N/A' }}</td></tr>
                <tr><th>Total Weekly Hours</th><td>{{ $schedule->hours ?? 'N/A' }}</td></tr>
                <tr><th>Off Day</th><td>{{ $schedule->restday ?? 'N/A' }}</td></tr>
            </table>
            @else
                <p class="no-data">No schedule data found</p>
            @endif

        </div>
    </div>

    <!-- MONTHLY ATTENDANCE CARD -->
    <div class="card">
        <div class="card-header">Monthly Attendance Report</div>
        <div class="card-body">

            @if($attendance && $attendance->count() > 0)

                @foreach($attendance as $month => $monthData)

                    <h3>Month: {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}</h3>

                    <table>
                        <tr>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Hours</th>
                            <th>Status In</th>
                            <th>Status Out</th>
                        </tr>

                        @php
                            $monthlyTotal = 0;
                            $seenDates = [];
                        @endphp

                        @foreach($monthData as $a)

                            @if(in_array($a->date, $seenDates))
                                @continue
                            @endif

                            @php
                                $seenDates[] = $a->date;
                                $hours = 0;

                                if (!empty($a->timein) && !empty($a->timeout)) {
                                    $start = strtotime($a->timein);
                                    $end = strtotime($a->timeout);

                                    if ($end < $start) {
                                        $end += 86400;
                                    }

                                    $hours = ($end - $start) / 3600;
                                }

                                $monthlyTotal += $hours;
                            @endphp

                            <tr>
                                <td>{{ $a->date ?? 'N/A' }}</td>
                                <td>{{ $a->timein ?? 'N/A' }}</td>
                                <td>{{ $a->timeout ?? 'N/A' }}</td>
                                <td>{{ number_format($hours, 2) }}</td>
                                <td>{{ $a->status_timein ?? 'N/A' }}</td>
                                <td>{{ $a->status_timeout ?? 'N/A' }}</td>
                            </tr>

                        @endforeach

                        <tr class="monthly-total-row">
                            <td colspan="3" style="text-align:right;">Total Hours This Month</td>
                            <td colspan="3">{{ number_format($monthlyTotal, 2) }}</td>
                        </tr>
                    </table>

                @endforeach

            @else
                <p class="no-data">No attendance data found</p>
            @endif

        </div>
    </div>

    <!-- LEAVE RECORDS CARD -->
    <div class="card">
        <div class="card-header">Leave Records</div>
        <div class="card-body">

            @if($leaves->isNotEmpty())

                <div style="overflow-x:auto;">
                    <table>
                        <tr>
                            <th style="width:auto;">Employee</th>
                            <th style="width:auto;">Type</th>
                            <th style="width:auto;">From</th>
                            <th style="width:auto;">To</th>
                            <th style="width:auto;">Return Date</th>
                            <th style="width:auto;">Reason</th>
                            <th style="width:auto;">Status</th>
                            <th style="width:auto;">Comment</th>
                        </tr>

                        @foreach($leaves as $l)
                            <tr>
                                <td>{{ $l->employee ?? 'N/A' }}</td>
                                <td>{{ $l->type ?? 'N/A' }}</td>
                                <td>{{ $l->leavefrom ? date('d M Y', strtotime($l->leavefrom)) : 'N/A' }}</td>
                                <td>{{ $l->leaveto ? date('d M Y', strtotime($l->leaveto)) : 'N/A' }}</td>
                                <td>{{ $l->returndate ? date('d M Y', strtotime($l->returndate)) : 'N/A' }}</td>
                                <td>{{ $l->reason ?? 'N/A' }}</td>
                                <td>
                                    @if($l->status == 'Approved')
                                        <span class="badge badge-approved">{{ $l->status }}</span>
                                    @elseif($l->status == 'Pending')
                                        <span class="badge badge-pending">{{ $l->status }}</span>
                                    @else
                                        <span class="badge badge-other">{{ $l->status ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>{{ $l->comment ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

            @else
                <p class="no-data" style="font-style:normal;">
                    No leave records available. Employee has not taken annual or sick leave during this period.
                </p>
            @endif

        </div>
    </div>

</div>
</body>
</html>