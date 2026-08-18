<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * UK PAYE Income Tax + Class 1 National Insurance (employee AND
 * employer) estimator, calibrated against a real Jpingo's Flame Grill
 * payslip (Month 1, 2026/27 tax year, code 1257L, gross £2,580.00):
 *
 *      PAYE Tax        £306.20
 *      Employee NI     £122.56
 *      Employer NI     £324.45
 *      Net Pay         £2,151.24
 *
 * The MONTHLY threshold constants below are the published HMRC
 * "Pay Adjustment Table" / NI table values for a given period, NOT a
 * naive annual-figure/12 - HMRC's real tables round each period's
 * cumulative allowance to the nearest whole pound using their own
 * (non-linear) rounding, so period 1 doesn't always equal annual/12
 * exactly. These constants reproduce the real payslip above; if HMRC
 * publishes new tables for a later tax year, update the constants
 * (not the formulas).
 *
 * IMPORTANT - still an ESTIMATE for budgeting/preview purposes, not a
 * substitute for HMRC-recognised payroll software:
 *  - Non-cumulative: each month is taxed on its own period figures,
 *    not tracked year-to-date the way real cumulative PAYE is.
 *  - Standard tax code (1257L-equivalent) only - no Scottish rates,
 *    no student loan, no pension salary-sacrifice, no benefits in kind.
 *  - Month 1 constants only. Later months' HMRC table values are not
 *    always the prior month's value + a fixed increment (rounding
 *    varies month to month) - if you need this correct beyond month 1,
 *    source the real monthly table values from HMRC rather than
 *    computing them.
 */
trait UkPayrollHelpers
{
    // ---- Income Tax, monthly Month-1 table value, code 1257L ----
    protected float $taxMonthlyPersonalAllowance = 1049.00;
    protected float $taxBasicRate                = 0.20;
    protected float $taxHigherRate                = 0.40;
    protected float $taxAdditionalRate            = 0.45;
    protected float $taxMonthlyBasicRateTop       = 4189.00;  // ~£50,270/yr ÷ 12
    protected float $taxMonthlyHigherRateTop      = 10428.00; // ~£125,140/yr ÷ 12

    // ---- Class 1 Employee NI, monthly table value ----
    protected float $niEmployeeMonthlyThreshold = 1048.00; // Primary Threshold
    protected float $niEmployeeMonthlyUel       = 4189.00; // Upper Earnings Limit
    protected float $niEmployeeMainRate         = 0.08;
    protected float $niEmployeeUpperRate        = 0.02;

    // ---- Class 1 Employer NI, monthly table value ----
    protected float $niEmployerMonthlyThreshold = 417.00; // Secondary Threshold (~£5,000/yr)
    protected float $niEmployerRate             = 0.15;

    /**
     * Weeks used to convert monthly ↔ weekly figures (365.25 ÷ 7).
     */
    protected float $weeksPerYear = 52.1786;

    /**
     * Estimate monthly Income Tax for a given monthly taxable
     * (non-pensionable-deducted) gross pay.
     */
    protected function estimateMonthlyIncomeTax(float $monthlyGross): float
    {
        $taxable = max(0, $monthlyGross - $this->taxMonthlyPersonalAllowance);

        $basicBandSize  = max(0, $this->taxMonthlyBasicRateTop - $this->taxMonthlyPersonalAllowance);
        $higherBandSize = max(0, $this->taxMonthlyHigherRateTop - $this->taxMonthlyBasicRateTop);

        $inBasic  = min($taxable, $basicBandSize);
        $inHigher = min(max(0, $taxable - $basicBandSize), $higherBandSize);
        $inTop    = max(0, $taxable - $basicBandSize - $higherBandSize);

        $tax = ($inBasic * $this->taxBasicRate)
             + ($inHigher * $this->taxHigherRate)
             + ($inTop * $this->taxAdditionalRate);

        return round($tax, 2);
    }

    /**
     * Estimate monthly employee Class 1 National Insurance.
     */
    protected function estimateMonthlyNi(float $monthlyGross): float
    {
        if ($monthlyGross <= $this->niEmployeeMonthlyThreshold) {
            return 0.0;
        }

        if ($monthlyGross <= $this->niEmployeeMonthlyUel) {
            return round(($monthlyGross - $this->niEmployeeMonthlyThreshold) * $this->niEmployeeMainRate, 2);
        }

        $mainBand  = ($this->niEmployeeMonthlyUel - $this->niEmployeeMonthlyThreshold) * $this->niEmployeeMainRate;
        $upperBand = ($monthlyGross - $this->niEmployeeMonthlyUel) * $this->niEmployeeUpperRate;

        return round($mainBand + $upperBand, 2);
    }

    /**
     * Estimate monthly EMPLOYER Class 1 National Insurance (not
     * deducted from the employee - a cost to the employer, shown on
     * the payslip for information only).
     */
    protected function estimateMonthlyEmployerNi(float $monthlyGross): float
    {
        if ($monthlyGross <= $this->niEmployerMonthlyThreshold) {
            return 0.0;
        }

        return round(($monthlyGross - $this->niEmployerMonthlyThreshold) * $this->niEmployerRate, 2);
    }

    /**
     * Full yearly/monthly/weekly/hourly gross-tax-ni-net breakdown,
     * reusing estimateMonthlyIncomeTax()/estimateMonthlyNi() so the
     * monthly row always matches what's stored on the payroll row.
     */
    protected function payBreakdown(float $monthlyGross, float $weeklyHours): array
    {
        if ($monthlyGross <= 0 || $weeklyHours <= 0) {
            return [];
        }

        $monthlyTax = $this->estimateMonthlyIncomeTax($monthlyGross);
        $monthlyNi  = $this->estimateMonthlyNi($monthlyGross);
        $monthlyNet = $monthlyGross - $monthlyTax - $monthlyNi;

        $annualGross = $monthlyGross * 12;
        $annualTax   = round($monthlyTax * 12, 2);
        $annualNi    = round($monthlyNi * 12, 2);
        $annualNet   = $annualGross - $annualTax - $annualNi;

        $weeklyGross = $annualGross / $this->weeksPerYear;
        $weeklyTax   = $annualTax / $this->weeksPerYear;
        $weeklyNi    = $annualNi / $this->weeksPerYear;
        $weeklyNet   = $annualNet / $this->weeksPerYear;

        $hourlyGross = $weeklyGross / $weeklyHours;
        $hourlyTax   = $weeklyTax / $weeklyHours;
        $hourlyNi    = $weeklyNi / $weeklyHours;
        $hourlyNet   = $weeklyNet / $weeklyHours;

        return [
            'yearly'  => $this->roundSet($annualGross, $annualTax, $annualNi, $annualNet),
            'monthly' => $this->roundSet($monthlyGross, $monthlyTax, $monthlyNi, $monthlyNet),
            'weekly'  => $this->roundSet($weeklyGross, $weeklyTax, $weeklyNi, $weeklyNet),
            'hourly'  => $this->roundSet($hourlyGross, $hourlyTax, $hourlyNi, $hourlyNet),
        ];
    }

    /**
     * Same breakdown as payBreakdown(), for the employee's CONTRACTED
     * monthly gross (accountpay) rather than the actual period gross.
     */
    protected function contractedPayBreakdown(float $monthlyGross, float $weeklyHours): array
    {
        return $this->payBreakdown($monthlyGross, $weeklyHours);
    }

    /**
     * UK tax year start (6 April) for whichever tax year a given
     * period date falls in. Jan-Mar dates belong to the tax year that
     * started the previous April.
     */
    protected function taxYearStart(Carbon $periodStart): Carbon
    {
        $year = $periodStart->month >= 4 ? $periodStart->year : $periodStart->year - 1;

        return Carbon::createFromDate($year, 4, 6)->startOfDay();
    }

    /**
     * Tax month number (1-12) within the UK tax year for a given
     * period start date - April = Month 1, March = Month 12.
     */
    protected function taxMonthNumber(Carbon $periodStart): int
    {
        $month = $periodStart->month - 3;

        return $month > 0 ? $month : $month + 12;
    }

    private function roundSet(float $gross, float $tax, float $ni, float $net): array
    {
        return [
            'gross' => round($gross, 2),
            'tax'   => round($tax, 2),
            'ni'    => round($ni, 2),
            'net'   => round($net, 2),
        ];
    }
}