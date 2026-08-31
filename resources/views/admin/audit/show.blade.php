@extends('layouts.default')
 @php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';
@endphp

@section('meta')
    <title>Activity Detail | {{ $appName }}</title>
@endsection

@section('styles')
<style>
    .detail-grid{ display:grid; grid-template-columns:repeat(2, 1fr); gap:12px 24px; margin-bottom:20px; }
    @media (max-width:767px){ .detail-grid{ grid-template-columns:1fr; } }
    .detail-row{ font-size:13px; }
    .detail-row .label{ color:#6b7280; text-transform:uppercase; font-size:11px; letter-spacing:.03em; margin-bottom:2px; }
    .detail-row .value{ color:#111827; font-weight:600; word-break:break-word; }
    table.diff-table{ width:100%; border-collapse:collapse; font-size:13px; margin-top:10px; }
    table.diff-table th{ background:#111827; color:#fff; padding:8px 10px; text-align:left; font-size:11px; text-transform:uppercase; }
    table.diff-table td{ padding:8px 10px; border-bottom:1px solid #eef2f5; }
    .old-val{ color:#991b1b; }
    .new-val{ color:#166534; }
    .badge-severity{ display:inline-block; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; text-transform:uppercase; }
    .badge-info{ background:#dbeafe; color:#1e40af; }
    .badge-success{ background:#dcfce7; color:#166534; }
    .badge-warning{ background:#fef3c7; color:#92400e; }
    .badge-danger{ background:#fee2e2; color:#991b1b; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title">
                {{ __('Activity Detail') }}
                <a href="{{ route('audit.index') }}" class="ui basic button mini offsettop5 float-right">
                    <i class="ui icon arrow left"></i> {{ __('Back to Audit Log') }}
                </a>
            </h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-body">

                    <div class="detail-grid">
                        <div class="detail-row"><div class="label">{{ __('User') }}</div><div class="value">{{ $activity->user_name ?? __('Guest') }} (ID: {{ $activity->user_id ?? '—' }})</div></div>
                        <div class="detail-row"><div class="label">{{ __('Role') }}</div><div class="value">{{ $activity->role ?? '—' }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('Category') }}</div><div class="value">{{ $activity->category }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('Action') }}</div><div class="value">{{ ucfirst(str_replace('_',' ',$activity->action)) }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('Severity') }}</div><div class="value"><span class="badge-severity badge-{{ $activity->severity }}">{{ $activity->severity }}</span></div></div>
                        <div class="detail-row"><div class="label">{{ __('Module') }}</div><div class="value">{{ $activity->module ?? '—' }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('Table') }}</div><div class="value">{{ $activity->table_name ?? '—' }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('Record ID') }}</div><div class="value">{{ $activity->record_id ?? '—' }}</div></div>
                        <div class="detail-row" style="grid-column:1/-1;"><div class="label">{{ __('Description') }}</div><div class="value">{{ $activity->description ?? '—' }}</div></div>
                        <div class="detail-row" style="grid-column:1/-1;"><div class="label">{{ __('URL') }}</div><div class="value">{{ $activity->url ?? '—' }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('Route Name') }}</div><div class="value">{{ $activity->route_name ?? '—' }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('HTTP Method') }}</div><div class="value">{{ $activity->http_method ?? '—' }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('IP Address') }}</div><div class="value">{{ $activity->ip_address ?? '—' }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('Browser') }}</div><div class="value">{{ $activity->browser ?? '—' }} {{ $activity->browser_version ?? '' }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('Operating System') }}</div><div class="value">{{ $activity->operating_system ?? '—' }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('Device') }}</div><div class="value">{{ $activity->device ?? '—' }}</div></div>
                        <div class="detail-row"><div class="label">{{ __('Timestamp') }}</div><div class="value">{{ \Carbon\Carbon::parse($activity->created_at, config('audit.timezone', 'Europe/London'))->format('d M Y H:i:s') }}</div></div>
                    </div>

                    @if($oldData || $newData)
                        <h4 class="ui dividing header">{{ __('Field Changes') }}</h4>

                        @php
                            $changedFields = $metadata['changed_fields'] ?? array_keys($newData ?? []);
                            $allFields = array_unique(array_merge(array_keys($oldData ?? []), array_keys($newData ?? [])));
                        @endphp

                        <table class="diff-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Field') }}</th>
                                    <th>{{ __('Old Value') }}</th>
                                    <th>{{ __('New Value') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allFields as $field)
                                    @php
                                        $oldVal = $oldData[$field] ?? null;
                                        $newVal = $newData[$field] ?? null;
                                        $changed = in_array($field, $changedFields, true);
                                    @endphp
                                    @if(!$oldData || $changed || !$newData)
                                        <tr>
                                            <td>{{ $field }}</td>
                                            <td class="old-val">{{ $oldVal ?? '—' }}</td>
                                            <td class="new-val">{{ $newVal ?? '—' }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr><td colspan="3" style="text-align:center; color:#9ca3af;">{{ __('No field-level data available for this activity.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>

                        @if($newData && !$oldData)
                            <p style="font-size:12px; color:#9ca3af; margin-top:8px;">
                                {{ __('Old values were not available for this activity - either it was a create, or the WHERE clause was too complex to safely pre-fetch the previous row (see the Audit system docs).') }}
                            </p>
                        @endif
                    @endif

                    @if($metadata)
                        <h4 class="ui dividing header">{{ __('Additional Metadata') }}</h4>
                        <pre style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:12px; font-size:12px; overflow-x:auto;">{{ json_encode($metadata, JSON_PRETTY_PRINT) }}</pre>
                    @endif

                </div>
            </div>
        </div>
    </div>

</div>
@endsection