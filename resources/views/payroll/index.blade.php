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
    <title>Payroll | {{ $appName }}</title>
@endsection

@section('styles')
<style>
    :root {
        --pr-ink: #161B24;
        --pr-panel: #1E2635;
        --pr-brass: #C9A54A;
        --pr-brass-light: #E4C878;
        --pr-paper: #F6F4EE;
        --pr-ink-soft: #8B93A3;
        --pr-ok: #3FA671;
        --pr-err: #D0483B;
    }

    .pr-wrap { padding: 24px 16px; }

    .pr-card {
        background: var(--pr-panel);
        border: 1px solid #313C50;
        border-radius: 14px;
        overflow: hidden;
        color: var(--pr-paper);
    }

    .pr-header {
        padding: 18px 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid var(--pr-brass);
        background: linear-gradient(180deg, #232C3D 0%, #1E2635 100%);
    }

    .pr-title {
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        font-size: 14px;
        color: #E9E5D8;
        margin: 0;
    }

    .pr-period-form { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin: 0; }
    .pr-period-form select {
        background: #10151D; border: 1px solid #2C3648; color: var(--pr-paper);
        border-radius: 8px; padding: 8px 10px; font-size: 13px;
    }

    .pr-btn {
        border: 1px solid var(--pr-brass-light);
        background: var(--pr-brass);
        color: #241C08;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-size: 12px;
        padding: 9px 16px;
        border-radius: 8px;
        cursor: pointer;
    }
    .pr-btn:hover { background: var(--pr-brass-light); }
    .pr-btn.secondary { background: transparent; color: var(--pr-ink-soft); border-color: #2C3648; }

    .pr-body { padding: 20px 24px; overflow-x: auto; }

    table.pr-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    table.pr-table th {
        text-align: left; padding: 10px 12px; color: var(--pr-brass-light);
        text-transform: uppercase; font-size: 11px; letter-spacing: 0.06em;
        border-bottom: 1px solid #2C3648; white-space: nowrap;
    }
    table.pr-table td { padding: 10px 12px; border-bottom: 1px solid #232C3D; white-space: nowrap; }
    table.pr-table tfoot td { font-weight: 700; border-top: 2px solid var(--pr-brass); border-bottom: none; }

    .pr-badge { padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
    .pr-badge.Pending  { background: rgba(201,165,74,0.15); color: var(--pr-brass-light); }
    .pr-badge.Approved { background: rgba(63,166,113,0.15); color: var(--pr-ok); }
    .pr-badge.Paid     { background: rgba(63,166,113,0.25); color: var(--pr-ok); }

    .pr-link { color: var(--pr-brass-light); text-decoration: none; font-weight: 600; }
    .pr-link:hover { text-decoration: underline; }

    .pr-flash { margin: 16px 24px 0; padding: 12px 16px; border-radius: 8px; font-size: 13px; }
    .pr-flash.ok { background: #E4F3EA; color: #1F5C3C; }
    .pr-flash.warn { background: #FBEAE7; color: #8C2E24; }

    .pr-empty { padding: 30px; text-align: center; color: var(--pr-ink-soft); }
    .pr-note { font-size: 11px; color: var(--pr-ink-soft); margin-top: 14px; line-height: 1.6; }

    @media (max-width: 640px) {
        .pr-header { flex-direction: column; align-items: stretch; }
        .pr-period-form, .pr-header form { width: 100%; }
        .pr-period-form select, .pr-btn { width: 100%; }
    }
</style>
@endsection

@section('content')
<div class="pr-wrap">
    <div class="pr-card">
        <div class="pr-header">
            <p class="pr-title">{{ __('Payroll') }} — {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}</p>

            <form class="pr-period-form" method="GET" action="{{ route('payroll.index') }}">
                <select name="month">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
                <select name="year">
                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="pr-btn secondary">{{ __('View') }}</button>
            </form>

            <form method="POST" action="{{ route('payroll.generate') }}"
                  onsubmit="return confirm('{{ __('Generate/refresh payroll for all active employees this period? This will overwrite any existing entries for this period.') }}');">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="pr-btn">{{ __('Generate Payroll') }}</button>
            </form>
        </div>

        @if(session('success'))
            <div class="pr-flash ok">{{ session('success') }}</div>
        @endif

        @if(session('skipped') && count(session('skipped')))
            <div class="pr-flash warn">{{ __('Skipped') }}: {{ implode(', ', session('skipped')) }}</div>
        @endif

        <div class="pr-body">
            @if($payrolls->isEmpty())
                <div class="pr-empty">{{ __('No payroll generated for this period yet. Pick a month and click Generate Payroll.') }}</div>
            @else
                <table class="pr-table">
                    <thead>
                        <tr>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Rate') }}</th>
                            <th>{{ __('Worked Hrs') }}</th>
                            <th>{{ __('Gross Pay') }}</th>
                            <th>{{ __('Tax') }}</th>
                            <th>{{ __('NI') }}</th>
                            <th>{{ __('Net Pay') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payrolls as $p)
                            <tr>
                                <td>{{ $p->employee }}</td>
                                <td>{{ $p->idno }}</td>
                                <td>{{ ucfirst($p->pay_type) }}</td>
                                <td>£{{ number_format($p->rate, 2) }}{{ $p->pay_type === 'hourly' ? '/hr' : '/mo' }}</td>
                                <td>{{ number_format($p->worked_hours, 2) }}</td>
                                <td>£{{ number_format($p->gross_pay, 2) }}</td>
                                <td>£{{ number_format($p->income_tax, 2) }}</td>
                                <td>£{{ number_format($p->employee_ni, 2) }}</td>
                                <td><strong>£{{ number_format($p->net_pay, 2) }}</strong></td>
                                <td><span class="pr-badge {{ $p->status }}">{{ $p->status }}</span></td>
                                <td><a class="pr-link" href="{{ route('payroll.show', $p->id) }}">{{ __('Payslip') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">{{ __('Totals') }}</td>
                            <td>£{{ number_format($totals['gross'], 2) }}</td>
                            <td>£{{ number_format($totals['tax'], 2) }}</td>
                            <td>£{{ number_format($totals['ni'], 2) }}</td>
                            <td>£{{ number_format($totals['net'], 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            @endif

            
        </div>
    </div>
</div>
@endsection