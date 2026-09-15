@php
    use Carbon\Carbon;

    /*
    |--------------------------------------------------------------------------
    | TODAY
    |--------------------------------------------------------------------------
    */
    $now = Carbon::today();
@endphp


@foreach($data ?? [] as $employee)

    @php

        /*
        |--------------------------------------------------------------------------
        | VISA ISSUE DATE
        |--------------------------------------------------------------------------
        */

        $visaIssue = null;

        if (!empty($employee->visaissuedate)) {
            try {
                $visaIssue = Carbon::parse(
                    $employee->visaissuedate
                )->startOfDay();
            } catch (\Exception $e) {
                $visaIssue = null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | VISA EXPIRY DATE
        |--------------------------------------------------------------------------
        */

        $end = null;

        if (!empty($employee->visaend)) {
            try {
                $end = Carbon::parse(
                    $employee->visaend
                )->startOfDay();
            } catch (\Exception $e) {
                $end = null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ORIGINAL VISA DURATION
        |--------------------------------------------------------------------------
        |
        | Visa Issue Date -> Visa Expiry Date
        |
        | Example:
        | 14/09/2026 -> 14/03/2027
        | = 6 months 0 days
        |
        */

        $visaDurationMonths = null;
        $visaDurationDays = null;

        if (
            $visaIssue &&
            $end &&
            $end->gte($visaIssue)
        ) {

            $visaDuration = $visaIssue->diff($end);

            $visaDurationMonths =
                ($visaDuration->y * 12) +
                $visaDuration->m;

            $visaDurationDays =
                $visaDuration->d;
        }


        /*
        |--------------------------------------------------------------------------
        | REMAINING VISA TIME
        |--------------------------------------------------------------------------
        |
        | Today -> Visa Expiry Date
        |
        */

        $months = null;
        $days = null;
        $diffDays = null;
        $expired = false;

        if ($end) {

            $diffDays = $now->diffInDays(
                $end,
                false
            );

            if ($diffDays < 0) {

                $expired = true;

            } else {

                $diff = $now->diff($end);

                $months =
                    ($diff->y * 12) +
                    $diff->m;

                $days =
                    $diff->d;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PASSPORT EXPIRY
        |--------------------------------------------------------------------------
        */

        $passportExpiry = null;
        $passportMonths = null;
        $passportDays = null;
        $passportExpired = false;

        if (!empty($employee->idexpirydate)) {

            try {

                $passportExpiry = Carbon::parse(
                    $employee->idexpirydate
                )->startOfDay();

                $passportDiffDays = $now->diffInDays(
                    $passportExpiry,
                    false
                );

                if ($passportDiffDays < 0) {

                    $passportExpired = true;

                } else {

                    $passportDiff = $now->diff(
                        $passportExpiry
                    );

                    $passportMonths =
                        ($passportDiff->y * 12) +
                        $passportDiff->m;

                    $passportDays =
                        $passportDiff->d;
                }

            } catch (\Exception $e) {

                $passportExpiry = null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SHARE CODE EXPIRY
        |--------------------------------------------------------------------------
        */

        $sharecodeExpiry = null;
        $sharecodeExpired = false;
        $sharecodeDaysLeft = null;

        if (!empty($employee->sharecode_expires_at)) {

            try {

                $sharecodeExpiry = Carbon::parse(
                    $employee->sharecode_expires_at
                )->startOfDay();

                $sharecodeDaysLeft = (int) $now->diffInDays(
                    $sharecodeExpiry,
                    false
                );

                if ($sharecodeDaysLeft < 0) {
                    $sharecodeExpired = true;
                }

            } catch (\Exception $e) {

                $sharecodeExpiry = null;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | RED BADGE CONDITIONS
        |--------------------------------------------------------------------------
        */

        $visaRed =
            $end &&
            (
                $expired ||
                ($months !== null && $months <= 3)
            );


        $passportRed =
            $passportExpiry &&
            (
                $passportExpired ||
                ($passportMonths !== null && $passportMonths <= 3)
            );


        $sharecodeRed =
            $sharecodeExpiry &&
            (
                $sharecodeExpired ||
                (
                    $sharecodeDaysLeft !== null &&
                    $sharecodeDaysLeft <= 14
                )
            );


        /*
        |--------------------------------------------------------------------------
        | ROW BLINK
        |--------------------------------------------------------------------------
        */

        $hasRedBadge =
            $visaRed ||
            $passportRed ||
            $sharecodeRed;

    @endphp


    <tr class="{{ $hasRedBadge ? 'expiring-row' : '' }}">


        {{-- =========================================================
             EMPLOYEE ID
        ========================================================== --}}

        <td>
            {{ $employee->idno }}
        </td>


        {{-- =========================================================
             EMPLOYEE
        ========================================================== --}}

        <td>
            {{ $employee->lastname }}, {{ $employee->firstname }}
        </td>


        {{-- =========================================================
             COMPANY
        ========================================================== --}}

        <td>
            {{ $employee->company }}
        </td>


        {{-- =========================================================
             DEPARTMENT
        ========================================================== --}}

        <td>
            {{ $employee->department }}
        </td>


        {{-- =========================================================
             POSITION
        ========================================================== --}}

        <td>
            {{ $employee->jobposition }}
        </td>


        {{-- =========================================================
             SHARE CODE
        ========================================================== --}}

        <td>

            @if(empty($employee->sharecode))

                <span class="ui grey label">
                    No Share Code
                </span>

            @else

                <div class="secret-row">

                    <span class="secret-value">
                        {{ $employee->sharecode }}
                    </span>

                    <a href="javascript:void(0)"
                       class="toggle-secret"
                       title="Show / hide share code">

                        <i class="eye icon"></i>

                    </a>

                </div>


                @if(!$sharecodeExpiry)

                    <span class="ui orange label">
                        Expiry Not Set
                    </span>


                @elseif($sharecodeExpired)

                    <span class="ui red label">
                        Share Code Expired
                    </span>


                @else

                    @php
                        $sharecodeLabel =
                            $sharecodeDaysLeft > 30
                                ? 'green'
                                : (
                                    $sharecodeDaysLeft > 14
                                        ? 'yellow'
                                        : 'red'
                                );
                    @endphp


                    <span class="ui {{ $sharecodeLabel }} label">

                        @if($sharecodeDaysLeft == 0)

                            Expires Today

                        @else

                            {{ $sharecodeDaysLeft }} days left

                        @endif

                    </span>


                    <div style="
                        font-size:11px;
                        color:#777;
                        margin-top:3px;
                    ">

                        Expires
                        {{ $sharecodeExpiry->format('d M Y') }}

                    </div>

                @endif

            @endif

        </td>


        {{-- =========================================================
             PASSPORT
        ========================================================== --}}

        <td>

            @if(!empty($employee->nationalid))

                <div class="secret-row">

                    <span class="secret-value">
                        {{ $employee->nationalid }}
                    </span>

                    <a href="javascript:void(0)"
                       class="toggle-secret"
                       title="Show / hide passport number">

                        <i class="eye icon"></i>

                    </a>

                </div>

            @else

                <div class="secret-row">

                    <span class="cell-muted">
                        —
                    </span>

                </div>

            @endif


            @if($passportExpiry)

                @if($passportExpired)

                    <span class="ui red label">
                        Passport Expired
                    </span>

                @elseif(
                    $passportMonths == 0 &&
                    $passportDays == 0
                )

                    <span class="ui red label">
                        Expires Today
                    </span>

                @else

                    @php
                        $passportLabel =
                            $passportMonths > 6
                                ? 'green'
                                : (
                                    $passportMonths > 3
                                        ? 'yellow'
                                        : 'red'
                                );
                    @endphp


                    <span class="ui {{ $passportLabel }} label">

                        {{ $passportMonths }}
                        months

                        {{ $passportDays }}
                        days left

                    </span>

                @endif

            @else

                <span class="ui grey label">
                    No Expiry Set
                </span>

            @endif

        </td>


        {{-- =========================================================
             VISA EXPIRY
        ========================================================== --}}

        <td>

            @if($end)

                {{-- Visa expiry date --}}

                <div class="visa-expiry-date">

                    {{ $end->format('d M Y') }}

                </div>


                {{-- Remaining time --}}

                @if($expired)

                    <span class="ui red label">
                        Expired
                    </span>


                @elseif(
                    $months == 0 &&
                    $days == 0
                )

                    <span class="ui red label">
                        Expires Today
                    </span>


                @else

                    @php

                        $visaLabel =
                            $months > 6
                                ? 'green'
                                : (
                                    $months > 3
                                        ? 'yellow'
                                        : 'red'
                                );

                    @endphp


                    <span class="ui {{ $visaLabel }} label">

                        {{ $months }}
                        months

                        {{ $days }}
                        days left

                    </span>

                @endif


                {{-- =================================================
                     ORIGINAL VISA DURATION
                ================================================== --}}

                @if(
                    $visaIssue &&
                    $visaDurationMonths !== null
                )

                    <div style="
                        font-size:11px;
                        color:#777;
                        margin-top:4px;
                    ">

                        Visa duration:
                        {{ $visaDurationMonths }}
                        months

                        {{ $visaDurationDays }}
                        days

                    </div>

                @endif

            @else

                <span class="ui green label">
                    British Citizen
                </span>

            @endif

        </td>


        {{-- =========================================================
             STATUS
        ========================================================== --}}

        <td>

            @if($employee->employmentstatus === 'Active')

                <span class="ui green label">
                    Active
                </span>

            @else

                <span class="ui grey label">
                    Archived
                </span>

            @endif

        </td>


        {{-- =========================================================
             ACTIONS
        ========================================================== --}}

        <td class="right aligned">


            {{-- Documents --}}

            <a href="{{ url('/employee/'.$employee->id.'/documents') }}"
               class="ui circular basic icon button tiny blue"
               title="Documents">

                <i class="folder open icon"></i>

            </a>


            {{-- View Profile --}}

            <a href="{{ url('/profile/view/'.$employee->reference) }}"
               class="ui circular basic icon button tiny green"
               title="View Profile">

                <i class="file alternate outline icon"></i>

            </a>


            {{-- Edit Profile --}}

            <a href="{{ url('/profile/edit/'.$employee->reference) }}"
               class="ui circular basic icon button tiny orange"
               title="Edit Profile">

                <i class="edit outline icon"></i>

            </a>


            {{-- Delete --}}

            <a href="{{ url('/profile/delete/'.$employee->reference) }}"
               class="ui circular basic icon button tiny red"
               title="Delete">

                <i class="trash alternate outline icon"></i>

            </a>


            {{-- Archive --}}

            <a href="{{ url('/profile/archive/'.$employee->reference) }}"
               class="ui circular basic icon button tiny grey"
               title="Archive">

                <i class="archive icon"></i>

            </a>


            {{-- Print PDF --}}

            <a href="{{ route('employee.print.pdf', $employee->id) }}"
               class="ui circular basic icon button tiny purple"
               target="_blank"
               title="Print PDF">

                <i class="print icon"></i>

            </a>


            {{-- QR --}}

            <button type="button"
                    class="ui circular basic icon button tiny teal download-qr"
                    data-pdf="{{ route('employee.print.pdf', $employee->id) }}"
                    title="QR Code">

                <i class="qrcode icon"></i>

            </button>

        </td>

    </tr>

@endforeach