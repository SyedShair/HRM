@extends('layouts.default')
 @php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Company';
@endphp

@section('meta')
    <title>Activity Detail | {{ $appName }}</title>
@endsection

@section('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
    :root{
        --ink:#161A23;
        --ink-soft:#4A5169;
        --muted:#8991A6;
        --page-bg:#EEF1F6;
        --card:#FFFFFF;
        --line:#E3E7EF;
        --accent:#3F3BD6;
        --accent-soft:#EEEDFC;
        --info-fg:#1D4ED8;   --info-bg:#DDE7FD;
        --success-fg:#157A44; --success-bg:#DCF6E7;
        --warning-fg:#A15C07; --warning-bg:#FCEECB;
        --danger-fg:#B5222A;  --danger-bg:#FBDEDF;
        --mono: 'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
        --sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .audit-page{
        font-family:var(--sans);
        color:var(--ink);
        background:var(--page-bg);
        margin:-15px -15px 0;
        padding:28px 15px 60px;
    }

    /* ---------- Header ---------- */
    .audit-header{
        max-width:980px;
        margin:0 auto 22px;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:16px;
        flex-wrap:wrap;
    }
    .audit-header .back-link{
        display:inline-flex;
        align-items:center;
        gap:6px;
        font-size:13px;
        font-weight:600;
        color:var(--ink-soft);
        text-decoration:none;
        margin-bottom:10px;
    }
    .audit-header .back-link:hover{ color:var(--accent); }
    .audit-header h1{
        font-size:26px;
        font-weight:700;
        letter-spacing:-0.01em;
        margin:0 0 4px;
        color:var(--ink);
    }
    .audit-header .subline{
        font-size:13.5px;
        color:var(--muted);
        display:flex;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
    }
    .badge-severity{
        display:inline-block;
        padding:3px 11px;
        border-radius:100px;
        font-size:11.5px;
        font-weight:600;
        line-height:1.5;
    }
    .badge-info{ background:var(--info-bg); color:var(--info-fg); }
    .badge-success{ background:var(--success-bg); color:var(--success-fg); }
    .badge-warning{ background:var(--warning-bg); color:var(--warning-fg); }
    .badge-danger{ background:var(--danger-bg); color:var(--danger-fg); }

    /* ---------- Shell ---------- */
    .audit-shell{
        max-width:980px;
        margin:0 auto;
        display:flex;
        flex-direction:column;
        gap:16px;
    }
    .record-card{
        background:var(--card);
        border:1px solid var(--line);
        border-radius:10px;
        padding:22px 24px;
    }
    .record-card + .record-card{ margin-top:0; }

    .group-title{
        font-size:12.5px;
        font-weight:600;
        color:var(--accent);
        margin:0 0 14px;
        padding-bottom:10px;
        border-bottom:1px solid var(--line);
    }

    .fact-grid{
        display:grid;
        grid-template-columns:repeat(3, 1fr);
        gap:16px 24px;
    }
    .fact-grid.cols-2{ grid-template-columns:repeat(2, 1fr); }
    @media (max-width:767px){
        .fact-grid, .fact-grid.cols-2{ grid-template-columns:1fr; }
    }
    .fact{ min-width:0; }
    .fact .label{
        font-size:12px;
        color:var(--muted);
        margin-bottom:3px;
    }
    .fact .value{
        font-size:14px;
        font-weight:600;
        color:var(--ink);
        word-break:break-word;
    }
    .fact .value.mono{
        font-family:var(--mono);
        font-weight:500;
        font-size:13px;
        color:var(--ink-soft);
    }
    .fact .value.full{ grid-column:1/-1; font-weight:400; color:var(--ink-soft); }

    /* ---------- Diff table ---------- */
    table.diff-table{
        width:100%;
        border-collapse:collapse;
        font-size:13px;
        margin-top:4px;
    }
    table.diff-table th{
        text-align:left;
        font-size:11.5px;
        font-weight:600;
        color:var(--muted);
        padding:0 10px 10px;
        border-bottom:1px solid var(--line);
    }
    table.diff-table td{
        padding:10px;
        border-bottom:1px solid var(--line);
        font-family:var(--mono);
        font-size:12.5px;
        vertical-align:top;
    }
    table.diff-table tr:last-child td{ border-bottom:none; }
    table.diff-table td.field-name{
        font-family:var(--sans);
        font-weight:600;
        font-size:13px;
        color:var(--ink);
        white-space:nowrap;
    }
    .old-val{ color:var(--danger-fg); text-decoration:line-through; text-decoration-color:#E8AEB1; }
    .new-val{ color:var(--success-fg); font-weight:600; }
    .empty-note{
        text-align:center;
        color:var(--muted);
        font-family:var(--sans);
        padding:20px 10px;
    }
    .diff-footnote{
        font-size:12px;
        color:var(--muted);
        margin-top:10px;
        line-height:1.5;
    }

    /* ---------- Metadata panel ---------- */
    .meta-panel{
        background:#191D28;
        border-radius:8px;
        padding:16px 18px;
        overflow-x:auto;
    }
    .meta-panel pre{
        margin:0;
        font-family:var(--mono);
        font-size:12.5px;
        line-height:1.6;
        color:#D7DCEA;
        white-space:pre;
    }
</style>
@endsection

@section('content')
<div class="audit-page">

    <div class="audit-header">
        <div>
            <a href="{{ route('audit.index') }}" class="back-link">&larr; {{ __('Back to audit log') }}</a>
            <h1>{{ __('Activity detail') }}</h1>
            <div class="subline">
                <span class="badge-severity badge-{{ $activity->severity }}">{{ $activity->severity }}</span>
                <span>{{ ucfirst(str_replace('_',' ',$activity->action)) }} &middot; {{ $activity->category }}</span>
                <span>&middot; {{ \Carbon\Carbon::parse($activity->created_at)->format('d M Y, H:i:s') }}</span>
            </div>
        </div>
    </div>

    <div class="audit-shell">

        {{-- Who / what happened --}}
        <div class="record-card">
            <div class="group-title">{{ __('Who and what') }}</div>
            <div class="fact-grid">
                <div class="fact">
                    <div class="label">{{ __('User') }}</div>
                    <div class="value">{{ $activity->user_name ?? __('Guest') }} <span class="mono" style="font-size:12px;color:var(--muted);">#{{ $activity->user_id ?? '—' }}</span></div>
                </div>
                <div class="fact">
                    <div class="label">{{ __('Role') }}</div>
                    <div class="value">{{ $activity->role ?? '—' }}</div>
                </div>
                <div class="fact">
                    <div class="label">{{ __('Module') }}</div>
                    <div class="value">{{ $activity->module ?? '—' }}</div>
                </div>
                <div class="fact">
                    <div class="label">{{ __('Table') }}</div>
                    <div class="value mono">{{ $activity->table_name ?? '—' }}</div>
                </div>
                <div class="fact">
                    <div class="label">{{ __('Record ID') }}</div>
                    <div class="value mono">{{ $activity->record_id ?? '—' }}</div>
                </div>
                <div class="fact">
                    <div class="label">{{ __('Action') }}</div>
                    <div class="value">{{ ucfirst(str_replace('_',' ',$activity->action)) }}</div>
                </div>
                @if($activity->description)
                <div class="fact" style="grid-column:1/-1;">
                    <div class="label">{{ __('Description') }}</div>
                    <div class="value full">{{ $activity->description }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Request context --}}
        <div class="record-card">
            <div class="group-title">{{ __('Where it happened') }}</div>
            <div class="fact-grid">
                <div class="fact">
                    <div class="label">{{ __('IP address') }}</div>
                    <div class="value mono">{{ $activity->ip_address ?? '—' }}</div>
                </div>
                <div class="fact">
                    <div class="label">{{ __('HTTP method') }}</div>
                    <div class="value mono">{{ $activity->http_method ?? '—' }}</div>
                </div>
                <div class="fact">
                    <div class="label">{{ __('Route name') }}</div>
                    <div class="value mono">{{ $activity->route_name ?? '—' }}</div>
                </div>
                <div class="fact">
                    <div class="label">{{ __('Browser') }}</div>
                    <div class="value">{{ $activity->browser ?? '—' }} {{ $activity->browser_version ?? '' }}</div>
                </div>
                <div class="fact">
                    <div class="label">{{ __('Operating system') }}</div>
                    <div class="value">{{ $activity->operating_system ?? '—' }}</div>
                </div>
                <div class="fact">
                    <div class="label">{{ __('Device') }}</div>
                    <div class="value">{{ $activity->device ?? '—' }}</div>
                </div>
                <div class="fact" style="grid-column:1/-1;">
                    <div class="label">{{ __('URL') }}</div>
                    <div class="value mono full">{{ $activity->url ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Field changes --}}
        @if($oldData || $newData)
            @php
                $changedFields = $metadata['changed_fields'] ?? array_keys($newData ?? []);
                $allFields = array_unique(array_merge(array_keys($oldData ?? []), array_keys($newData ?? [])));
            @endphp
            <div class="record-card">
                <div class="group-title">{{ __('Field changes') }}</div>
                <table class="diff-table">
                    <thead>
                        <tr>
                            <th>{{ __('Field') }}</th>
                            <th>{{ __('Old value') }}</th>
                            <th>{{ __('New value') }}</th>
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
                                    <td class="field-name">{{ $field }}</td>
                                    <td class="old-val">{{ $oldVal ?? '—' }}</td>
                                    <td class="new-val">{{ $newVal ?? '—' }}</td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="3" class="empty-note">{{ __('No field-level data available for this activity.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>

                @if($newData && !$oldData)
                    <p class="diff-footnote">
                        {{ __('Old values were not available for this activity - either it was a create, or the WHERE clause was too complex to safely pre-fetch the previous row (see the Audit system docs).') }}
                    </p>
                @endif
            </div>
        @endif

        {{-- Raw metadata --}}
        @if($metadata)
            <div class="record-card">
                <div class="group-title">{{ __('Additional metadata') }}</div>
                <div class="meta-panel">
                    <pre>{{ json_encode($metadata, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection