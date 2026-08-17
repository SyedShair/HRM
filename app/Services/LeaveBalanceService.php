<?php

namespace App\Services;

use App\Classes\table;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Leave Balance Service (v2 - wired to the real `leaves` table)
|--------------------------------------------------------------------------
| Same accrual math as before, but now reads from the `leaves` table
| behind your existing LeavesController / PersonalLeavesController
| instead of a separate table. Assumed columns, based on what your
| "Leaves of Absence" view already renders: id, employee_id,
| leavetype_id, leavefrom, leaveto, returndate, status
| ('Pending'|'Approved'|'Denied'), comment, file.
|
| If your real column names differ (e.g. `employee` is already a raw
| name column rather than employee_id), only the where()/select() calls
| in forEmployee() need adjusting - the entitlement/accrual math is
| independent of that.
*/
class LeaveBalanceService
{
    public function forEmployee(int $employeeId, ?int $year = null): array
    {
        $year = $year ?: (int) now()->format('Y');
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create($year, 12, 31)->endOfDay();

        $employee = table::leaves()->where('reference', $employeeId)->first();
        if (!$employee) {
            return [];
        }

        $leaveGroup = table::leavegroup()->where('id', $employee->typeid)->first();
        if (!$leaveGroup) {
            return [];
        }

        $leaveTypeIds = array_values(array_filter(array_map(
            fn ($v) => (int) trim($v),
            explode(',', (string) $leaveGroup->leaveprivileges)
        )));

        if (empty($leaveTypeIds)) {
            return [];
        }

        $leaveTypes = table::leavetypes()->whereIn('id', $leaveTypeIds)->get();

        $hireDate = $employee->startdate ? Carbon::parse($employee->startdate) : $yearStart->copy();
        $periodStart = $hireDate->year === $year ? $hireDate->copy() : $yearStart->copy();

        $records = table::leaves()
            ->where('employee_id', $employeeId)
            ->whereIn('leavetype_id', $leaveTypeIds)
            ->where('leavefrom', '<=', $yearEnd->toDateString())
            ->where('leaveto', '>=', $yearStart->toDateString())
            ->whereIn('status', ['Approved', 'Pending'])
            ->get(['leavetype_id', 'leavefrom', 'leaveto', 'status']);

        $balances = [];

        foreach ($leaveTypes as $lt) {
            $fullEntitlement = (float) $lt->limit;
            $entitlement = $this->proratedAnnualEntitlement($fullEntitlement, $periodStart, $yearEnd, $hireDate->year === $year);

            $accrued = strcasecmp((string) $lt->percalendar, 'Monthly') === 0
                ? $this->monthlyAccrued($fullEntitlement, $periodStart, $yearEnd)
                : $entitlement;

            $typeRecords = $records->where('leavetype_id', $lt->id);

            $used = $this->sumDays($typeRecords->where('status', 'Approved'));
            $pending = $this->sumDays($typeRecords->where('status', 'Pending'));

            $remaining = round($entitlement - $used - $pending, 1);
            $availableNow = round($accrued - $used - $pending, 1);

            $balances[] = [
                'id' => (int) $lt->id,
                'leavetype' => $lt->leavetype,
                'term' => $lt->percalendar,
                'entitlement' => round($entitlement, 1),
                'accrued' => round($accrued, 1),
                'used' => $used,
                'pending' => $pending,
                'remaining' => $remaining,
                'available_now' => $availableNow,
                'percent_used' => $entitlement > 0
                    ? (int) min(100, round((($used + $pending) / $entitlement) * 100))
                    : 0,
            ];
        }

        return $balances;
    }

    public function forEmployeeAndType(int $employeeId, int $leaveTypeId, ?int $year = null): ?array
    {
        return collect($this->forEmployee($employeeId, $year))->firstWhere('id', $leaveTypeId);
    }

    private function sumDays($records): float
    {
        $total = 0.0;
        foreach ($records as $r) {
            $total += $this->businessDaysBetween(Carbon::parse($r->leavefrom), Carbon::parse($r->leaveto));
        }
        return $total;
    }

    private function proratedAnnualEntitlement(float $fullEntitlement, Carbon $periodStart, Carbon $yearEnd, bool $isNewStarterThisYear): float
    {
        if (!$isNewStarterThisYear) {
            return $fullEntitlement;
        }

        $monthsRemaining = max(0, min(12, $periodStart->diffInMonths($yearEnd->copy()->addDay())));

        return round($fullEntitlement * ($monthsRemaining / 12), 1);
    }

    private function monthlyAccrued(float $fullEntitlement, Carbon $periodStart, Carbon $yearEnd): float
    {
        $monthlyRate = $fullEntitlement / 12;
        $asOf = Carbon::now()->lessThan($yearEnd) ? Carbon::now() : $yearEnd;

        if ($asOf->lessThan($periodStart)) {
            return 0.0;
        }

        $monthsElapsed = min(12, $periodStart->diffInMonths($asOf) + 1);

        return round($monthlyRate * $monthsElapsed, 1);
    }

    /**
     * Business days (Mon-Fri) between two dates, inclusive.
     */
    public function businessDaysBetween(Carbon $start, Carbon $end): float
    {
        $days = 0;
        $cursor = $start->copy();

        while ($cursor->lessThanOrEqualTo($end)) {
            if (!$cursor->isWeekend()) {
                $days++;
            }
            $cursor->addDay();
        }

        return (float) $days;
    }
}