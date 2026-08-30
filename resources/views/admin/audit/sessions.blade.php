@extends('layouts.default')
 @php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';
@endphp

@section('meta')
    <title>Live Sessions | {{ $appName }}</title>
@endsection

@section('styles')
<style>
    table.sessions-table{ width:100%; border-collapse:collapse; font-size:13px; }
    table.sessions-table th{ background:#111827; color:#fff; padding:9px 10px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.03em; }
    table.sessions-table td{ padding:8px 10px; border-bottom:1px solid #eef2f5; }
    .badge-status{ display:inline-block; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; text-transform:uppercase; }
    .badge-online{ background:#dcfce7; color:#166534; }
    .badge-offline{ background:#f3f4f6; color:#6b7280; }
    .badge-expired{ background:#fef3c7; color:#92400e; }
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
                {{ __('Live Sessions') }}
                <span style="font-size:13px; font-weight:400; color:#6b7280;">
                    ({{ __('a session is marked offline after :n minutes of inactivity', ['n' => $timeout]) }})
                </span>
                <button type="button" id="bulk-delete-btn" class="ui red basic button mini offsettop5 float-right" disabled>
                    <i class="ui icon trash alternate outline"></i> {{ __('Delete Selected') }}
                </button>
                <a href="{{ route('audit.index') }}" class="ui basic button mini offsettop5 float-right">
                    <i class="ui icon arrow left"></i> {{ __('Back to Audit Log') }}
                </a>
            </h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-body" style="overflow-x:auto;">
                    <table class="sessions-table" id="sessions-table" width="100%" data-order='[[ 4, "desc" ]]'>
                        <thead>
                            <tr>
                                <th style="width:36px;"><input type="checkbox" id="select-all-sessions"></th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Login Time') }}</th>
                                <th>{{ __('Last Activity') }}</th>
                                <th>{{ __('Logout Time') }}</th>
                                <th>{{ __('IP Address') }}</th>
                                <th>{{ __('Browser') }}</th>
                                <th>{{ __('Device') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $s)
                                <tr>
                                    <td><input type="checkbox" class="row-checkbox" value="{{ $s->id }}"></td>
                                    <td>{{ $s->user_name ?? ('User #'.$s->user_id) }}</td>
                                    <td>{{ $s->role_name ?? $s->acc_type ?? '—' }}</td>
                                    <td>{{ $s->login_at ? \Carbon\Carbon::parse($s->login_at)->format('d M Y H:i:s') : '—' }}</td>
                                    <td>{{ $s->last_activity_at ? \Carbon\Carbon::parse($s->last_activity_at)->diffForHumans() : '—' }}</td>
                                    <td>{{ $s->logout_at ? \Carbon\Carbon::parse($s->logout_at)->format('d M Y H:i:s') : '—' }}</td>
                                    <td>{{ $s->ip_address }}</td>
                                    <td>{{ $s->browser }}</td>
                                    <td>{{ $s->device }}</td>
                                    <td><span class="badge-status badge-{{ $s->status }}">{{ $s->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="10" style="text-align:center; padding:24px; color:#9ca3af;">{{ __('No sessions recorded yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= BULK DELETE FORM (hidden - populated by JS on confirm) ================= -->
    <form id="bulk-delete-sessions-form" method="POST" action="{{ route('audit.sessions.bulkDelete') }}" style="display:none;">
        @csrf
        <div id="bulk-delete-sessions-ids-container"></div>
    </form>

    <!-- ================= BULK DELETE CONFIRMATION MODAL ================= -->
    <div class="ui basic modal" id="bulkDeleteSessionsConfirmModal">
        <div class="ui icon header" style="border:none;">
            <i class="trash alternate outline icon" style="color:#d93025;"></i>
            {{ __('Delete Selected Sessions') }}
        </div>
        <div class="content" style="text-align:center; color:#e5e7eb;">
            <p style="font-size:15px; margin:0;">
                {{ __('Are you sure you want to permanently delete') }}
                <strong id="bulkDeleteSessionsCount" style="color:#fff;"></strong> {{ __('selected session(s)?') }}
            </p>
            <p style="font-size:12px; color:#9ca3af; margin-top:8px;">
                {{ __('This action cannot be undone.') }}
            </p>
        </div>
        <div class="actions" style="text-align:center; border:none; padding-bottom:20px;">
            <div class="ui red basic inverted cancel button">
                <i class="times icon"></i> {{ __('Cancel') }}
            </div>
            <div class="ui red inverted ok button" id="bulkDeleteSessionsConfirmButton">
                <i class="checkmark icon"></i> {{ __('Yes, Delete') }}
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    var sessionsTable = $('#sessions-table').DataTable({
        responsive: true,
        pageLength: 25,
        lengthChange: true,
        searching: true,
        ordering: true,
        columnDefs: [{ orderable: false, searchable: false, targets: 0 }]
    });

    function updateSessionsBulkDeleteState() {
        var anyChecked = $('#sessions-table .row-checkbox:checked').length > 0;
        $('#bulk-delete-btn').prop('disabled', !anyChecked);
    }

    $('#select-all-sessions').on('change', function () {
        var checked = this.checked;
        sessionsTable.rows({ search: 'applied' }).every(function () {
            $(this.node()).find('.row-checkbox').prop('checked', checked);
        });
        updateSessionsBulkDeleteState();
    });

    $(document).on('change', '#sessions-table .row-checkbox', updateSessionsBulkDeleteState);

    $('#bulk-delete-btn').on('click', function () {
        var count = $('#sessions-table .row-checkbox:checked').length;
        if (count === 0) return;
        $('#bulkDeleteSessionsCount').text(count);
        $('#bulkDeleteSessionsConfirmModal').modal('show');
    });

    $('#bulkDeleteSessionsConfirmButton').on('click', function () {
        var $container = $('#bulk-delete-sessions-ids-container').empty();
        $('#sessions-table .row-checkbox:checked').each(function () {
            $container.append('<input type="hidden" name="ids[]" value="' + $(this).val() + '">');
        });
        $('#bulk-delete-sessions-form').submit();
    });
</script>
@endsection