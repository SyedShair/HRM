@php
    use Carbon\Carbon;
    $now = Carbon::now();
@endphp

@foreach($data ?? [] as $employee)

    @php
        $end = $employee->visaend ? Carbon::parse($employee->visaend) : null;

        $months = null;
        $days = null;
        $diffDays = null;
        $expired = false;

        if ($end) {
            $diff = $now->diff($end, false);
            $diffDays = $now->diffInDays($end, false);

            if ($diff->invert) {
                $expired = true;
            } else {
                $months = ($diff->y * 12) + $diff->m;
                $days = $diff->d;
            }
        }

        $passportExpiry = $employee->idexpirydate ? Carbon::parse($employee->idexpirydate) : null;
        $passportMonths = null;
        $passportDays = null;
        $passportExpired = false;

        if ($passportExpiry) {
            $passportDiff = $now->diff($passportExpiry, false);
            if ($passportDiff->invert) {
                $passportExpired = true;
            } else {
                $passportMonths = ($passportDiff->y * 12) + $passportDiff->m;
                $passportDays = $passportDiff->d;
            }
        }

        $sharecodeExpiry = null;
        $sharecodeExpired = false;
        $sharecodeDaysLeft = null;

        if (!empty($employee->sharecode_expires_at)) {
            try {
                $sharecodeExpiry = Carbon::parse($employee->sharecode_expires_at);
                $sharecodeDaysLeft = (int) $now->diffInDays($sharecodeExpiry, false);

                if ($sharecodeDaysLeft < 0) {
                    $sharecodeExpired = true;
                }
            } catch (\Exception $e) {
                $sharecodeExpiry = null;
            }
        }

        /*
        |------------------------------------------------------------
        | Row blink
        |------------------------------------------------------------
        |
        | Previously only tied to the visa's 90-day window (amber AND
        | red both blinked). Now scoped to exactly what shows a RED
        | badge in any of the three columns below - visa, passport, or
        | share code - matching the same "red" cutoffs the badges
        | themselves use (<=3 months for visa/passport, <=14 days for
        | share code), plus any already-expired document.
        |
        */
        $visaRed = $end && ($expired || ($months !== null && $months <= 3));
        $passportRed = $passportExpiry && ($passportExpired || ($passportMonths !== null && $passportMonths <= 3));
        $sharecodeRed = $sharecodeExpiry && ($sharecodeExpired || ($sharecodeDaysLeft !== null && $sharecodeDaysLeft <= 14));

        $hasRedBadge = $visaRed || $passportRed || $sharecodeRed;
    @endphp

    <tr class="{{ $hasRedBadge ? 'expiring-row' : '' }}">

        <td>{{ $employee->idno }}</td>
        <td>{{ $employee->lastname }}, {{ $employee->firstname }}</td>
        <td>{{ $employee->company }}</td>
        <td>{{ $employee->department }}</td>
        <td>{{ $employee->jobposition }}</td>

        {{--
            SHARE CODE
            The code itself is hidden by default behind the eye toggle;
            the days-remaining / expired badge is always visible so the
            column stays useful at a glance without exposing the code.
        --}}
        <td>
            @if(empty($employee->sharecode))

                <span class="ui grey label">
                    No Share Code
                </span>

            @else

                <div class="secret-row">
                    <span class="secret-value">{{ $employee->sharecode }}</span>
                    <a href="javascript:void(0)" class="toggle-secret" title="Show / hide share code">
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

                    <span class="ui {{ $sharecodeDaysLeft > 30 ? 'green' : ($sharecodeDaysLeft > 14 ? 'yellow' : 'red') }} label">
                        {{ $sharecodeDaysLeft }} days left
                    </span>

                    <div style="font-size: 11px; color: #777; margin-top: 3px;">
                        Expires {{ $sharecodeExpiry->format('d M Y') }}
                    </div>

                @endif

            @endif
        </td>

        {{--
            PASSPORT (nationalid)
            Same pattern - number hidden behind the eye toggle, months/days
            remaining always shown.
        --}}
        <td>
            @if(!empty($employee->nationalid))

                <div class="secret-row">
                    <span class="secret-value">{{ $employee->nationalid }}</span>
                    <a href="javascript:void(0)" class="toggle-secret" title="Show / hide passport number">
                        <i class="eye icon"></i>
                    </a>
                </div>

            @else

                <div class="secret-row">
                    <span class="cell-muted">—</span>
                </div>

            @endif

            @if($passportExpiry)
                @if($passportExpired)
                    <span class="ui red label">Passport Expired</span>
                @else
                    <span class="ui {{ $passportMonths > 6 ? 'green' : ($passportMonths > 3 ? 'yellow' : 'red') }} label">
                        {{ $passportMonths }} months {{ $passportDays }} days left
                    </span>
                @endif
            @else
                <span class="ui grey label">No Expiry Set</span>
            @endif
        </td>

        <!-- VISA -->
        <td>
            @if($end)

                <div class="visa-expiry-date">{{ $end->format('d M Y') }}</div>

                @if($expired)
                    <span class="ui red label">Expired</span>
                @else
                    <span class="ui {{ $months > 6 ? 'green' : ($months > 3 ? 'yellow' : 'red') }} label">
                        {{ $months }} months {{ $days }} days left
                    </span>
                @endif

            @else
                <span class="ui green label">British Citizen</span>
            @endif
        </td>

        <!-- STATUS -->
        <td>
            @if($employee->employmentstatus === 'Active')
                <span class="ui green label">Active</span>
            @else
                <span class="ui grey label">Archived</span>
            @endif
        </td>

        <!-- ACTIONS -->
        <td class="right aligned">

            <a href="{{ url('/employee/'.$employee->id.'/documents') }}"
               class="ui circular basic icon button tiny blue">
                <i class="folder open icon"></i>
            </a>

            <a href="{{ url('/profile/view/'.$employee->reference) }}"
               class="ui circular basic icon button tiny green">
                <i class="file alternate outline icon"></i>
            </a>

            <a href="{{ url('/profile/edit/'.$employee->reference) }}"
               class="ui circular basic icon button tiny orange">
                <i class="edit outline icon"></i>
            </a>

            <a href="{{ url('/profile/delete/'.$employee->reference) }}"
               class="ui circular basic icon button tiny red">
                <i class="trash alternate outline icon"></i>
            </a>

            <a href="{{ url('/profile/archive/'.$employee->reference) }}"
               class="ui circular basic icon button tiny grey">
                <i class="archive icon"></i>
            </a>

            <a href="{{ route('employee.print.pdf', $employee->id) }}"
               class="ui circular basic icon button tiny purple"
               target="_blank">
                <i class="print icon"></i>
            </a>

            <button class="ui circular basic icon button tiny teal download-qr"
                    data-pdf="{{ route('employee.print.pdf', $employee->id) }}">
                <i class="qrcode icon"></i>
            </button>

        </td>

    </tr>

@endforeach