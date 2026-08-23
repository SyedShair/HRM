@extends('layouts.default')
@php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
@endphp
@section('meta')
    <title>Email Center | {{ $appName }}</title>
    <meta name="description" content="Send passport, visa, share code, and general HR emails to employees">
@endsection

@section('styles')
<style>
    :root {
        --ec-green: #16a34a;
        --ec-green-dark: #15803d;
        --ec-green-soft: #ecfdf5;
        --ec-border: #e5e7eb;
        --ec-text: #111827;
        --ec-muted: #6b7280;
    }

    .ec-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 22px;
    }
    .ec-header h2.page-title {
        font-weight: 800;
        color: var(--ec-text);
        margin-bottom: 3px;
        letter-spacing: -0.01em;
    }
    .ec-header .page-subtitle {
        font-size: 13.5px;
        color: var(--ec-muted);
        margin: 0;
    }

    .box.box-success {
        border-radius: 14px;
        border: 1px solid var(--ec-border);
        border-top: none;
        box-shadow: 0 1px 2px rgba(16,24,40,0.04), 0 1px 6px rgba(16,24,40,0.03);
        overflow: hidden;
    }
    .box-header.with-border {
        font-size: 14.5px;
        font-weight: 700;
        padding: 16px 20px;
        border-bottom: 1px solid var(--ec-border);
        background: linear-gradient(180deg, #fafbfc, #f7f8f9);
        color: var(--ec-text);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .box-body { padding: 20px; }

    /* ================= COMPANY FILTER ================= */
    .company-filter-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        border: 1px solid var(--ec-border);
        border-radius: 10px;
        padding: 8px 14px;
        box-shadow: 0 1px 2px rgba(16,24,40,0.04);
    }
    .company-filter-wrap i.icon {
        color: var(--ec-green);
        font-size: 15px;
        margin: 0;
    }
    .company-filter-wrap select {
        min-width: 200px;
        border: none;
        outline: none;
        font-size: 13px;
        font-weight: 600;
        color: var(--ec-text);
        background: transparent;
        cursor: pointer;
    }

    /* ================= TABS ================= */
    .email-tabs {
        display: flex;
        gap: 6px;
        background: #f3f4f6;
        border-radius: 12px;
        padding: 5px;
        margin-bottom: 22px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .email-tabs::-webkit-scrollbar { display: none; }

    .email-tab {
        flex: 1 1 auto;
        white-space: nowrap;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 700;
        color: var(--ec-muted);
        cursor: pointer;
        border-radius: 9px;
        transition: all .18s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }
    .email-tab:hover { color: var(--ec-green-dark); background: rgba(22,163,74,0.06); }
    .email-tab.active {
        color: #fff;
        background: var(--ec-green);
        box-shadow: 0 2px 6px rgba(22,163,74,0.35);
    }
    .email-tab .count-pill {
        background: rgba(0,0,0,0.08);
        color: inherit;
        font-size: 11px;
        font-weight: 800;
        padding: 1px 8px;
        border-radius: 999px;
        min-width: 20px;
        text-align: center;
    }
    .email-tab.active .count-pill { background: rgba(255,255,255,0.25); }

    .email-panel { display: none; animation: ecFadeIn .25s ease; }
    .email-panel.active { display: block; }
    @keyframes ecFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ================= TABLE ================= */
    .table-responsive-wrap { overflow-x: auto; }
    table.email-table { width: 100%; border-collapse: collapse; }
    table.email-table thead th {
        text-align: left;
        font-size: 10.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--ec-muted);
        padding: 11px 12px;
        border-bottom: 2px solid var(--ec-border);
        white-space: nowrap;
    }
    table.email-table tbody td {
        padding: 13px 12px;
        border-bottom: 1px solid #f0f1f3;
        vertical-align: middle;
        font-size: 13px;
        color: var(--ec-text);
    }
    table.email-table tbody tr { transition: background-color .12s ease; }
    table.email-table tbody tr:hover { background-color: #f9fafb; }
    table.email-table tbody tr:last-child td { border-bottom: none; }

    .emp-avatar-fallback {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: var(--ec-green-soft);
        color: var(--ec-green-dark);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 12px;
        margin-right: 10px;
        flex-shrink: 0;
    }
    .emp-cell { display: flex; align-items: center; }
    .emp-name { font-weight: 700; color: var(--ec-text); line-height: 1.3; }
    .emp-company { font-size: 12px; color: var(--ec-muted); }

    .badge {
        display: inline-block;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: .02em;
        color: #fff;
        white-space: nowrap;
    }
    .bg-success { background: var(--ec-green); }
    .bg-warning { background: #d97706; }
    .bg-danger  { background: #dc2626; }

    .last-sent-hint {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .cell-muted { color: #9ca3af; }

    .btn-send {
        border-radius: 8px !important;
        font-weight: 600 !important;
        transition: transform .12s ease, box-shadow .12s ease, background-color .2s ease, color .2s ease !important;
    }
    .btn-send:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(16,24,40,0.12);
    }
    .btn-send:disabled,
    .btn-send.loading-state {
        opacity: .85;
        cursor: default;
        transform: none !important;
        box-shadow: none !important;
    }
    .btn-send.just-sent {
        background: var(--ec-green-soft) !important;
        color: var(--ec-green-dark) !important;
        border-color: var(--ec-green) !important;
    }

    .empty-state {
        text-align: center;
        padding: 46px 20px;
        color: var(--ec-muted);
    }
    .empty-state i.icon {
        font-size: 34px;
        color: #d1d5db;
        display: block;
        margin-bottom: 10px;
    }

    /* ================= COMPOSE ================= */
    .compose-grid {
        display: grid;
        grid-template-columns: 1fr 1.4fr;
        gap: 22px;
    }
    .compose-employee-list {
        max-height: 380px;
        overflow-y: auto;
        border: 1px solid var(--ec-border);
        border-radius: 10px;
        padding: 6px 14px;
        background: #fcfcfd;
    }
    .compose-employee-list label {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 0;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        border-bottom: 1px solid #f3f4f6;
    }
    .compose-employee-list label:last-child { border-bottom: none; }
    .compose-employee-list input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--ec-green);
    }
    .select-all-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-size: 12px;
        padding: 4px 2px;
    }
    .select-all-row a {
        color: var(--ec-green-dark);
        font-weight: 700;
        text-decoration: none;
    }
    .select-all-row a:hover { text-decoration: underline; }

    #composeMessage {
        min-height: 220px;
        resize: vertical;
        border-radius: 8px;
    }

    .compose-side-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--ec-text);
        margin-bottom: 8px;
        display: block;
    }

    .field.error-field input,
    .field.error-field textarea {
        border-color: #dc2626 !important;
        background-color: #fef2f2 !important;
    }

    /* ================= SEND CONFIRMATION MODAL ================= */
    #send-confirm-modal .content {
        text-align: center;
        padding: 34px 30px 20px !important;
    }
    #send-confirm-modal .confirm-icon-wrap {
        width: 62px;
        height: 62px;
        border-radius: 50%;
        background: var(--ec-green-soft);
        color: var(--ec-green);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        margin: 0 auto 18px;
    }
    #send-confirm-modal .confirm-title {
        font-size: 17px;
        font-weight: 800;
        color: var(--ec-text);
        margin-bottom: 8px;
    }
    #send-confirm-modal .confirm-message {
        font-size: 13.5px;
        color: var(--ec-muted);
        line-height: 1.6;
    }
    #send-confirm-modal .confirm-message strong {
        color: var(--ec-text);
    }
    #send-confirm-modal .actions {
        display: flex;
        gap: 10px;
        padding: 18px 30px 26px !important;
        border-top: none !important;
        background: transparent !important;
    }
    #send-confirm-modal .actions .button {
        flex: 1;
        margin: 0 !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        padding: 12px !important;
    }
    #send-confirm-modal .actions .cancel-btn {
        background: #f3f4f6 !important;
        color: var(--ec-text) !important;
    }
    #send-confirm-modal .actions .confirm-btn {
        background: var(--ec-green) !important;
        color: #fff !important;
    }
    #send-confirm-modal .actions .confirm-btn:hover {
        background: var(--ec-green-dark) !important;
    }
    #send-confirm-modal .actions .confirm-btn:disabled {
        opacity: .75;
        cursor: default;
    }

    /* ================= TOASTS ================= */
    .ec-toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 100000;
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 340px;
        max-width: calc(100vw - 32px);
        pointer-events: none;
    }
    .ec-toast {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        background: #ffffff;
        border-radius: 10px;
        padding: 14px 34px 16px 14px;
        box-shadow: 0 10px 28px rgba(16,24,40,0.14), 0 2px 6px rgba(16,24,40,0.08);
        border-left: 4px solid var(--ec-green);
        opacity: 0;
        transform: translateX(24px);
        transition: opacity .25s ease, transform .25s ease;
        overflow: hidden;
        pointer-events: auto;
    }
    .ec-toast.show { opacity: 1; transform: translateX(0); }
    .ec-toast.hide { opacity: 0; transform: translateX(24px); }

    .ec-toast-success { border-left-color: var(--ec-green); }
    .ec-toast-error   { border-left-color: #dc2626; }
    .ec-toast-warning { border-left-color: #d97706; }
    .ec-toast-info    { border-left-color: #2563eb; }

    .ec-toast-icon { font-size: 18px; margin-top: 1px; line-height: 1; }
    .ec-toast-success .ec-toast-icon { color: var(--ec-green); }
    .ec-toast-error   .ec-toast-icon { color: #dc2626; }
    .ec-toast-warning .ec-toast-icon { color: #d97706; }
    .ec-toast-info    .ec-toast-icon { color: #2563eb; }

    .ec-toast-body { font-size: 13px; color: var(--ec-text); line-height: 1.5; flex: 1; padding-top: 1px; }
    .ec-toast-close {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
        color: #b7bcc4;
        font-size: 10px;
    }
    .ec-toast-close:hover { color: var(--ec-text); }

    .ec-toast-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        width: 100%;
        background: rgba(0,0,0,0.07);
        transition: width linear;
    }
    .ec-toast-success .ec-toast-bar { background: var(--ec-green); }
    .ec-toast-error   .ec-toast-bar { background: #dc2626; }
    .ec-toast-warning .ec-toast-bar { background: #d97706; }
    .ec-toast-info    .ec-toast-bar { background: #2563eb; }

    @media (max-width: 480px) {
        .ec-toast-container { right: 10px; left: 10px; width: auto; top: 10px; }
    }

    /* ================= LOADER OVERLAY ================= */
    .ec-loader-overlay {
        position: fixed;
        inset: 0;
        background: rgba(17,24,39,0.4);
        -webkit-backdrop-filter: blur(2px);
        backdrop-filter: blur(2px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        opacity: 0;
        visibility: hidden;
        transition: opacity .18s ease;
    }
    .ec-loader-overlay.show { opacity: 1; visibility: visible; }

    .ec-loader-box {
        background: #ffffff;
        border-radius: 16px;
        padding: 32px 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        box-shadow: 0 24px 60px rgba(16,24,40,0.28);
        min-width: 230px;
        transform: scale(.92);
        transition: transform .2s ease;
    }
    .ec-loader-overlay.show .ec-loader-box { transform: scale(1); }

    .ec-spinner {
        width: 46px;
        height: 46px;
        border-radius: 50%;
        border: 4px solid var(--ec-border);
        border-top-color: var(--ec-green);
        animation: ecSpin .75s linear infinite;
        position: relative;
    }
    .ec-spinner::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 4px solid transparent;
        border-top-color: rgba(22,163,74,0.35);
        animation: ecSpin 1.5s linear infinite reverse;
    }
    @keyframes ecSpin { to { transform: rotate(360deg); } }

    .ec-loader-text {
        font-size: 13px;
        font-weight: 700;
        color: var(--ec-text);
        text-align: center;
    }
    .ec-loader-subtext {
        font-size: 11.5px;
        color: var(--ec-muted);
        text-align: center;
        margin-top: -8px;
    }

    /* ================= RESPONSIVE ================= */
    @media (max-width: 991px) {
        .compose-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 767px) {
        .ec-header { flex-direction: column; }
        .company-filter-wrap { width: 100%; }
        .company-filter-wrap select { width: 100%; }

        table.email-table thead { display: none; }
        table.email-table, table.email-table tbody, table.email-table tr, table.email-table td {
            display: block;
            width: 100%;
        }
        table.email-table tr {
            margin-bottom: 12px;
            border: 1px solid var(--ec-border);
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 1px 3px rgba(16,24,40,0.04);
        }
        table.email-table td { border: none; padding: 7px 0; }
        table.email-table td[data-label]::before {
            content: attr(data-label);
            display: block;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        table.email-table td.text-right { text-align: left; }
        .btn-send { width: 100%; justify-content: center; }
    }

    @media (max-width: 480px) {
        .email-tab { padding: 9px 10px; font-size: 12px; }
        .email-tab .count-pill { display: none; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    <div class="ec-header">
        <div>
            <h2 class="page-title">{{ __('Email Center') }}</h2>
            <p class="page-subtitle">{{ __('Send passport, visa, share code, and general HR emails to employees') }}</p>
        </div>

        <div class="company-filter-wrap">
            <i class="ui building icon"></i>
            <select onchange="window.location.href='{{ url('emails') }}?company_id='+this.value">
                <option value="">{{ __('All Companies') }}</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" @selected($company->id == $companyId)>{{ $company->company }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if(session('success'))
        <div class="ui positive message"><i class="close icon"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="ui negative message"><i class="close icon"></i>{{ session('error') }}</div>
    @endif

    <div class="email-tabs">
        <div class="email-tab active" data-tab="passport">
            <i class="ui id card outline icon"></i>{{ __('Passport') }}
            <span class="count-pill">{{ $passportList->count() }}</span>
        </div>
        <div class="email-tab" data-tab="visa">
            <i class="ui globe icon"></i>{{ __('Visa') }}
            <span class="count-pill">{{ $visaList->count() }}</span>
        </div>
        <div class="email-tab" data-tab="sharecode">
            <i class="ui qrcode icon"></i>{{ __('Share Code') }}
            <span class="count-pill">{{ $shareCodeList->count() }}</span>
        </div>
        <div class="email-tab" data-tab="compose">
            <i class="ui pencil icon"></i>{{ __('Compose') }}
        </div>
    </div>

    {{-- ================= PASSPORT PANEL ================= --}}
    <div class="email-panel active" id="panel-passport">
        <div class="box box-success">
            <div class="box-header with-border"><i class="ui id card outline icon"></i>{{ __('Passport Expiry') }}</div>
            <div class="box-body">
                <div class="table-responsive-wrap">
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Passport No') }}</th>
                            <th>{{ __('Expiry Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($passportList as $person)
                            @php
                                $lsPassport = isset($lastSent[$person->id]) ? $lastSent[$person->id]->firstWhere('type', 'passport_expiry') : null;
                                $hintId = 'hint-passport-'.$person->id;
                            @endphp
                            <tr>
                                <td data-label="{{ __('Employee') }}">
                                    <div class="emp-cell">
                                        <span class="emp-avatar-fallback">{{ mb_substr($person->firstname,0,1) }}{{ mb_substr($person->lastname,0,1) }}</span>
                                        <div>
                                            <div class="emp-name">{{ $person->lastname }}, {{ $person->firstname }}</div>
                                            <div class="emp-company">{{ $person->company }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="{{ __('Passport No') }}">{{ $person->nationalid ?: '—' }}</td>
                                <td data-label="{{ __('Expiry Date') }}">{{ $person->expiryInfo['expiryDate'] }}</td>
                                <td data-label="{{ __('Status') }}"><span class="badge {{ $person->expiryInfo['class'] }}">{{ $person->expiryInfo['text'] }}</span></td>
                                <td data-label="{{ __('Actions') }}" class="text-right">
                                    @if($person->emailaddress)
                                        <form action="{{ route('emails.passport', $person->id) }}" method="post" class="send-reminder-form" style="display:inline;">
                                            @csrf
                                        </form>
                                        <button type="button" class="ui button small basic btn-send send-confirm-trigger"
                                                data-employee="{{ $person->lastname }}, {{ $person->firstname }}"
                                                data-doc-type="{{ __('passport') }}"
                                                data-hint-target="{{ $hintId }}"
                                                onclick="openSendConfirm(this)">
                                            <i class="ui send icon"></i>{{ __('Send Now') }}
                                        </button>
                                        <div class="last-sent-hint" id="{{ $hintId }}" style="{{ $lsPassport ? '' : 'display:none;' }}">
                                            <i class="ui check circle outline icon"></i>{{ __('Last sent') }}: <span class="hint-time">{{ $lsPassport ? \Carbon\Carbon::parse($lsPassport->last_sent_at)->diffForHumans() : '' }}</span>
                                        </div>
                                    @else
                                        <span class="cell-muted">{{ __('No email on file') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">
                                <div class="empty-state">
                                    <i class="ui id card outline icon"></i>
                                    {{ __('No employees with a passport expiry date on file.') }}
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= VISA PANEL ================= --}}
    <div class="email-panel" id="panel-visa">
        <div class="box box-success">
            <div class="box-header with-border"><i class="ui globe icon"></i>{{ __('Visa Expiry') }}</div>
            <div class="box-body">
                <div class="table-responsive-wrap">
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Expiry Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visaList as $person)
                            @php
                                $lsVisa = isset($lastSent[$person->id]) ? $lastSent[$person->id]->firstWhere('type', 'visa_expiry') : null;
                                $hintId = 'hint-visa-'.$person->id;
                            @endphp
                            <tr>
                                <td data-label="{{ __('Employee') }}">
                                    <div class="emp-cell">
                                        <span class="emp-avatar-fallback">{{ mb_substr($person->firstname,0,1) }}{{ mb_substr($person->lastname,0,1) }}</span>
                                        <div>
                                            <div class="emp-name">{{ $person->lastname }}, {{ $person->firstname }}</div>
                                            <div class="emp-company">{{ $person->company }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="{{ __('Expiry Date') }}">{{ $person->expiryInfo['expiryDate'] }}</td>
                                <td data-label="{{ __('Status') }}"><span class="badge {{ $person->expiryInfo['class'] }}">{{ $person->expiryInfo['text'] }}</span></td>
                                <td data-label="{{ __('Actions') }}" class="text-right">
                                    @if($person->emailaddress)
                                        <form action="{{ route('emails.visa', $person->id) }}" method="post" class="send-reminder-form" style="display:inline;">
                                            @csrf
                                        </form>
                                        <button type="button" class="ui button small basic btn-send send-confirm-trigger"
                                                data-employee="{{ $person->lastname }}, {{ $person->firstname }}"
                                                data-doc-type="{{ __('visa') }}"
                                                data-hint-target="{{ $hintId }}"
                                                onclick="openSendConfirm(this)">
                                            <i class="ui send icon"></i>{{ __('Send Now') }}
                                        </button>
                                        <div class="last-sent-hint" id="{{ $hintId }}" style="{{ $lsVisa ? '' : 'display:none;' }}">
                                            <i class="ui check circle outline icon"></i>{{ __('Last sent') }}: <span class="hint-time">{{ $lsVisa ? \Carbon\Carbon::parse($lsVisa->last_sent_at)->diffForHumans() : '' }}</span>
                                        </div>
                                    @else
                                        <span class="cell-muted">{{ __('No email on file') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">
                                <div class="empty-state">
                                    <i class="ui globe icon"></i>
                                    {{ __('No employees with a visa expiry date on file.') }}
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= SHARE CODE PANEL ================= --}}
    <div class="email-panel" id="panel-sharecode">
        <div class="box box-success">
            <div class="box-header with-border"><i class="ui qrcode icon"></i>{{ __('Share Code Expiry') }}</div>
            <div class="box-body">
                <div class="table-responsive-wrap">
                <table class="email-table">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Share Code') }}</th>
                            <th>{{ __('Expiry Date') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shareCodeList as $person)
                            @php
                                $lsShare = isset($lastSent[$person->id]) ? $lastSent[$person->id]->firstWhere('type', 'sharecode_expiry') : null;
                                $hintId = 'hint-sharecode-'.$person->id;
                            @endphp
                            <tr>
                                <td data-label="{{ __('Employee') }}">
                                    <div class="emp-cell">
                                        <span class="emp-avatar-fallback">{{ mb_substr($person->firstname,0,1) }}{{ mb_substr($person->lastname,0,1) }}</span>
                                        <div>
                                            <div class="emp-name">{{ $person->lastname }}, {{ $person->firstname }}</div>
                                            <div class="emp-company">{{ $person->company }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="{{ __('Share Code') }}">{{ $person->sharecode ?: '—' }}</td>
                                <td data-label="{{ __('Expiry Date') }}">{{ $person->expiryInfo['expiryDate'] }}</td>
                                <td data-label="{{ __('Status') }}"><span class="badge {{ $person->expiryInfo['class'] }}">{{ $person->expiryInfo['text'] }}</span></td>
                                <td data-label="{{ __('Actions') }}" class="text-right">
                                    @if($person->emailaddress)
                                        <form action="{{ route('emails.sharecode', $person->id) }}" method="post" class="send-reminder-form" style="display:inline;">
                                            @csrf
                                        </form>
                                        <button type="button" class="ui button small basic btn-send send-confirm-trigger"
                                                data-employee="{{ $person->lastname }}, {{ $person->firstname }}"
                                                data-doc-type="{{ __('share code') }}"
                                                data-hint-target="{{ $hintId }}"
                                                onclick="openSendConfirm(this)">
                                            <i class="ui send icon"></i>{{ __('Send Now') }}
                                        </button>
                                        <div class="last-sent-hint" id="{{ $hintId }}" style="{{ $lsShare ? '' : 'display:none;' }}">
                                            <i class="ui check circle outline icon"></i>{{ __('Last sent') }}: <span class="hint-time">{{ $lsShare ? \Carbon\Carbon::parse($lsShare->last_sent_at)->diffForHumans() : '' }}</span>
                                        </div>
                                    @else
                                        <span class="cell-muted">{{ __('No email on file') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">
                                <div class="empty-state">
                                    <i class="ui qrcode icon"></i>
                                    {{ __('No employees with a share code expiry date on file.') }}
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= COMPOSE PANEL ================= --}}
    <div class="email-panel" id="panel-compose">
        <div class="box box-success">
            <div class="box-header with-border"><i class="ui pencil icon"></i>{{ __('Compose Email') }}</div>
            <div class="box-body">
                <form action="{{ route('emails.custom') }}" method="post" class="ui form" id="composeForm">
                    @csrf

                    <div class="compose-grid">
                        <div>
                            <label class="compose-side-label">{{ __('Recipients') }}</label>
                            <div class="select-all-row">
                                <span class="cell-muted">{{ __('Select one or more employees') }}</span>
                                <span>
                                    <a href="#" onclick="toggleAllRecipients(true); return false;">{{ __('Select all') }}</a>
                                    &middot;
                                    <a href="#" onclick="toggleAllRecipients(false); return false;">{{ __('Clear') }}</a>
                                </span>
                            </div>
                            <div class="compose-employee-list">
                                @forelse($people as $person)
                                    <label>
                                        <input type="checkbox" name="employee_ids[]" value="{{ $person->id }}" class="recipient-checkbox" {{ !$person->emailaddress ? 'disabled' : '' }}>
                                        {{ $person->lastname }}, {{ $person->firstname }}
                                        <span class="emp-company">&middot; {{ $person->company }}</span>
                                        @if(!$person->emailaddress)
                                            <span class="cell-muted">({{ __('no email') }})</span>
                                        @endif
                                    </label>
                                @empty
                                    <p class="cell-muted">{{ __('No employees found for this company.') }}</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <label class="compose-side-label">{{ __('Message') }}</label>
                            <div class="field">
                                <label>{{ __('Subject') }}</label>
                                <input type="text" name="subject" value="{{ old('subject') }}" placeholder="{{ __('e.g. Important Update from HR') }}">
                            </div>

                            <div class="field">
                                <label>{{ __('Body') }}</label>
                                <textarea id="composeMessage" name="message" placeholder="{{ __('Write your message here...') }}">{{ old('message') }}</textarea>
                            </div>

                            @if ($errors->any())
                                <div class="ui error message">
                                    <ul class="list">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="field" style="margin-top:16px;">
                                <button type="submit" class="ui green button small btn-send"><i class="ui send icon"></i>{{ __('Send Email') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- ================= SEND CONFIRMATION MODAL ================= --}}
<div class="ui small modal" id="send-confirm-modal">
    <div class="content">
        <div class="confirm-icon-wrap"><i class="ui paper plane icon"></i></div>
        <div class="confirm-title">{{ __('Send Reminder Email?') }}</div>
        <div class="confirm-message">
            {{ __('Send a') }} <strong id="send-confirm-doctype"></strong> {{ __('expiry reminder to') }} <strong id="send-confirm-employee"></strong>{{ __(' now?') }}
        </div>
    </div>
    <div class="actions">
        <button type="button" class="ui button cancel-btn" onclick="$('#send-confirm-modal').modal('hide')">{{ __('Cancel') }}</button>
        <button type="button" class="ui button confirm-btn" id="send-confirm-ok-btn">
            <i class="ui send icon"></i>{{ __('Yes, Send') }}
        </button>
    </div>
</div>

{{-- ================= TOASTS & PRELOADER ================= --}}
<div id="ec-toast-container" class="ec-toast-container" aria-live="polite"></div>

<div id="ec-loader-overlay" class="ec-loader-overlay">
    <div class="ec-loader-box">
        <div class="ec-spinner"></div>
        <div class="ec-loader-text" id="ec-loader-text">{{ __('Please wait...') }}</div>
        <div class="ec-loader-subtext" id="ec-loader-subtext"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
/* =====================================================================
   TABS
===================================================================== */
document.querySelectorAll('.email-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.email-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.email-panel').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('panel-' + this.dataset.tab).classList.add('active');
    });
});

/* =====================================================================
   COMPOSE: select all / clear recipients
===================================================================== */
function toggleAllRecipients(checked) {
    document.querySelectorAll('.recipient-checkbox:not(:disabled)').forEach(function (cb) {
        cb.checked = checked;
    });
}

/* =====================================================================
   TOASTS
===================================================================== */
let ecToastCounter = 0;
const EC_TOAST_ICONS = {
    success: 'check circle',
    error: 'times circle',
    warning: 'exclamation triangle',
    info: 'info circle',
};

function showToast(type, message, timeout = 5500) {
    const container = document.getElementById('ec-toast-container');
    if (!container || !message) return;

    const id = 'ec-toast-' + (++ecToastCounter);
    const icon = EC_TOAST_ICONS[type] || EC_TOAST_ICONS.info;

    const toast = document.createElement('div');
    toast.className = 'ec-toast ec-toast-' + type;
    toast.id = id;
    toast.innerHTML =
        '<div class="ec-toast-icon"><i class="ui ' + icon + ' icon"></i></div>' +
        '<div class="ec-toast-body">' + message + '</div>' +
        '<div class="ec-toast-close" onclick="dismissToast(\'' + id + '\')"><i class="ui close icon"></i></div>' +
        '<div class="ec-toast-bar"></div>';

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('show');
        const bar = toast.querySelector('.ec-toast-bar');
        bar.style.transitionDuration = timeout + 'ms';
        requestAnimationFrame(() => { bar.style.width = '0%'; });
    });

    const timer = setTimeout(() => dismissToast(id), timeout);
    toast.dataset.timer = timer;

    return id;
}

function dismissToast(id) {
    const toast = document.getElementById(id);
    if (!toast) return;
    clearTimeout(toast.dataset.timer);
    toast.classList.remove('show');
    toast.classList.add('hide');
    setTimeout(() => toast.remove(), 250);
}

/* =====================================================================
   PRELOADER OVERLAY
===================================================================== */
function showLoader(text, subtext) {
    const overlay = document.getElementById('ec-loader-overlay');
    document.getElementById('ec-loader-text').textContent = text || '{{ __('Please wait...') }}';
    document.getElementById('ec-loader-subtext').textContent = subtext || '';
    overlay.classList.add('show');
}
function hideLoader() {
    document.getElementById('ec-loader-overlay').classList.remove('show');
}

/* =====================================================================
   QUICK SEND (passport / visa / share code) - confirm modal + AJAX
===================================================================== */
let pendingSendForm = null;
let pendingSendButton = null;

function openSendConfirm(button) {
    // The trigger button sits right after its matching hidden form in
    // the DOM (one form + one button per row), so this always targets
    // the correct employee's form regardless of row/tab.
    pendingSendForm = button.previousElementSibling;
    pendingSendButton = button;

    document.getElementById('send-confirm-employee').textContent = button.dataset.employee;
    document.getElementById('send-confirm-doctype').textContent = button.dataset.docType;

    $('#send-confirm-modal').modal({ closable: true }).modal('show');
}

document.getElementById('send-confirm-ok-btn').addEventListener('click', async function () {
    if (!pendingSendForm || !pendingSendButton) return;

    const okBtn = this;
    const originalOkHtml = okBtn.innerHTML;
    const employee = document.getElementById('send-confirm-employee').textContent;

    okBtn.disabled = true;
    okBtn.innerHTML = '<i class="ui notched circle loading icon"></i> {{ __('Sending...') }}';

    const button = pendingSendButton;
    const form = pendingSendForm;

    button.disabled = true;
    button.classList.add('loading-state');
    const originalBtnHtml = button.innerHTML;
    button.innerHTML = '<i class="ui notched circle loading icon"></i> {{ __('Sending') }}';

    $('#send-confirm-modal').modal('hide');
    showLoader('{{ __('Sending reminder email...') }}', employee);

    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new FormData(form),
        });
        const data = await res.json();

        showToast(data.success ? 'success' : 'error', data.message);

        if (data.success) {
            markSentButton(button);
        } else {
            button.disabled = false;
            button.classList.remove('loading-state');
            button.innerHTML = originalBtnHtml;
        }
    } catch (err) {
        showToast('error', '{{ __('Something went wrong while sending the email. Please try again.') }}');
        button.disabled = false;
        button.classList.remove('loading-state');
        button.innerHTML = originalBtnHtml;
    } finally {
        hideLoader();
        okBtn.disabled = false;
        okBtn.innerHTML = originalOkHtml;
        pendingSendForm = null;
        pendingSendButton = null;
    }
});

function markSentButton(button) {
    const hintId = button.dataset.hintTarget;
    if (hintId) {
        const hint = document.getElementById(hintId);
        if (hint) {
            hint.style.display = 'flex';
            const timeEl = hint.querySelector('.hint-time');
            if (timeEl) timeEl.textContent = '{{ __('just now') }}';
        }
    }

    button.innerHTML = '<i class="ui check icon"></i> {{ __('Sent') }}';
    button.classList.remove('loading-state');
    button.classList.add('just-sent');

    setTimeout(() => {
        button.disabled = false;
        button.classList.remove('just-sent');
        button.innerHTML = '<i class="ui send icon"></i>{{ __('Send Now') }}';
    }, 2500);
}

/* =====================================================================
   COMPOSE - AJAX submit
===================================================================== */
const composeForm = document.getElementById('composeForm');
if (composeForm) {
    composeForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const checked = composeForm.querySelectorAll('.recipient-checkbox:checked');
        const subjectField = composeForm.querySelector('[name="subject"]');
        const messageField = composeForm.querySelector('[name="message"]');

        composeForm.querySelectorAll('.field.error-field').forEach(f => f.classList.remove('error-field'));

        if (checked.length === 0) {
            showToast('warning', '{{ __('Please select at least one recipient.') }}');
            return;
        }
        if (!subjectField.value.trim()) {
            subjectField.closest('.field').classList.add('error-field');
            showToast('warning', '{{ __('Please enter a subject.') }}');
            return;
        }
        if (!messageField.value.trim()) {
            messageField.closest('.field').classList.add('error-field');
            showToast('warning', '{{ __('Please write a message.') }}');
            return;
        }

        const submitBtn = composeForm.querySelector('button[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ui notched circle loading icon"></i> {{ __('Sending...') }}';

        const recipientLabel = checked.length + ' ' + (checked.length === 1 ? '{{ __('employee') }}' : '{{ __('employees') }}');
        showLoader('{{ __('Sending email...') }}', '{{ __('to') }} ' + recipientLabel);

        try {
            const res = await fetch(composeForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(composeForm),
            });
            const data = await res.json();

            if (res.status === 422 && data.errors) {
                const messages = Object.values(data.errors).flat();
                showToast('error', messages.join(' '));
            } else {
                showToast(data.success ? 'success' : 'error', data.message);
                if (data.success) {
                    composeForm.reset();
                }
            }
        } catch (err) {
            showToast('error', '{{ __('Something went wrong while sending the email. Please try again.') }}');
        } finally {
            hideLoader();
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    });
}
</script>
@endsection