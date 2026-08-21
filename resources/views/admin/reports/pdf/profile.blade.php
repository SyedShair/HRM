<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Profile</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }
        h1 {
            font-size: 20px;
            margin-bottom: 2px;
        }
        h2.section {
            font-size: 14px;
            background: #f3f4f6;
            padding: 6px 8px;
            margin-top: 20px;
            margin-bottom: 8px;
            border-left: 4px solid #16a34a;
        }
        .header-row {
            width: 100%;
            margin-bottom: 10px;
        }
        .avatar {
            width: 80px;
            height: 80px;
            border: 1px solid #ddd;
        }
        table.info {
            width: 100%;
            border-collapse: collapse;
        }
        table.info td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        table.info td.label {
            width: 35%;
            color: #555;
            font-weight: bold;
        }
        .uppercase {
            text-transform: uppercase;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            color: #fff;
            font-size: 10px;
        }
        .bg-success { background: #16a34a; }
        .bg-warning { background: #d97706; }
        .bg-danger  { background: #dc2626; }

        /* ============ ADDRESS HISTORY ============ */
        table.address-history {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.address-history th {
            background: #f3f4f6;
            text-align: left;
            padding: 5px 8px;
            font-size: 11px;
            border-bottom: 1px solid #ddd;
        }
        table.address-history td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
            vertical-align: top;
        }
        .current-tag {
            display: inline-block;
            padding: 1px 5px;
            background: #16a34a;
            color: #fff;
            border-radius: 3px;
            font-size: 9px;
            margin-left: 4px;
        }
        .no-data {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>

    <table class="header-row">
        <tr>
            <td style="width: 90px;">
         @php
    $imagePath = $i
        ? Storage::disk('public')->path($i)
        : public_path('assets/images/faces/default_user.jpg');

    $base64 = base64_encode(file_get_contents($imagePath));
    $mimeType = mime_content_type($imagePath);
@endphp

<img
    class="avatar border-white img-fluid"
    src="data:{{ $mimeType }};base64,{{ $base64 }}"
    alt="profile photo"
/>
            </td>
            <td>
                <h1>{{ $p->firstname ?? '' }} {{ $p->mi ?? '' }} {{ $p->lastname ?? '' }}</h1>
                <div>{{ $c->jobposition ?? '' }} &mdash; {{ $c->department ?? '' }}</div>
                <div>{{ $c->company ?? '' }}</div>
            </td>
        </tr>
    </table>

    <h2 class="section">Contact</h2>
    <table class="info">
        <tr>
            <td class="label">Email</td>
            <td>{{ $p->emailaddress ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Mobile No.</td>
            <td>{{ $p->mobileno ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">ID No.</td>
            <td>{{ $c->idno ?? '' }}</td>
        </tr>
    </table>

    <h2 class="section">Personal Information</h2>
    <table class="info">
        <tr>
            <td class="label">Civil Status</td>
            <td>{{ $p->civilstatus ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Gender</td>
            <td class="uppercase">{{ $p->gender ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Date of Birth</td>
            <td>{{ !empty($p->birthday) ? date('F d, Y', strtotime($p->birthday)) : '' }}</td>
        </tr>
        <tr>
            <td class="label">Place of Birth</td>
            <td>{{ $p->birthplace ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Passport No</td>
            <td>
                {{ $p->nationalid ?? '' }}
                @if(!empty($passportExpiry['text']))
                    <span class="badge {{ $passportExpiry['class'] }}">{{ $passportExpiry['text'] }}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">National Insurance</td>
            <td class="uppercase">{{ $p->NI ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Share Code</td>
            <td class="uppercase">{{ $p->sharecode ?? '' }}</td>
        </tr>
    </table>

    <h2 class="section">Address History (Last 5 Years)</h2>
    @if(isset($addressHistory) && $addressHistory->count() > 0)
        <table class="address-history">
            <thead>
                <tr>
                    <th style="width: 45%;">Address</th>
                    <th style="width: 20%;">From</th>
                    <th style="width: 20%;">To</th>
                    <th style="width: 15%;">Doc Reference</th>
                </tr>
            </thead>
            <tbody>
                @foreach($addressHistory as $addr)
                <tr>
                    <td>
                        {{ $addr->address_line }}
                        @if($addr->is_current)
                            <span class="current-tag">CURRENT</span>
                        @endif
                    </td>
                    <td>{{ !empty($addr->date_from) ? \Carbon\Carbon::parse($addr->date_from)->format('d M Y') : '' }}</td>
                    <td>{{ !empty($addr->date_to) ? \Carbon\Carbon::parse($addr->date_to)->format('d M Y') : 'Present' }}</td>
                    <td>{{ $addr->doc_reference ?? '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">No address history on file.</p>
    @endif

    <h2 class="section">Designation</h2>
    <table class="info">
        <tr>
            <td class="label">Company</td>
            <td>{{ $c->company ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Department</td>
            <td>{{ $c->department ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Position</td>
            <td>{{ $c->jobposition ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Job Type</td>
            <td class="uppercase">{{ $c->jobtype ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Job Duties</td>
                                        <td class="uppercase">@isset($c->jobduties) {!! $c->jobduties !!} @endisset</td>
        </tr>
        <tr>
            <td class="label">Leave Privilege</td>
            <td>
                @isset($leavetype)
                    @isset($leavegroup)
                        @isset($c->leaveprivilege)
                            @foreach($leavegroup as $lg)
                                @if($lg->id == $c->leaveprivilege)
                                    @php $lp = explode(",", $lg->leaveprivileges); @endphp
                                    @foreach($lp as $rights)
                                        @foreach($leavetype as $lt)
                                            @if($lt->id == $rights) {{ $lt->leavetype }}, @endif
                                        @endforeach
                                    @endforeach
                                @endif
                            @endforeach
                        @endisset
                    @endisset
                @endisset
            </td>
        </tr>
        <tr>
            <td class="label">Employment Type</td>
            <td>{{ $p->employmenttype ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Employment Status</td>
            <td>{{ $p->employmentstatus ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Official Start Date</td>
            <td>{{ !empty($c->startdate) ? date('F d, Y', strtotime($c->startdate)) : '' }}</td>
        </tr>
        <tr>
            <td class="label">Date Regularized</td>
            <td>{{ !empty($c->dateregularized) ? date('F d, Y', strtotime($c->dateregularized)) : '' }}</td>
        </tr>
    </table>

    <h2 class="section">Visa &amp; Compliance</h2>
    <table class="info">
        <tr>
            <td class="label">COS Certificate No</td>
            <td class="uppercase">{{ $c->COSCertificateNo ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Visa Issue Date</td>
            <td>{{ !empty($c->visastart) ? \Carbon\Carbon::parse($c->visastart)->format('d F Y') : '' }}</td>
        </tr>
        <tr>
            <td class="label">Visa Expiry Date</td>
            <td>
                @if(!empty($c->visaend))
                    {{ \Carbon\Carbon::parse($c->visaend)->format('d F Y') }}
                    @if(!empty($visaExpiry['text']))
                        <span class="badge {{ $visaExpiry['class'] }}">{{ $visaExpiry['text'] }}</span>
                    @endif
                @endif
            </td>
        </tr>
    </table>

</body>
</html>