@extends('layouts.default')
@php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
@endphp
@section('meta')
    <title>Employee Rota | {{ $appName }}</title>
    <meta name="description" content="Manage weekly employee schedules">
@endsection

@section('styles')
<style>
    /* ================= LAYOUT / GENERAL ================= */

    .rota-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 18px;
    }
    .rota-page-header .page-title {
        margin-bottom: 2px;
        font-weight: 700;
        color: #111827;
    }
    .rota-page-subtitle {
        font-size: 13px;
        color: #6b7280;
    }

    .box.box-success {
        border-radius: 10px;
        border-top: 3px solid #16a34a;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .box-header.with-border {
        font-size: 15px;
        font-weight: 600;
        padding: 14px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: #fafafa;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .box-body { padding: 18px; }

    /* ================= COMPANY FILTER ================= */

    .company-filter-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .company-filter-wrap .filter-icon {
        color: #16a34a;
        font-size: 15px;
    }
    .company-filter-wrap select {
        min-width: 220px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        padding: 9px 12px;
        font-size: 13px;
        background: #fff;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .company-filter-wrap select:focus {
        outline: none;
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22,163,74,0.12);
    }

    /* ================= MESSAGES ================= */

    .ui.positive.message, .ui.negative.message {
        border-radius: 8px;
    }

    /* ================= TABLE (desktop) ================= */

    .table-responsive-wrap { overflow-x: auto; }

    table.rota-table {
        width: 100%;
        border-collapse: collapse;
    }
    table.rota-table thead th {
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        padding: 10px 12px;
        border-bottom: 2px solid #e5e7eb;
        white-space: nowrap;
    }
    table.rota-table tbody td {
        padding: 14px 12px;
        border-bottom: 1px solid #f0f1f3;
        vertical-align: middle;
        font-size: 13px;
        color: #1f2937;
    }
    table.rota-table tbody tr {
        transition: background-color .12s ease;
    }
    table.rota-table tbody tr:hover {
        background-color: #f9fafb;
    }
    table.rota-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* ================= EMPLOYEE CELL ================= */

    .employee-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .rota-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e5e7eb;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        flex-shrink: 0;
    }
    .rota-name { font-weight: 700; color: #111827; line-height: 1.3; }
    .rota-position { font-size: 12px; color: #6b7280; margin-top: 1px; }

    /* ================= BADGES ================= */

    .rota-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .rota-badge::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }
    .badge-current { background: #dcfce7; color: #166534; }
    .badge-upcoming { background: #dbeafe; color: #1e40af; }
    .badge-archived { background: #fee2e2; color: #991b1b; }
    .badge-none { background: #f3f4f6; color: #6b7280; }

    /* ================= SCHEDULE DETAILS ================= */

    .rota-period { font-weight: 600; color: #1f2937; }
    .rota-days { font-size: 12px; color: #374151; margin-top: 4px; }
    .rota-restday { color: #dc2626; }
    .cell-muted { color: #9ca3af; }

    /* ================= ACTION BUTTONS ================= */

    .action-buttons {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .action-buttons .ui.button {
        margin: 0 !important;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 7px !important;
        font-weight: 600 !important;
        transition: transform .08s ease, box-shadow .12s ease;
    }
    .action-buttons .ui.button:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }

    /* ================= EMPTY STATE ================= */

    .rota-empty-state {
        text-align: center;
        padding: 46px 16px;
        color: #9ca3af;
    }
    .rota-empty-state .icon {
        font-size: 34px;
        margin-bottom: 10px;
        color: #d1d5db;
    }
    .rota-empty-state .empty-text {
        font-size: 14px;
    }

    /* ================= RESPONSIVE (MOBILE CARD LAYOUT) ================= */

    @media (max-width: 767px) {

        .rota-page-header { flex-direction: column; align-items: stretch; }
        .company-filter-wrap { width: 100%; }
        .company-filter-wrap select { width: 100%; min-width: 0; }

        .box-body { padding: 12px; }

        table.rota-table thead { display: none; }

        table.rota-table, table.rota-table tbody,
        table.rota-table tr, table.rota-table td {
            display: block;
            width: 100%;
        }

        table.rota-table tr {
            margin-bottom: 14px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        table.rota-table tr:hover { background-color: #ffffff; }

        table.rota-table td {
            border: none;
            padding: 7px 0;
            text-align: left !important;
        }

        table.rota-table td[data-label]::before {
            content: attr(data-label);
            display: block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #9ca3af;
            margin-bottom: 3px;
        }

        table.rota-table td.actions-cell {
            padding-top: 10px;
            border-top: 1px solid #f0f1f3;
            margin-top: 6px;
        }

        .action-buttons { justify-content: flex-start; }
        .action-buttons .ui.button { flex: 1 1 auto; justify-content: center; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    <div class="rota-page-header">
        <div>
            <h2 class="page-title">{{ __('Employee Rota') }}</h2>
            <div class="rota-page-subtitle">{{ __('Manage weekly work schedules for your team') }}</div>
        </div>
        <div class="company-filter-wrap">
            <i class="ui building icon filter-icon"></i>
            <select id="companySelect" class="form-control" onchange="window.location.href='{{ url('staff-rota') }}?company_id='+this.value">
                @forelse($companies as $company)
                    <option value="{{ $company->id }}" @selected($company->id == $companyId)>{{ $company->company }}</option>
                @empty
                    <option value="">{{ __('No companies found') }}</option>
                @endforelse
            </select>
        </div>
    </div>

    @if(session('success'))
        <div class="ui positive message"><i class="close icon"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="ui negative message"><i class="close icon"></i>{{ session('error') }}</div>
    @endif

    <div class="box box-success">
        <div class="box-header with-border">
            <span>{{ __('Employees') }}</span>
            <span class="cell-muted" style="font-size:12px;font-weight:500;">{{ $employees->count() }} {{ __('total') }}</span>
        </div>
        <div class="box-body">
            <div class="table-responsive-wrap">
            <table class="table responsive nobordertop rota-table">
                <thead>
                    <tr>
                        <th>{{ __('Employee') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Schedule Period') }}</th>
                        <th>{{ __('Working Days') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        @php
                            $active = $emp->currentSchedule;
                            $upcoming = $emp->nextSchedule;
                            $archived = $emp->archivedSchedule;
                            $shown = $active ?: $upcoming ?: $archived;
                        @endphp
                        <tr>
                            <td data-label="{{ __('Employee') }}">
                                <div class="employee-cell">
                                    <img class="rota-avatar" src="{{ $emp->avatar ? asset('storage/'.$emp->avatar) : asset('/assets/images/default.png') }}" alt="">
                                    <div>
                                        <div class="rota-name">{{ $emp->lastname }}, {{ $emp->firstname }}</div>
                                        <div class="rota-position">{{ $emp->jobposition }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="{{ __('Status') }}">
                                @if($active)
                                    <span class="rota-badge badge-current">{{ __('Active Schedule') }}</span>
                                @elseif($upcoming)
                                    <span class="rota-badge badge-upcoming">{{ __('Upcoming') }}</span>
                                @elseif($archived)
                                    <span class="rota-badge badge-archived">{{ __('Archived') }}</span>
                                @else
                                    <span class="rota-badge badge-none">{{ __('No Schedule') }}</span>
                                @endif
                            </td>
                            <td data-label="{{ __('Schedule Period') }}">
                                @if($shown)
                                    <div class="rota-period">{{ date('M d, Y', strtotime($shown->datefrom)) }} &ndash; {{ date('M d, Y', strtotime($shown->dateto)) }}</div>
                                @else
                                    <span class="cell-muted">&mdash;</span>
                                @endif
                            </td>
                            <td data-label="{{ __('Working Days') }}">
                                @if($shown)
                                    @php
                                        $restDays = array_filter(explode(',', $shown->restday ?? ''));
                                    @endphp
                                    <div class="rota-days">
                                        @if($shown->intime && $shown->outime)
                                            {{ $shown->intime }} - {{ $shown->outime }}
                                        @endif
                                        @if(!empty($restDays))
                                            <br><span class="rota-restday">{{ __('Off') }}: {{ implode(', ', $restDays) }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="cell-muted">&mdash;</span>
                                @endif
                            </td>
                            <td data-label="{{ __('Actions') }}" class="text-right actions-cell">
                                <div class="action-buttons">
                                    {{-- View PDF: shown whenever there is any schedule to show at all,
                                         active, upcoming, or archived - lets a manager print/reference
                                         a rota regardless of its current status. --}}
                                    @if($shown)
                                        <a href="{{ route('rota.pdf', $shown->id) }}" target="_blank" class="ui button small basic">
                                            <i class="ui file pdf outline icon"></i>{{ __('PDF') }}
                                        </a>
                                    @endif

                                    @if($active || $upcoming)
                                        <a href="{{ url('schedules/edit/'.$shown->id) }}" class="ui button small basic">
                                            <i class="ui edit icon"></i>{{ __('Edit') }}
                                        </a>
                                        <a href="{{ url('schedules/archive/'.$shown->id) }}" class="ui button small basic" onclick="return confirm('{{ __('Archive this schedule?') }}');">
                                            <i class="ui archive icon"></i>{{ __('Archive') }}
                                        </a>
                                    @elseif($archived)
                                        {{-- Archived schedules can no longer be edited or re-archived -
                                             offer a permanent delete plus a quick way to set a fresh one. --}}
                                        <a href="{{ url('schedules/delete/'.$archived->id) }}" class="ui button small red" onclick="return confirm('{{ __('This will permanently delete this archived schedule. This cannot be undone. Continue?') }}');">
                                            <i class="ui trash icon"></i>{{ __('Delete') }}
                                        </a>
                                        <a href="{{ url('schedules/new/'.$emp->id) }}" class="ui button small green">
                                            <i class="ui plus icon"></i>{{ __('New') }}
                                        </a>
                                    @else
                                        <a href="{{ url('schedules/new/'.$emp->id) }}" class="ui button small green">
                                            <i class="ui plus icon"></i>{{ __('Set Schedule') }}
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="rota-empty-state">
                                    <div class="icon"><i class="ui users icon"></i></div>
                                    <div class="empty-text">{{ __('No employees found for this company.') }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
@endsection