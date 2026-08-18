<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Classes\table;
use App\Models\Payroll;
use App\Classes\permission;
use Illuminate\Http\Request;
use App\Traits\UkPayrollHelpers;
use App\Traits\AttendanceShiftHelpers;

class PayrollController extends Controller
{
    // Same shift-resolution logic ClockController uses, so a payslip
    // and a clock-in always agree on what a "shift" was for a date.
    use AttendanceShiftHelpers;
    use UkPayrollHelpers;

    /**
     * Fallback contracted weekly hours used when an employee record
     * doesn't have its own weeklyhours value set.
     */
    protected float $defaultWeeklyHours = 37.5;

    /**
     * Fallback standard tax code shown on the payslip when the
     * employee record doesn't carry its own.
     */
    protected string $defaultTaxCode = '1257L';

    /**
     * Payroll dashboard - pick a month/year, see what's been generated,
     * generate/regenerate, drill into a payslip.
     */
    public function index(Request $request)
    {
        if (permission::permitted('payroll-view') == 'fail') {
            return redirect()->route('denied');
        }

        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $periodStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        $payrolls = Payroll::where('period_start', $periodStart->toDateString())
            ->orderBy('employee')
            ->get();

        $totals = [
            'gross'       => $payrolls->sum('gross_pay'),
            'tax'         => $payrolls->sum('income_tax'),
            'ni'          => $payrolls->sum('employee_ni'),
            'employer_ni' => $payrolls->sum('employer_ni'),
            'net'         => $payrolls->sum('net_pay'),
        ];

        return view('payroll.index', compact('payrolls', 'totals', 'month', 'year'));
    }

    /**
     * Generate (or regenerate) payroll for every active employee for
     * the given month. Existing rows for the same employee + period are
     * overwritten, so this is safe to re-run after attendance
     * corrections.
     */
    public function generate(Request $request)
    {
        if (permission::permitted('payroll-generate') == 'fail') {
            return redirect()->route('denied');
        }

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:2100',
        ]);

        $month = (int) $request->month;
        $year  = (int) $request->year;

        $periodStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $periodEnd   = $periodStart->copy()->endOfMonth();

        // Adjust 'employmentstatus' value below to match whatever your
        // tbl_people actually stores (e.g. 'Active', 'active', 1...).
        $employees = table::people()
            ->where('employmentstatus', 'Active')
            ->get();

        $generated = 0;
        $skipped   = [];

        foreach ($employees as $emp) {
            $company = table::companydata()->where('reference', $emp->id)->first();

            if (!$company || empty($company->idno)) {
                $skipped[] = trim($emp->firstname . ' ' . $emp->lastname) . ' (no employee ID on file)';
                continue;
            }

            $result = $this->calculateForEmployee($emp, $company->idno, $periodStart, $periodEnd);

            if ($result === null) {
                $skipped[] = trim($emp->firstname . ' ' . $emp->lastname) . ' (no hourly rate or account pay set)';
                continue;
            }

            $payroll = Payroll::updateOrCreate(
                [
                    'reference'    => $emp->id,
                    'period_start' => $periodStart->toDateString(),
                    'period_end'   => $periodEnd->toDateString(),
                ],
                array_merge($result, [
                    'idno'         => $company->idno,
                    'employee'     => mb_strtoupper($emp->lastname . ', ' . $emp->firstname . ' ' . $emp->mi),
                    'department'   => $emp->department ?? null,
                    'ni_number'    => $company->ninumber ?? null,
                    'generated_at' => now(),
                ])
            );

            // YTD figures depend on every row for this employee in the
            // same UK tax year existing, so compute after the upsert.
            $this->applyYearToDate($payroll, $periodStart);

            $generated++;
        }

        return redirect()
            ->route('payroll.index', ['month' => $month, 'year' => $year])
            ->with('success', "Generated payroll for {$generated} employee(s).")
            ->with('skipped', $skipped);
    }

    /**
     * One employee's full payslip for one period.
     */
    public function show($id)
    {
        if (permission::permitted('payroll-view') == 'fail') {
            return redirect()->route('denied');
        }

        $payroll = Payroll::findOrFail($id);

        return view('payroll.show', compact('payroll'));
    }

    public function updateStatus(Request $request, $id)
    {
        if (permission::permitted('payroll-generate') == 'fail') {
            return redirect()->route('denied');
        }

        $request->validate(['status' => 'required|in:Pending,Approved,Paid']);

        Payroll::where('id', $id)->update(['status' => $request->status]);

        return back()->with('success', 'Status updated.');
    }

    public function destroy($id)
    {
        if (permission::permitted('payroll-generate') == 'fail') {
            return redirect()->route('denied');
        }

        Payroll::where('id', $id)->delete();

        return back()->with('success', 'Payroll entry removed.');
    }

    /**
     * Core calculation for one employee over one period. Returns null
     * if the employee has neither an hourly rate nor account (salaried)
     * pay set - there's nothing to pay them on.
     */
    protected function calculateForEmployee($emp, string $idno, Carbon $periodStart, Carbon $periodEnd): ?array
    {
        $perHourPay = is_numeric($emp->perhourpay) ? (float) $emp->perhourpay : 0.0;
        $accountPay = is_numeric($emp->accountpay) ? (float) $emp->accountpay : 0.0;

        if ($perHourPay <= 0 && $accountPay <= 0) {
            return null;
        }

        $payType = $perHourPay > 0 ? 'hourly' : 'salaried';

        // Contracted weekly hours, used only for projecting gross/tax/NI
        // out to weekly/hourly figures - falls back to defaultWeeklyHours
        // if the employee record doesn't carry its own value.
        $weeklyHours = is_numeric($emp->weeklyhours ?? null)
            ? (float) $emp->weeklyhours
            : $this->defaultWeeklyHours;

        $taxCode = !empty($emp->taxcode) ? $emp->taxcode : $this->defaultTaxCode;

        // Pull the whole period's attendance once, indexed by date.
        $attendance = table::attendance()
            ->where('idno', $idno)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString());

        // Walk the schedule day-by-day to know what SHOULD have
        // happened, using the same resolveShift() ClockController uses.
        // Keyed by date => scheduled minutes, so calculateSalaried() can
        // check each individual scheduled day against attendance.
        $scheduledDates = [];

        for ($date = $periodStart->copy(); $date->lte($periodEnd); $date->addDay()) {
            $shift = $this->resolveShift($idno, $date->toDateString());

            if ($shift === null || $shift['is_off']) {
                continue;
            }

            $in  = Carbon::createFromFormat('H:i:s', $shift['time_in']);
            $out = Carbon::createFromFormat('H:i:s', $shift['time_out']);
            if ($out->lessThanOrEqualTo($in)) {
                $out->addDay();
            }

            $scheduledDates[$date->toDateString()] = $in->diffInMinutes($out);
        }

        $scheduledDays  = count($scheduledDates);
        $scheduledHours = round(array_sum($scheduledDates) / 60, 2);

        $periodMeta = [
            'tax_code'     => $taxCode,
            'period_label' => 'M ' . $this->taxMonthNumber($periodStart),
        ];

        if ($payType === 'hourly') {
            return array_merge(
                $this->calculateHourly($perHourPay, $attendance, $scheduledHours, $scheduledDays, $accountPay, $weeklyHours),
                $periodMeta
            );
        }

        return array_merge(
            $this->calculateSalaried($accountPay, $attendance, $scheduledDates, $scheduledHours, $scheduledDays, $weeklyHours),
            $periodMeta
        );
    }

    /**
     * Hourly employees: paid purely on hours actually clocked via
     * ClockController@add. Regular hours at 1x, overtime_minutes
     * (already computed at clock-out, on a normal working day) at
     * 1.5x, any day flagged 'Rest Day' (worked on a scheduled day off)
     * at 2x.
     *
     * $accountPay / $weeklyHours are only used to populate the
     * "contracted" reference figures - they play no part in the actual
     * hourly gross/tax/NI/net calculation below, which is unchanged.
     */
    protected function calculateHourly(float $rate, $attendance, float $scheduledHours, int $scheduledDays, float $accountPay = 0.0, float $weeklyHours = 37.5): array
    {
        $regularHours  = 0.0;
        $overtimeHours = 0.0;
        $restdayHours  = 0.0;
        $workedHours   = 0.0;

        foreach ($attendance as $row) {
            $hours = (float) ($row->totalhours ?? 0);
            $workedHours += $hours;

            if (($row->status_timein ?? null) === 'Rest Day') {
                $restdayHours += $hours;
                continue;
            }

            $otHours = min(($row->overtime_minutes ?? 0) / 60, $hours);
            $overtimeHours += $otHours;
            $regularHours  += max(0, $hours - $otHours);
        }

        $overtimePay = round($overtimeHours * $rate * 1.5, 2);
        $restdayPay  = round($restdayHours * $rate * 2, 2);
        $regularPay  = round($regularHours * $rate, 2);

        $grossPay = round($regularPay + $overtimePay + $restdayPay, 2);

        // No pension/salary-sacrifice deduction modelled yet, so
        // taxable pay = NI'able pay = gross pay, non-taxable pay = 0.
        $taxablePay    = $grossPay;
        $nonTaxablePay = 0.0;
        $niablePay     = $grossPay;

        $incomeTax = $this->estimateMonthlyIncomeTax($taxablePay);
        $ni        = $this->estimateMonthlyNi($niablePay);
        $employerNi = $this->estimateMonthlyEmployerNi($niablePay);

        return [
            'pay_type'                 => 'hourly',
            'rate'                     => $rate,
            'contracted_monthly_gross' => $accountPay > 0 ? $accountPay : null,
            'contracted_breakdown'     => $accountPay > 0 ? $this->contractedPayBreakdown($accountPay, $weeklyHours) : null,
            'scheduled_hours'          => $scheduledHours,
            'scheduled_days'           => $scheduledDays,
            'worked_hours'             => round($workedHours, 2),
            'regular_hours'            => round($regularHours, 2),
            'overtime_hours'           => round($overtimeHours, 2),
            'restday_hours'            => round($restdayHours, 2),
            'unapproved_absence_days'  => 0,
            'absence_deduction'        => 0,
            'overtime_pay'             => $overtimePay,
            'restday_pay'              => $restdayPay,
            'taxable_pay'              => round($taxablePay, 2),
            'non_taxable_pay'          => round($nonTaxablePay, 2),
            'niable_pay'               => round($niablePay, 2),
            'gross_pay'                => $grossPay,
            'income_tax'               => $incomeTax,
            'employee_ni'              => $ni,
            'employer_ni'              => $employerNi,
            'total_deductions'         => round($incomeTax + $ni, 2),
            'net_pay'                  => round($grossPay - $incomeTax - $ni, 2),
            'status'                   => 'Pending',
        ];
    }

    /**
     * Salaried (accountpay) employees: paid the full monthly amount,
     * MINUS a per-scheduled-day deduction for any scheduled working day
     * with no attendance record (or an incomplete one) AND no reason on
     * file. A day with a recorded reason (leave, sick, etc.) is treated
     * as approved and not deducted. Overtime and rest-day work are paid
     * ON TOP of salary using an equivalent hourly rate derived from the
     * schedule (accountpay / scheduled hours).
     */
    protected function calculateSalaried(float $accountPay, $attendance, array $scheduledDates, float $scheduledHours, int $scheduledDays, float $weeklyHours = 37.5): array
    {
        $dailyRate        = $scheduledDays > 0 ? $accountPay / $scheduledDays : 0.0;
        $hourlyEquivalent = $scheduledHours > 0 ? $accountPay / $scheduledHours : 0.0;

        $unapprovedDays   = 0;
        $absenceDeduction = 0.0;

        foreach ($scheduledDates as $dateString => $minutes) {
            $row = $attendance->get($dateString);

            $fullyWorked = $row && !empty($row->timein) && !empty($row->timeout);
            $excused     = $row && !empty($row->reason);

            if (!$fullyWorked && !$excused) {
                $unapprovedDays++;
                $absenceDeduction += $dailyRate;
            }
        }

        $overtimeHours = 0.0;
        $restdayHours  = 0.0;
        $workedHours   = 0.0;

        foreach ($attendance as $row) {
            $workedHours += (float) ($row->totalhours ?? 0);

            if (($row->status_timein ?? null) === 'Rest Day') {
                $restdayHours += (float) ($row->totalhours ?? 0);
            } elseif (!empty($row->overtime_minutes)) {
                $overtimeHours += $row->overtime_minutes / 60;
            }
        }

        $overtimePay      = round($overtimeHours * $hourlyEquivalent * 1.5, 2);
        $restdayPay       = round($restdayHours * $hourlyEquivalent * 2, 2);
        $absenceDeduction = round($absenceDeduction, 2);

        $grossPay = max(0, round($accountPay - $absenceDeduction + $overtimePay + $restdayPay, 2));

        // No pension/salary-sacrifice deduction modelled yet, so
        // taxable pay = NI'able pay = gross pay, non-taxable pay = 0.
        $taxablePay    = $grossPay;
        $nonTaxablePay = 0.0;
        $niablePay     = $grossPay;

        $incomeTax  = $this->estimateMonthlyIncomeTax($taxablePay);
        $ni         = $this->estimateMonthlyNi($niablePay);
        $employerNi = $this->estimateMonthlyEmployerNi($niablePay);

        return [
            'pay_type'                 => 'salaried',
            'rate'                     => $accountPay,
            'contracted_monthly_gross' => $accountPay,
            'contracted_breakdown'     => $this->contractedPayBreakdown($accountPay, $weeklyHours),
            'scheduled_hours'          => $scheduledHours,
            'scheduled_days'           => $scheduledDays,
            'worked_hours'             => round($workedHours, 2),
            'regular_hours'            => round(max(0, $workedHours - $overtimeHours - $restdayHours), 2),
            'overtime_hours'           => round($overtimeHours, 2),
            'restday_hours'            => round($restdayHours, 2),
            'unapproved_absence_days'  => $unapprovedDays,
            'absence_deduction'        => $absenceDeduction,
            'overtime_pay'             => $overtimePay,
            'restday_pay'              => $restdayPay,
            'taxable_pay'              => round($taxablePay, 2),
            'non_taxable_pay'          => round($nonTaxablePay, 2),
            'niable_pay'               => round($niablePay, 2),
            'gross_pay'                => $grossPay,
            'income_tax'               => $incomeTax,
            'employee_ni'              => $ni,
            'employer_ni'              => $employerNi,
            'total_deductions'         => round($incomeTax + $ni, 2),
            'net_pay'                  => round($grossPay - $incomeTax - $ni, 2),
            'status'                   => 'Pending',
        ];
    }

    /**
     * Fill in the four YTD columns shown on the real payslip (Total
     * Pay, Tax, Employee NI, Employer NI) by summing every payroll row
     * for this employee within the same UK tax year up to and
     * including the current period. Month 1 (April) YTD is naturally
     * just that month's own figures.
     */
    protected function applyYearToDate(Payroll $payroll, Carbon $periodStart): void
    {
        $yearStart = $this->taxYearStart($periodStart);

        $rows = Payroll::where('reference', $payroll->reference)
            ->whereBetween('period_start', [$yearStart->toDateString(), $periodStart->toDateString()])
            ->get();

        $payroll->update([
            'ytd_gross'       => round($rows->sum('gross_pay'), 2),
            'ytd_taxable_pay' => round($rows->sum('taxable_pay'), 2),
            'ytd_tax'         => round($rows->sum('income_tax'), 2),
            'ytd_employee_ni' => round($rows->sum('employee_ni'), 2),
            'ytd_employer_ni' => round($rows->sum('employer_ni'), 2),
            'ytd_niable_pay'  => round($rows->sum('niable_pay'), 2),
        ]);
    }
}