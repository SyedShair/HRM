@extends('layouts.default')

@section('meta')
    <title>Payslip | {{ $payroll->employee }}</title>
@endsection

@section('styles')
<style>
    :root {
        --ps-navy: #1E2F4F;
        --ps-navy-light: #2C4370;
        --ps-border: #A9B4C7;
        --ps-paper: #FFFFFF;
        --ps-ink: #1B1F27;
        --ps-ink-soft: #5B6478;
    }

    .ps-wrap { padding: 24px 16px; display: flex; justify-content: center; }

    .ps-card {
        width: 100%; max-width: 880px;
        background: var(--ps-paper);
        border: 1px solid var(--ps-border);
        border-radius: 4px;
        overflow: hidden;
        color: var(--ps-ink);
        font-size: 13px;
    }

    .ps-grid { display: grid; border-bottom: 1px solid var(--ps-border); }
    .ps-grid.cols-3 { grid-template-columns: 2fr 2fr 1fr; }
    .ps-grid.cols-5 { grid-template-columns: 1.5fr 1.7fr 1fr 1fr 0.8fr; }
    .ps-grid.cols-4-body { grid-template-columns: 1fr 0.6fr 0.7fr 1.4fr 1.3fr; }

    .ps-cell { padding: 8px 12px; border-right: 1px solid var(--ps-border); }
    .ps-cell:last-child { border-right: none; }

    .ps-cell-label {
        background: var(--ps-navy);
        color: #fff;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 6px 12px;
        border-right: 1px solid var(--ps-navy-light);
    }
    .ps-cell-label:last-child { border-right: none; }

    .ps-value { font-weight: 600; }

    .ps-body-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; }
    .ps-panel { border-right: 1px solid var(--ps-border); }
    .ps-panel:last-child { border-right: none; }

    .ps-panel-head {
        background: var(--ps-navy);
        color: #fff;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 6px 12px;
    }

    .ps-line {
        display: flex; justify-content: space-between; gap: 8px;
        padding: 5px 12px; font-size: 12px; border-bottom: 1px solid #EEF0F4;
    }
    .ps-line span:first-child { color: var(--ps-ink-soft); }
    .ps-line span:last-child { text-align: right; font-weight: 600; }
    .ps-line.zero span:last-child { color: var(--ps-ink-soft); font-weight: 400; }

    .ps-summary-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; border-top: 1px solid var(--ps-border); }
    .ps-summary-cell { border-right: 1px solid var(--ps-border); }
    .ps-summary-cell:last-child { border-right: none; }

    .ps-summary-head {
        background: var(--ps-navy);
        color: #fff;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 6px 12px;
    }
    .ps-summary-body { padding: 10px 12px; }
    .ps-summary-body .ps-line { padding: 4px 0; border-bottom: none; }

    .ps-net-box {
        padding: 12px;
        border: 2px solid var(--ps-navy);
        margin: 12px;
        text-align: center;
    }
    .ps-net-box .ps-net-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ps-ink-soft); }
    .ps-net-box .ps-net-value { font-size: 22px; font-weight: 700; color: var(--ps-navy); margin-top: 4px; }

    .ps-status {
        display: inline-block; margin: 12px; padding: 5px 12px; border-radius: 20px;
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        background: rgba(30,47,79,0.08); color: var(--ps-navy);
    }

    .ps-actions { padding: 12px; display: flex; gap: 10px; flex-wrap: wrap; border-top: 1px solid var(--ps-border); }
    .ps-btn {
        border: 1px solid var(--ps-border); background: transparent; color: var(--ps-ink);
        padding: 9px 16px; border-radius: 6px; font-size: 12px; font-weight: 600;
        text-transform: uppercase; cursor: pointer; text-decoration: none; display: inline-block;
    }
    .ps-btn.primary { border-color: var(--ps-navy); background: var(--ps-navy); color: #fff; }

    .ps-note { font-size: 11px; color: var(--ps-ink-soft); padding: 0 12px 16px; line-height: 1.6; }

    .ps-key { font-size: 10px; color: var(--ps-ink-soft); padding: 8px 12px; border-top: 1px solid var(--ps-border); }

    @media (max-width: 720px) {
        .ps-grid.cols-3,
        .ps-grid.cols-5 { grid-template-columns: 1fr; }
        .ps-body-grid,
        .ps-summary-grid { grid-template-columns: 1fr 1fr; }
        .ps-cell, .ps-cell-label { border-right: none; border-bottom: 1px solid var(--ps-border); }
    }
</style>
@endsection

@section('content')
<div class="ps-wrap">
    <div class="ps-card">

        {{-- Row 1: Employer / Employee / Date --}}
        <div class="ps-grid cols-3">
            <div class="ps-cell-label">{{ __('Employer') }}</div>
            <div class="ps-cell-label">{{ __('Employee Name') }}</div>
            <div class="ps-cell-label">{{ __('Date') }}</div>
        </div>
       @php
                    $app = \App\Classes\table::settings()->value('app_name');
@endphp
        <div class="ps-grid cols-3">
            <div class="ps-cell ps-value">{{ $app }}</div>
            <div class="ps-cell ps-value">{{ $payroll->employee }}</div>
            <div class="ps-cell ps-value">{{ $payroll->generated_at ? \Carbon\Carbon::parse($payroll->generated_at)->format('d/m/Y') : \Carbon\Carbon::parse($payroll->period_end)->format('d/m/Y') }}</div>
        </div>

        {{-- Row 2: Department / NI number & table / Tax code / Payment method / Period --}}
        <div class="ps-grid cols-5">
            <div class="ps-cell-label">{{ __('Department') }}</div>
            <div class="ps-cell-label">{{ __('N.I. Number & Table') }}</div>
            <div class="ps-cell-label">{{ __('Tax Code') }}</div>
            <div class="ps-cell-label">{{ __('Payment Method') }}</div>
            <div class="ps-cell-label">{{ __('Period') }}</div>
        </div>
        <div class="ps-grid cols-5">
            <div class="ps-cell">{{ $payroll->department ?? '-' }}</div>
            <div class="ps-cell ps-value">{{ $payroll->ni_number ?? '-' }}</div>
            <div class="ps-cell ps-value">{{ $payroll->tax_code ?? '1257L' }}</div>
            <div class="ps-cell ps-value">{{ __('BACS') }}</div>
            <div class="ps-cell ps-value">{{ $payroll->period_label ?? '-' }}</div>
        </div>

        {{-- Body: YTD | Rate/Hours/Gross Pay | Deductions --}}
        <div class="ps-body-grid">
            <div class="ps-panel">
                <div class="ps-panel-head">{{ __('Year to Date') }}</div>
                <div class="ps-line"><span>{{ __('Total Pay') }}</span><span>£{{ number_format($payroll->ytd_gross ?? $payroll->gross_pay, 2) }}</span></div>
                <div class="ps-line"><span>{{ __('Taxable Pay') }}</span><span>£{{ number_format($payroll->ytd_taxable_pay ?? $payroll->taxable_pay, 2) }}</span></div>
                <div class="ps-line"><span>{{ __('Tax') }}</span><span>£{{ number_format($payroll->ytd_tax ?? $payroll->income_tax, 2) }}</span></div>
                <div class="ps-line"><span>{{ __('N.I. Employee') }}</span><span>£{{ number_format($payroll->ytd_employee_ni ?? $payroll->employee_ni, 2) }}</span></div>
                <div class="ps-line"><span>{{ __('N.I. Employer') }}</span><span>£{{ number_format($payroll->ytd_employer_ni ?? $payroll->employer_ni, 2) }}</span></div>
                <div class="ps-line"><span>{{ __("N.I.'able Pay") }}</span><span>£{{ number_format($payroll->ytd_niable_pay ?? $payroll->niable_pay, 2) }}</span></div>
                <div class="ps-line zero"><span>{{ __('SAP') }}</span><span>£0.00</span></div>
                <div class="ps-line zero"><span>{{ __('SPP') }}</span><span>£0.00</span></div>
                <div class="ps-line zero"><span>{{ __('SSP') }}</span><span>£0.00</span></div>
                <div class="ps-line zero"><span>{{ __('SMP') }}</span><span>£0.00</span></div>
                <div class="ps-line zero"><span>{{ __('SNCP') }}</span><span>£0.00</span></div>
                <div class="ps-line zero"><span>{{ __('SPBP') }}</span><span>£0.00</span></div>
                <div class="ps-line zero"><span>{{ __('Pension Employee') }}</span><span>£0.00</span></div>
                <div class="ps-line zero"><span>{{ __('Pension Employer') }}</span><span>£0.00</span></div>
            </div>

            <div class="ps-panel" style="grid-column: span 2;">
                <div class="ps-panel-head">
                    {{ __('Rate') }} / {{ __('Hours') }} / {{ __('Gross Pay') }}
                </div>
                <div class="ps-line">
                    <span>{{ $payroll->pay_type === 'hourly' ? __('Hourly Rate') : __('Basic Pay') }}</span>
                    <span>£{{ number_format($payroll->pay_type === 'hourly' ? $payroll->rate : $payroll->gross_pay, 2) }}</span>
                </div>
                <div class="ps-line"><span>{{ __('Hours') }}</span><span>{{ $payroll->pay_type === 'hourly' ? number_format($payroll->worked_hours, 2) : '--' }}</span></div>
                @if($payroll->overtime_pay > 0)
                    <div class="ps-line"><span>{{ __('Overtime') }}</span><span>£{{ number_format($payroll->overtime_pay, 2) }}</span></div>
                @endif
                @if($payroll->restday_pay > 0)
                    <div class="ps-line"><span>{{ __('Rest Day Pay') }}</span><span>£{{ number_format($payroll->restday_pay, 2) }}</span></div>
                @endif
                @if(($payroll->absence_deduction ?? 0) > 0)
                    <div class="ps-line"><span>{{ __('Absence Deduction') }}</span><span>-£{{ number_format($payroll->absence_deduction, 2) }}</span></div>
                @endif
                <div class="ps-line"><span><strong>{{ __('Gross Pay') }}</strong></span><span><strong>£{{ number_format($payroll->gross_pay, 2) }}</strong></span></div>

                @if($payroll->contracted_monthly_gross)
                    <div class="ps-line"><span>{{ __('Contracted Monthly Gross') }}</span><span>£{{ number_format($payroll->contracted_monthly_gross, 2) }}</span></div>
                @endif
            </div>

            <div class="ps-panel">
                <div class="ps-panel-head">{{ __('Deductions') }}</div>
                <div class="ps-line"><span>{{ __('PAYE Tax') }}</span><span>£{{ number_format($payroll->income_tax, 2) }}</span></div>
                <div class="ps-line"><span>{{ __('Employee NI') }}</span><span>£{{ number_format($payroll->employee_ni, 2) }}</span></div>
            </div>
        </div>

        {{-- Summary strip: Hours | Employers NI | Taxable Pay / Non-taxable Pay / Total Pay | Deductions / Net Pay --}}
        <div class="ps-summary-grid">
            <div class="ps-summary-cell">
                <div class="ps-summary-head">{{ __('Hours') }}</div>
                <div class="ps-summary-body">{{ $payroll->pay_type === 'hourly' ? number_format($payroll->worked_hours, 2) : '--' }}</div>
            </div>
            <div class="ps-summary-cell">
                <div class="ps-summary-head">{{ __("Employer's National Insurance") }}</div>
                <div class="ps-summary-body">£{{ number_format($payroll->employer_ni, 2) }}</div>
            </div>
            <div class="ps-summary-cell">
                <div class="ps-summary-head">{{ __('Taxable / Non-taxable / Total Pay') }}</div>
                <div class="ps-summary-body">
                    <div class="ps-line"><span>{{ __('Taxable Pay') }}</span><span>£{{ number_format($payroll->taxable_pay, 2) }}</span></div>
                    <div class="ps-line"><span>{{ __('Non Taxable Pay') }}</span><span>£{{ number_format($payroll->non_taxable_pay, 2) }}</span></div>
                    <div class="ps-line"><span>{{ __('Total Pay') }}</span><span>£{{ number_format($payroll->gross_pay, 2) }}</span></div>
                </div>
            </div>
            <div class="ps-summary-cell">
                <div class="ps-summary-head">{{ __('Deductions') }}</div>
                <div class="ps-summary-body">
                    £{{ number_format($payroll->total_deductions, 2) }}
                </div>
            </div>
        </div>

        <div class="ps-net-box">
            <div class="ps-net-label">{{ __('Net Pay') }}</div>
            <div class="ps-net-value">£{{ number_format($payroll->net_pay, 2) }}</div>
        </div>

        <span class="ps-status">{{ $payroll->status }}</span>

        <div class="ps-key">
            {{ __('KEY') }}: T = {{ __('TAXABLE') }} &nbsp; N = {{ __("N.I.'ABLE") }} &nbsp; B = {{ __('BOTH') }}
        </div>

        <div class="ps-actions">
            @if($payroll->status !== 'Paid')
                <form method="POST" action="{{ route('payroll.status', $payroll->id) }}">
                    @csrf
                    <input type="hidden" name="status" value="Paid">
                    <button type="submit" class="ps-btn primary">{{ __('Mark as Paid') }}</button>
                </form>
            @endif
            <a href="{{ route('payroll.index', ['month' => \Carbon\Carbon::parse($payroll->period_start)->month, 'year' => \Carbon\Carbon::parse($payroll->period_start)->year]) }}" class="ps-btn">
                {{ __('Back to Payroll') }}
            </a>
        </div>

       
    </div>
</div>
@endsection