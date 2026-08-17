@extends('layouts.default')

    @section('meta')
        <title>Profile |Jpingos</title>
        <meta name="description" content="Workday view employee profile, edit employee profile, update employee profile">
        <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

        @endsection

    @section('content')

    @php
        
       // --- Visa expiry calculation ---
$visaClass = null;
$visaText = null;
if (!empty($c->visaend)) {
    $today = \Carbon\Carbon::now()->startOfDay();
    $visaEnd = \Carbon\Carbon::parse($c->visaend)->startOfDay();

    if ($visaEnd->isPast()) {
        $visaClass = 'bg-danger';
        $visaText = __('Expired');
    } else {
        $visaMonths = (int) $today->diffInMonths($visaEnd);
        $visaDays = $today->copy()->addMonths($visaMonths)->diffInDays($visaEnd);
        $visaClass = $visaMonths <= 3 ? 'bg-warning' : 'bg-success';
        $visaText = $visaMonths . ' ' . __('months') . ' ' . $visaDays . ' ' . __('days remaining');
    }
}

       // --- Passport / national ID expiry calculation ---
$passportClass = null;
$passportText = null;
if (!empty($p->idexpirydate)) {
    $today = \Carbon\Carbon::now()->startOfDay();
    $expiry = \Carbon\Carbon::parse($p->idexpirydate)->startOfDay();

    if ($expiry->isPast()) {
        $passportClass = 'bg-danger';
        $passportText = __('Expired');
    } else {
        $passportMonths = (int) $today->diffInMonths($expiry);
        $passportDays = $today->copy()->addMonths($passportMonths)->diffInDays($expiry);
        $passportClass = $passportMonths <= 3 ? 'bg-warning' : 'bg-success';
        $passportText = $passportMonths . ' ' . __('months') . ' ' . $passportDays . ' ' . __('days left');
    }
}
        
    @endphp
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h2 class="page-title d-flex flex-wrap align-items-center justify-content-between">
                    <span>{{ __('Employee Profile') }}</span>
                    <a href="{{ url('employees') }}" class="ui basic blue button mini"><i class="ui icon chevron left"></i>{{ __('Return') }}</a>
                </h2>
            </div>    
        </div>

        <div class="row">
            <div class="col-12 col-md-4 mb-4">
                <div class="box box-success">
                    <div class="box-body employee-info">
                        <div class="author">
                        @if($i != null)
                            <img class="avatar border-white img-fluid" src="{{ asset('/assets/faces/'.$i) }}" alt="profile photo"/>
                        @else
                            <img class="avatar border-white img-fluid" src="{{ asset('/assets/images/faces/default_user.jpg') }}" alt="profile photo"/>
                        @endif
                        </div>
                        <p class="description text-center">
                            <h4 class="title">@isset($p->firstname) {{ $p->firstname }} @endisset @isset($p->lastname) {{ $p->lastname }} @endisset</h4>
                            <div class="table-responsive">
                            <table style="width: 100%" class="profile-tbl">
                                <tbody>
                                    <tr>
                                        <td>{{ __('Email') }}</td>
                                        <td><span class="p_value">@isset($p->emailaddress) 
                                        
                                        <a href="mailto:{{ $p->emailaddress }}">{{ $p->emailaddress }}</a>
                                        
                                        @endisset</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Mobile no.') }}</td>
                                        <td><span class="p_value">@isset($p->mobileno) 
                                      
                                        <a href="tel:{{ $p->mobileno }}">  {{ $p->mobileno }}</a>
                                        
                                        @endisset</span></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('ID no.') }}</td>
                                        <td><span class="p_value">@isset($c->idno) {{ $c->idno }} @endisset</span></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
    <a href="{{ route('profile.print', $p->id) }}" target="_blank" class="ui basic green button mini offsettop5 float-right"><i class="ui icon file pdf outline"></i>{{ __('Print Full Profile') }}</a>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('QRCode') }}</td>
                                        <td><div id="qrcode" class="mt-5"></div></td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-8 mb-4">
                <div class="box box-success">
                    <div class="box-header with-border">{{ __('Personal Information') }}</div>
                    <div class="box-body employee-info">
                            <div class="table-responsive">
                            <table class="tablelist">
                                <tbody>
                                    <tr>
                                        <td><p>{{ __('Civil Status') }}</p></td>
                                        <td><p>@isset($p->civilstatus) {{ $p->civilstatus }} @endisset</p></td>
                                    </tr>
                                    
                                    
                                    <tr>
                                        <td><p>{{ __('COS Certificate No') }}</p></td>
                                        <td><p  class="uppercase">@isset($c->COSCertificateNo) {{ $c->COSCertificateNo }} @endisset</p></td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Visa Issue Date') }}</p></td>
                                        <td><p  class="uppercase">@isset($c->visastart) {{ \Carbon\Carbon::parse($c->visastart)->format('d F Y') }} @endisset</p></td>
                                    </tr>
                                   
                                    <tr>
                                        <td><p>{{ __('Visa Expiry Date') }}</p></td>
                                        <td>
                                            <p class="uppercase">
                                                @isset($c->visaend)
                                                    {{ \Carbon\Carbon::parse($c->visaend)->format('d F Y') }}
                                                    <br>
                                                    <span class="badge {{ $visaClass }}" style="background-color: {{ $visaClass === 'bg-danger' ? '#dc3545' : ($visaClass === 'bg-warning' ? '#ffc107' : '#28a745') }}; color: white;">
                                                        {{ $visaText }}
                                                    </span>
                                                @endisset
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('National Insurance') }}</p></td>
                                        <td><p  class="uppercase">@isset($p->NI) {{ $p->NI }} @endisset</p></td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Share Code') }}</p></td>
                                        <td><p  class="uppercase">@isset($p->sharecode) {{ $p->sharecode }} @endisset</p></td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Gender') }}</p></td>
                                        <td><p class="uppercase">@isset($p->gender) {{ $p->gender }} @endisset</p></td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Date of Birth') }}</p></td>
                                        <td>
                                            <p>
                                                @isset($p->birthday) 
                                                    @php echo e(date("F d, Y", strtotime($p->birthday))) @endphp
                                                @endisset
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Place of Birth') }}</p></td>
                                        <td><p>@isset($p->birthplace) {{ $p->birthplace }} @endisset</p></td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Home Address') }}</p></td>
                                        <td><p>@isset($p->homeaddress) {{ $p->homeaddress }} @endisset</p></td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Passport No') }}</p></td>
                                        <td>
                                            <p>@isset($p->nationalid) {{ $p->nationalid }} @endisset</p>
                                            @if($passportText)
                                                <span class="badge {{ $passportClass }}" style="background-color: {{ $passportClass === 'bg-danger' ? '#dc3545' : ($passportClass === 'bg-warning' ? '#ffc107' : '#28a745') }}; color: white;">
                                                    {{ $passportText }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <h4 class="ui dividing header">{{ __('Designation') }}</h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Company') }}</td>
                                        <td>@isset($c->company) {{ $c->company }} @endisset</td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Department') }}</p></td>
                                        <td><p>@isset($c->department) {{ $c->department }} @endisset</p></td>
                                    </tr>
                                    <tr>
                                        <td>{{ __('Position') }}</td>
                                        <td>@isset($c->jobposition) {{ $c->jobposition }} @endisset</td>

                                    </tr>
                                    
                                     <tr>
                                        <td>{{ __('Job Type') }}</td>
                                        <td class="uppercase">@isset($c->jobtype) {{ $c->jobtype }} @endisset</td>

                                    </tr>
                                       <tr>
                                        <td>{{ __('Job Duties') }}</td>
                                        <td class="uppercase">@isset($c->jobduties) {!! $c->jobduties !!} @endisset</td>

                                    </tr>
                                    <tr>
                                        <td>{{ __('Leave Privilege') }}</td>
                                        <td>
                                            @isset($leavetype)
                                                @isset($leavegroup) 
                                                    @isset($c->leaveprivilege)
                                                        @foreach($leavegroup as $lg)
                                                            @if($lg->id == $c->leaveprivilege)
                                                                @php
                                                                    $lp = explode(",", $lg->leaveprivileges);
                                                                @endphp
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
                                        <td><p>{{ __('Employment Type') }}</p></td>
                                        <td><p>@isset($p->employmenttype) {{ $p->employmenttype }} @endisset</p></td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Employement Status') }}</p></td>
                                        <td><p>@isset($p->employmentstatus) {{ $p->employmentstatus }} @endisset</p></td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Official Start Date') }}</p></td>
                                        <td>
                                            <p>
                                            @isset($c->startdate) 
                                                @php echo e(date("F d, Y", strtotime($c->startdate))) @endphp
                                            @endisset
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><p>{{ __('Date Regularized') }}</p></td>
                                        <td>
                                            <p>
                                            @isset($c->dateregularized) 
                                                @php echo e(date("F d, Y", strtotime($c->dateregularized))) @endphp
                                            @endisset
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endsection
    @section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {


        const qrText = "{{ $c->idno }}"; // Blade value becomes a JS string
            new QRCode(document.getElementById("qrcode"), {
                text: qrText,
                width: 70,
                height: 70,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });    
</script>
        @endsection