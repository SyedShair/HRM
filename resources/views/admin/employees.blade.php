@extends('layouts.default')

@section('meta')
    <title>Employees | Jpingos</title>
    <meta name="description" content="Workday employees management system">
@endsection

@section('content')

@php
use Carbon\Carbon;

$total = count($data ?? []);
$active = collect($data ?? [])->where('employmentstatus', 'Active')->count();
$expired = collect($data ?? [])->filter(fn($e) => $e->visaend && Carbon::parse($e->visaend)->isPast())->count();
$expiring = collect($data ?? [])->filter(function ($e) {
    if (!$e->visaend) return false;
    $days = Carbon::parse($e->visaend)->diffInDays(now(), false);
    return $days > 0 && $days <= 90;
})->count();
@endphp

<style>
/* Clean, professional table styling — no motion/animation effects */

.table thead th {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6b7280;
    background-color: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.table tbody tr {
    border-bottom: 1px solid #f1f1f1;
}

.table tbody tr:hover {
    background-color: #fafafa;
}

.visa-expiry-date {
    font-weight: 500;
    color: #1f2937;
}

.expiring-row,
.table-striped tbody tr.expiring-row,
.table tbody tr.expiring-row {
    animation: expiryBlink 1.2s ease-in-out infinite !important;
    border-left: 4px solid #d93025 !important;
}

@keyframes expiryBlink {
    0%, 100% { background-color: #ffffff !important; }
    50% { background-color: #ffb3b3 !important; }
}

.visa-expiry-sub {
    font-size: 11px;
    color: #6b7280;
}

.passport-expiry-warning {
    color: #d93025;
    font-weight: 600;
}
</style>

<div class="container-fluid">

    <!-- ================= HEADER ================= -->
    <div class="row">
        <h2 class="page-title uppercase">
            Employees
            <a class="ui positive button mini float-right" href="{{ url('employees/new') }}">
                <i class="ui icon plus"></i> Add
            </a>
        </h2>
    </div>

    <!-- ================= SUMMARY ================= -->
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-3">
            <div class="box box-solid" style="padding:12px 16px; border:1px solid #e5e7eb; border-radius:6px;">
                <div style="font-size:11px; color:#6b7280; text-transform:uppercase;">Total Employees</div>
                <div style="font-size:22px; font-weight:600;">{{ $total }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-solid" style="padding:12px 16px; border:1px solid #e5e7eb; border-radius:6px;">
                <div style="font-size:11px; color:#6b7280; text-transform:uppercase;">Active</div>
                <div style="font-size:22px; font-weight:600; color:#16794f;">{{ $active }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-solid" style="padding:12px 16px; border:1px solid #e5e7eb; border-radius:6px;">
                <div style="font-size:11px; color:#6b7280; text-transform:uppercase;">Visas Expiring (90 days)</div>
                <div style="font-size:22px; font-weight:600; color:#b7791f;">{{ $expiring }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-solid" style="padding:12px 16px; border:1px solid #e5e7eb; border-radius:6px;">
                <div style="font-size:11px; color:#6b7280; text-transform:uppercase;">Visas Expired</div>
                <div style="font-size:22px; font-weight:600; color:#d93025;">{{ $expired }}</div>
            </div>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="row">
        <div class="box box-success">
            <div class="box-body">

                <table class="table table-striped table-hover" id="dataTables" width="100%">

                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Company</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Passport</th>
                        <th>Visa Expiry</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($data ?? [] as $employee)

                        @php
                            $now = Carbon::now();
                            $end = $employee->visaend ? Carbon::parse($employee->visaend) : null;

                            $months = null;
                            $days = null;
                            $diffDays = null;
                            $expired = false;

                            if ($end) {
                                $diff = $now->diff($end, false);
                                $diffDays = $now->diffInDays($end, false);

                                if ($diff->invert) {
                                    $expired = true;
                                } else {
                                    $months = ($diff->y * 12) + $diff->m;
                                    $days = $diff->d;
                                }
                            }

                            $passportExpiry = $employee->idexpirydate ? Carbon::parse($employee->idexpirydate) : null;
                            $passportMonths = null;
                            $passportDays = null;
                            $passportExpired = false;

                            if ($passportExpiry) {
                                $passportDiff = $now->diff($passportExpiry, false);
                                if ($passportDiff->invert) {
                                    $passportExpired = true;
                                } else {
                                    $passportMonths = ($passportDiff->y * 12) + $passportDiff->m;
                                    $passportDays = $passportDiff->d;
                                }
                            }
                        @endphp

                        <tr class="{{ ($diffDays !== null && $diffDays <= 90 && $diffDays > 0) ? 'expiring-row' : '' }}">

                            <td>{{ $employee->idno }}</td>
                            <td>{{ $employee->lastname }}, {{ $employee->firstname }}</td>
                            <td>{{ $employee->company }}</td>
                            <td>{{ $employee->department }}</td>
                            <td>{{ $employee->jobposition }}</td>

                            <!-- PASSPORT (nationalid) + expiry countdown -->
                            <td>
                                <div class="visa-expiry-date">{{ $employee->nationalid ?? '—' }}</div>

                                @if($passportExpiry)
                                    @if($passportExpired)
                                        <span class="ui red label">Expired</span>
                                    @else
                                        <span class="ui {{ $passportMonths > 6 ? 'green' : ($passportMonths > 3 ? 'yellow' : 'red') }} label">
                                            {{ $passportMonths }} months {{ $passportDays }} days left
                                        </span>
                                    @endif
                                @endif
                            </td>

                            <!-- VISA -->
                            <td>
                                @if($end)

                                    <div class="visa-expiry-date">{{ $end->format('d M Y') }}</div>

                                    @if($expired)
                                        <span class="ui red label">Expired</span>
                                    @else
                                        <span class="ui {{ $months > 6 ? 'green' : ($months > 3 ? 'yellow' : 'red') }} label">
                                            {{ $months }} months {{ $days }} days left
                                        </span>
                                    @endif

                                @else
                                    <span class="ui green label">British Citizen</span>
                                @endif
                            </td>

                            <!-- STATUS -->
                            <td>
                                @if($employee->employmentstatus === 'Active')
                                    <span class="ui green label">Active</span>
                                @else
                                    <span class="ui grey label">Archived</span>
                                @endif
                            </td>

                            <!-- ACTIONS -->
                            <td class="right aligned">

                                <a href="{{ url('/employee/'.$employee->id.'/documents') }}"
                                   class="ui circular basic icon button tiny blue">
                                    <i class="folder open icon"></i>
                                </a>

                                <a href="{{ url('/profile/view/'.$employee->reference) }}"
                                   class="ui circular basic icon button tiny green">
                                    <i class="file alternate outline icon"></i>
                                </a>

                                <a href="{{ url('/profile/edit/'.$employee->reference) }}"
                                   class="ui circular basic icon button tiny orange">
                                    <i class="edit outline icon"></i>
                                </a>

                                <a href="{{ url('/profile/delete/'.$employee->reference) }}"
                                   class="ui circular basic icon button tiny red">
                                    <i class="trash alternate outline icon"></i>
                                </a>

                                <a href="{{ url('/profile/archive/'.$employee->reference) }}"
                                   class="ui circular basic icon button tiny grey">
                                    <i class="archive icon"></i>
                                </a>

                                <a href="{{ route('employee.print.pdf', $employee->id) }}"
                                   class="ui circular basic icon button tiny purple"
                                   target="_blank">
                                    <i class="print icon"></i>
                                </a>

                                <button class="ui circular basic icon button tiny teal download-qr"
                                        data-pdf="{{ route('employee.print.pdf', $employee->id) }}">
                                    <i class="qrcode icon"></i>
                                </button>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>
        </div>
    </div>

</div>
@endsection


@section('scripts')

<!-- QR code generation for the printable-profile download button -->
<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll('.download-qr').forEach(btn => {
        btn.addEventListener('click', function () {

            const canvas = document.createElement('canvas');

            QRCode.toCanvas(canvas, this.dataset.pdf, { width: 300 }, function (err) {
                if (err) return alert("QR Error");

                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = 'employee-qr.png';
                link.click();
            });

        });
    });

});
</script>

<!--
    DataTables init.

    jQuery + DataTables (with Responsive extension bundled) are already
    loaded once, globally, by layouts/default.blade.php — do NOT add
    another copy of jQuery/DataTables here. A second copy causes the
    table to appear to initialize multiple times on one page.

    The isDataTable() guard below is defensive: it lets this script run
    safely even if the view is ever re-rendered into the DOM without a
    full page reload (e.g. via AJAX/partial navigation) without throwing
    "table already initialized" errors or stacking duplicate instances.
-->
<script>
$(document).ready(function () {
    if ($.fn.DataTable.isDataTable('#dataTables')) {
        $('#dataTables').DataTable().destroy();
    }

    $('#dataTables').DataTable({
        responsive: true,
        pageLength: 15,
        lengthChange: false,
        searching: true,
        ordering: true
    });
});
</script>

@endsection