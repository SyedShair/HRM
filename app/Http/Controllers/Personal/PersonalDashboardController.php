<?php

namespace App\Http\Controllers\Personal;

use DB;
use App\Classes\table;
use App\Classes\permission;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PersonalDashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Employee Reference
        |--------------------------------------------------------------------------
        */

        $id = \Auth::user()->reference;

        /*
        |--------------------------------------------------------------------------
        | Current Month
        |--------------------------------------------------------------------------
        */

        $sm = date('m/01/Y');
        $em = date('m/31/Y');

        /*
        |--------------------------------------------------------------------------
        | Current Schedule
        |--------------------------------------------------------------------------
        */

        $cs = table::schedules()
            ->where([
                ['reference', $id],
                ['archive', '0']
            ])
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Previous / Archived Schedules
        |--------------------------------------------------------------------------
        */

        $ps = table::schedules()
            ->where([
                ['reference', $id],
                ['archive', '1'],
            ])
            ->orderBy('datefrom', 'desc')
            ->take(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Time Format
        |--------------------------------------------------------------------------
        */

        $tf = table::settings()->value('time_format');

        /*
        |--------------------------------------------------------------------------
        | LEAVE INFORMATION
        |--------------------------------------------------------------------------
        |
        | Approved = approved leave count
        | Pending  = ONLY pending leave count
        | Declined is NOT included in Pending
        |
        */

        // Total approved leaves
        $al = table::leaves()
            ->where('reference', $id)
            ->where('status', 'Approved')
            ->count();

        // Recent approved leaves
        $ald = table::leaves()
            ->where('reference', $id)
            ->where('status', 'Approved')
            ->orderBy('leavefrom', 'desc')
            ->take(8)
            ->get();

        // Total pending leaves
        $pl = table::leaves()
            ->where('reference', $id)
            ->where('status', 'Pending')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | RECENT ATTENDANCE
        |--------------------------------------------------------------------------
        */

        $a = table::attendance()
            ->where('reference', $id)
            ->latest('date')
            ->take(4)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | LATE ARRIVALS - CURRENT MONTH
        |--------------------------------------------------------------------------
        */

        $la = table::attendance()
            ->where([
                ['reference', $id],
                ['status_timein', 'Late Arrival']
            ])
            ->whereBetween('date', [$sm, $em])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | EARLY DEPARTURES - CURRENT MONTH
        |--------------------------------------------------------------------------
        */

        $ed = table::attendance()
            ->where([
                ['reference', $id],
                ['status_timeout', 'Early Departure']
            ])
            ->whereBetween('date', [$sm, $em])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | SEND DATA TO DASHBOARD
        |--------------------------------------------------------------------------
        */

        return view(
            'personal.personal-dashboard',
            compact(
                'cs',
                'ps',
                'al',
                'pl',
                'ald',
                'a',
                'la',
                'ed',
                'tf'
            )
        );
    }
}