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
    <title>Employees | {{ $appName }}</title>
    <meta name="description" content="Workday employees management system">
@endsection

@section('content')

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

#dataTables tbody tr.expiring-row {
    animation: expiryBlink 1.2s ease-in-out infinite;
    border-left: 4px solid #d93025 !important;
}

#dataTables tbody tr.expiring-row:hover {
    animation: expiryBlink 1.2s ease-in-out infinite;
}

@keyframes expiryBlink {
    0%, 100% { background-color: #ffffff; }
    50% { background-color: #ffb3b3; }
}

.visa-expiry-sub {
    font-size: 11px;
    color: #6b7280;
}

.passport-expiry-warning {
    color: #d93025;
    font-weight: 600;
}

/*
    Passport No / Share Code eye toggle. The raw value is hidden by
    default (.secret-value) - clicking the eye icon next to it adds
    .revealed and flips the icon to "eye slash". The remaining-time
    badge underneath is unaffected either way, so the column is always
    useful even with the value hidden.
*/
.secret-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 5px;
}

.secret-value {
    display: none;
    font-weight: 600;
    color: #1f2937;
    letter-spacing: 0.02em;
}

.secret-value.revealed {
    display: inline;
}

.toggle-secret {
    color: #6b7280;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
}

.toggle-secret:hover {
    color: #1f2937;
}

.toggle-secret i.icon {
    margin: 0 !important;
    font-size: 13px !important;
}

.cell-muted {
    color: #9ca3af;
}

/* Preloader overlay for AJAX table refresh */
#tablePreloader {
    display: none;
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,0.7);
    z-index: 10;
    align-items: center;
    justify-content: center;
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

    <!-- ================= COMPANY FILTER ================= -->
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-3">
            <select id="companyFilterSelect" class="ui fluid dropdown"
                    style="padding:8px 10px; border:1px solid #e5e7eb; border-radius:6px; width:100%;">
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ (string) $companyId === (string) $c->id ? 'selected' : '' }}>
                        {{ $c->company }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- ================= SUMMARY ================= -->
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-3">
            <div class="box box-solid" style="padding:12px 16px; border:1px solid #e5e7eb; border-radius:6px;">
                <div style="font-size:11px; color:#6b7280; text-transform:uppercase;">Total Employees</div>
                <div id="summaryTotal" style="font-size:22px; font-weight:600;">{{ $total }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-solid" style="padding:12px 16px; border:1px solid #e5e7eb; border-radius:6px;">
                <div style="font-size:11px; color:#6b7280; text-transform:uppercase;">Active</div>
                <div id="summaryActive" style="font-size:22px; font-weight:600; color:#16794f;">{{ $active }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-solid" style="padding:12px 16px; border:1px solid #e5e7eb; border-radius:6px;">
                <div style="font-size:11px; color:#6b7280; text-transform:uppercase;">Visas Expiring (90 days)</div>
                <div id="summaryExpiring" style="font-size:22px; font-weight:600; color:#b7791f;">{{ $expiring }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-solid" style="padding:12px 16px; border:1px solid #e5e7eb; border-radius:6px;">
                <div style="font-size:11px; color:#6b7280; text-transform:uppercase;">Visas Expired</div>
                <div id="summaryExpired" style="font-size:22px; font-weight:600; color:#d93025;">{{ $expired }}</div>
            </div>
        </div>
    </div>

    <!-- ================= TABLE ================= -->
    <div class="row">
        <div class="box box-success">
            <div class="box-body" style="position: relative;">

                <!-- Preloader overlay -->
                <div id="tablePreloader">
                    <div class="ui active inline loader"></div>
                </div>

                <table class="table table-striped table-hover" id="dataTables" width="100%">

                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee</th>
                        <th>Company</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Share Code</th>
                        <th>Passport</th>
                        <th>Visa Expiry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody id="employeesTableBody">
                        @include('admin.partials.employees-rows')
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

    /*
        Single delegated listener for both actions below - delegated
        (bound on document.body, matched by closest()) rather than bound
        directly to each row's elements, because rows get swapped out
        wholesale by the company-filter AJAX call further down. A
        directly-bound handler would only work on the rows present at
        page load and silently stop working on any row loaded afterward.
    */
    document.body.addEventListener('click', function (e) {

        // ---- QR download ----
        const qrBtn = e.target.closest('.download-qr');
        if (qrBtn) {
            const canvas = document.createElement('canvas');

            QRCode.toCanvas(canvas, qrBtn.dataset.pdf, { width: 300 }, function (err) {
                if (err) return alert("QR Error");

                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = 'employee-qr.png';
                link.click();
            });
            return;
        }

        // ---- Passport No / Share Code eye toggle ----
        const toggleBtn = e.target.closest('.toggle-secret');
        if (toggleBtn) {
            const row = toggleBtn.closest('.secret-row');
            const value = row ? row.querySelector('.secret-value') : null;
            if (!value) return;

            const revealed = value.classList.toggle('revealed');

            const icon = toggleBtn.querySelector('i.icon');
            icon.classList.toggle('slash', revealed);

            toggleBtn.title = revealed ? 'Hide value' : 'Show value';
        }
    });

});
</script>

<!--
    DataTables init + AJAX company filter.

    jQuery + DataTables (with Responsive extension bundled) are already
    loaded once, globally, by layouts/default.blade.php — do NOT add
    another copy of jQuery/DataTables here.

    A single DataTable instance is created once and kept alive; the
    company filter swaps its row data via clear()/rows.add()/draw()
    instead of destroying and reinitializing the table, which is the
    officially supported way to refresh DataTables content and avoids
    "already initialized" errors or stale pagination counts.
-->
<script>
$(document).ready(function () {

    var employeesTable = $('#dataTables').DataTable({
        responsive: true,
        pageLength: 15,
        lengthChange: false,
        searching: true,
        ordering: true
    });

    function showPreloader() {
        $('#tablePreloader').css('display', 'flex');
    }
    function hidePreloader() {
        $('#tablePreloader').css('display', 'none');
    }

    $('#companyFilterSelect').on('change', function () {
        const companyId = $(this).val();

        showPreloader();

        $.ajax({
            url: '{{ route("employees.filter") }}',
            method: 'GET',
            data: { company_id: companyId },
            dataType: 'json',
            success: function (response) {
                const $newRows = $('<tbody>' + response.rows + '</tbody>').find('tr');

                employeesTable.clear();
                employeesTable.rows.add($newRows);
                employeesTable.draw();

                $('#summaryTotal').text(response.total);
                $('#summaryActive').text(response.active);
                $('#summaryExpiring').text(response.expiring);
                $('#summaryExpired').text(response.expired);
            },
            error: function () {
                alert('Something went wrong while filtering employees.');
            },
            complete: function () {
                hidePreloader();
            }
        });
    });

});
</script>

@endsection