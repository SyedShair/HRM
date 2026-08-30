@extends('layouts.default')
 @php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';
@endphp

@section('meta')
    <title>Audit Log | {{ $appName }}</title>
    <meta name="description" content="System activity and audit log.">
@endsection

@section('styles')
<style>
    .audit-stats{
        display:grid;
        grid-template-columns:repeat(4, 1fr);
        gap:14px;
        margin-bottom:20px;
    }
    @media (max-width:991px){ .audit-stats{ grid-template-columns:repeat(2, 1fr); } }
    .audit-stat-card{
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:8px;
        padding:16px;
        box-shadow:0 1px 3px rgba(0,0,0,0.05);
    }
    .audit-stat-card .value{
        font-size:24px;
        font-weight:800;
        color:#111827;
    }
    .audit-stat-card .label{
        font-size:11px;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#6b7280;
        margin-top:2px;
    }
    .audit-filters{
        background:#f9fafb;
        border:1px solid #e5e7eb;
        border-radius:8px;
        padding:14px;
        margin-bottom:18px;
    }
    .audit-filters .fields{ margin-bottom:10px !important; }
    table.audit-table{ width:100%; border-collapse:collapse; font-size:13px; }
    table.audit-table th{
        background:#111827; color:#fff; padding:9px 10px; text-align:left;
        font-size:11px; text-transform:uppercase; letter-spacing:.03em;
    }
    table.audit-table td{ padding:8px 10px; border-bottom:1px solid #eef2f5; }
    table.audit-table tr:hover td{ background:#f9fafb; cursor:pointer; }
    .badge-severity{
        display:inline-block; padding:2px 8px; border-radius:10px;
        font-size:10px; font-weight:700; text-transform:uppercase;
    }
    .badge-info{ background:#dbeafe; color:#1e40af; }
    .badge-success{ background:#dcfce7; color:#166534; }
    .badge-warning{ background:#fef3c7; color:#92400e; }
    .badge-danger{ background:#fee2e2; color:#991b1b; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
        <div class="ui positive message"><i class="close icon"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="ui negative message"><i class="close icon"></i>{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title">
                {{ __('Audit Log') }}
                <button type="button" id="bulk-delete-btn" class="ui red basic button mini offsettop5 float-right" disabled>
                    <i class="ui icon trash alternate outline"></i> {{ __('Delete Selected') }}
                </button>
                <a href="{{ route('audit.sessions') }}" class="ui basic button mini offsettop5 float-right">
                    <i class="ui icon user circle"></i> {{ __('Live Sessions') }}
                </a>
                <a href="{{ route('audit.export', 'csv') }}?{{ http_build_query(request()->query()) }}"
                   class="ui basic button mini offsettop5 float-right">
                    <i class="ui icon download"></i> {{ __('Export CSV') }}
                </a>
            </h2>
        </div>
    </div>

    <div class="audit-stats">
        <div class="audit-stat-card"><div class="value">{{ $stats['total_today'] }}</div><div class="label">{{ __('Activities Today') }}</div></div>
        <div class="audit-stat-card"><div class="value">{{ $stats['online_now'] }}</div><div class="label">{{ __('Online Now') }}</div></div>
        <div class="audit-stat-card"><div class="value">{{ $stats['logins_today'] }}</div><div class="label">{{ __('Logins Today') }}</div></div>
        <div class="audit-stat-card"><div class="value">{{ $stats['failed_logins'] }}</div><div class="label">{{ __('Failed Logins Today') }}</div></div>
        <div class="audit-stat-card"><div class="value">{{ $stats['creates_today'] }}</div><div class="label">{{ __('Creates Today') }}</div></div>
        <div class="audit-stat-card"><div class="value">{{ $stats['updates_today'] }}</div><div class="label">{{ __('Updates Today') }}</div></div>
        <div class="audit-stat-card"><div class="value">{{ $stats['deletes_today'] }}</div><div class="label">{{ __('Deletes Today') }}</div></div>
        <div class="audit-stat-card"><div class="value">{{ $stats['logouts_today'] }}</div><div class="label">{{ __('Logouts Today') }}</div></div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-body">

                    <form method="GET" action="{{ route('audit.index') }}" class="ui form audit-filters">
                        <div class="four fields">
                            <div class="field">
                                <label>{{ __('Search') }}</label>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="User, IP, action, table...">
                            </div>
                            <div class="field">
                                <label>{{ __('Date Range') }}</label>
                                <select name="range" class="ui dropdown">
                                    <option value="">{{ __('All time') }}</option>
                                    <option value="today" @selected(request('range')=='today')>{{ __('Today') }}</option>
                                    <option value="yesterday" @selected(request('range')=='yesterday')>{{ __('Yesterday') }}</option>
                                    <option value="week" @selected(request('range')=='week')>{{ __('This Week') }}</option>
                                    <option value="month" @selected(request('range')=='month')>{{ __('This Month') }}</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>{{ __('Category') }}</label>
                                <select name="category" class="ui dropdown">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($filters['categories'] as $c)
                                        <option value="{{ $c }}" @selected(request('category')==$c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>{{ __('Severity') }}</label>
                                <select name="severity" class="ui dropdown">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($filters['severities'] as $s)
                                        <option value="{{ $s }}" @selected(request('severity')==$s)>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="four fields">
                            <div class="field">
                                <label>{{ __('Action') }}</label>
                                <select name="action" class="ui dropdown">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($filters['actions'] as $a)
                                        <option value="{{ $a }}" @selected(request('action')==$a)>{{ ucfirst(str_replace('_',' ',$a)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>{{ __('Module') }}</label>
                                <select name="module" class="ui dropdown">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($filters['modules'] as $m)
                                        <option value="{{ $m }}" @selected(request('module')==$m)>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>{{ __('Role') }}</label>
                                <select name="role" class="ui dropdown">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach($filters['roles'] as $r)
                                        <option value="{{ $r }}" @selected(request('role')==$r)>{{ $r }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field" style="align-self:flex-end;">
                                <button type="submit" class="ui green button small fluid">{{ __('Apply Filters') }}</button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive-wrap" style="overflow-x:auto;">
                        <table class="audit-table" id="audit-table" width="100%" data-order='[[ 1, "desc" ]]'>
                            <thead>
                                <tr>
                                    <th style="width:36px;"><input type="checkbox" id="select-all-audit"></th>
                                    <th>{{ __('Date/Time') }}</th>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th>{{ __('Module') }}</th>
                                    <th>{{ __('Table') }}</th>
                                    <th>{{ __('Record ID') }}</th>
                                    <th>{{ __('Severity') }}</th>
                                    <th>{{ __('IP') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $a)
                                    <tr onclick="window.location='{{ route('audit.show', $a->id) }}'">
                                        <td onclick="event.stopPropagation()"><input type="checkbox" class="row-checkbox" value="{{ $a->id }}"></td>
                                        <td>{{ \Carbon\Carbon::parse($a->created_at)->format('d M Y H:i:s') }}</td>
                                        <td>{{ $a->user_name ?? __('Guest') }}</td>
                                        <td>{{ $a->role ?? '—' }}</td>
                                        <td>{{ $a->category }}</td>
                                        <td>{{ ucfirst(str_replace('_',' ',$a->action)) }}</td>
                                        <td>{{ $a->module ?? '—' }}</td>
                                        <td>{{ $a->table_name ?? '—' }}</td>
                                        <td>{{ $a->record_id ?? '—' }}</td>
                                        <td><span class="badge-severity badge-{{ $a->severity }}">{{ $a->severity }}</span></td>
                                        <td>{{ $a->ip_address }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" style="text-align:center; padding:24px; color:#9ca3af;">{{ __('No activity found for these filters.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- ================= BULK DELETE FORM (hidden - populated by JS on confirm) ================= -->
    <form id="bulk-delete-form" method="POST" action="{{ route('audit.bulkDelete') }}" style="display:none;">
        @csrf
        <div id="bulk-delete-ids-container"></div>
    </form>

    <!-- ================= BULK DELETE CONFIRMATION MODAL ================= -->
    <div class="ui basic modal" id="bulkDeleteConfirmModal">
        <div class="ui icon header" style="border:none;">
            <i class="trash alternate outline icon" style="color:#d93025;"></i>
            {{ __('Delete Selected Records') }}
        </div>
        <div class="content" style="text-align:center; color:#e5e7eb;">
            <p style="font-size:15px; margin:0;">
                {{ __('Are you sure you want to permanently delete') }}
                <strong id="bulkDeleteCount" style="color:#fff;"></strong> {{ __('selected record(s)?') }}
            </p>
            <p style="font-size:12px; color:#9ca3af; margin-top:8px;">
                {{ __('This action cannot be undone.') }}
            </p>
        </div>
        <div class="actions" style="text-align:center; border:none; padding-bottom:20px;">
            <div class="ui red basic inverted cancel button">
                <i class="times icon"></i> {{ __('Cancel') }}
            </div>
            <div class="ui red inverted ok button" id="bulkDeleteConfirmButton">
                <i class="checkmark icon"></i> {{ __('Yes, Delete') }}
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    $('.audit-filters .ui.dropdown').dropdown();

    var auditTable = $('#audit-table').DataTable({
        responsive: true,
        pageLength: 25,
        lengthChange: true,
        searching: true,
        ordering: true,
        columnDefs: [{ orderable: false, searchable: false, targets: 0 }]
    });

    function updateBulkDeleteState() {
        var anyChecked = $('.row-checkbox:checked').length > 0;
        $('#bulk-delete-btn').prop('disabled', !anyChecked);
    }

    // "Select all" only selects rows currently matching the DataTables
    // search/filter (not rows hidden by it) - matches what a user
    // filtering the table down would actually expect "select all" to mean.
    $('#select-all-audit').on('change', function () {
        var checked = this.checked;
        auditTable.rows({ search: 'applied' }).every(function () {
            $(this.node()).find('.row-checkbox').prop('checked', checked);
        });
        updateBulkDeleteState();
    });

    // Clicking a row checkbox must not also trigger the row's own
    // onclick (which navigates to the activity detail page).
    $(document).on('click', '.row-checkbox', function (e) {
        e.stopPropagation();
    });
    $(document).on('change', '.row-checkbox', updateBulkDeleteState);

    $('#bulk-delete-btn').on('click', function () {
        var count = $('.row-checkbox:checked').length;
        if (count === 0) return;
        $('#bulkDeleteCount').text(count);
        $('#bulkDeleteConfirmModal').modal('show');
    });

    $('#bulkDeleteConfirmButton').on('click', function () {
        var $container = $('#bulk-delete-ids-container').empty();
        $('.row-checkbox:checked').each(function () {
            $container.append('<input type="hidden" name="ids[]" value="' + $(this).val() + '">');
        });
        $('#bulk-delete-form').submit();
    });
</script>
@endsection