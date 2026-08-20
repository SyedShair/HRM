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
    @endphp

    <tr class="{{ ($diffDays !== null && $diffDays <= 90 && $diffDays > 0) ? 'expiring-row' : '' }}">

        <td>{{ $employee->idno }}</td>
        <td>{{ $employee->lastname }}, {{ $employee->firstname }}</td>
        <td>{{ $employee->company }}</td>
        <td>{{ $employee->department }}</td>
        <td>{{ $employee->jobposition }}</td>

        <td>
            @if(empty($employee->sharecode))

                {{-- NO SHARE CODE --}}
                <span class="ui grey label">
                    No Share Code
                </span>

            @elseif(!$sharecodeExpiry)

                {{-- SHARE CODE EXISTS BUT NO EXPIRY DATE --}}
                <div>
                    <strong>{{ $employee->sharecode }}</strong>
                </div>

                <div style="margin-top: 5px;">
                    <span class="ui orange label">
                        Expiry Not Set
                    </span>
                </div>

            @elseif($sharecodeExpired)

                {{-- EXPIRED --}}
                <div>
                    <span class="ui red label">
                        Share Code Expired
                    </span>
                </div>

            @else

                {{-- VALID SHARE CODE --}}
                <div>
                    <strong>{{ $employee->sharecode }}</strong>
                </div>

                <div style="margin-top: 5px;">

                    @if($sharecodeDaysLeft > 30)

                        <span class="ui green label">
                            {{ $sharecodeDaysLeft }} days left
                        </span>

                    @elseif($sharecodeDaysLeft > 14)

                        <span class="ui yellow label">
                            {{ $sharecodeDaysLeft }} days left
                        </span>

                    @else

                        <span class="ui red label">
                            {{ $sharecodeDaysLeft }} days left
                        </span>

                    @endif

                </div>

                {{-- SAFE: only executed when $sharecodeExpiry exists --}}
                <div style="font-size: 11px; color: #777; margin-top: 3px;">
                    Expires {{ $sharecodeExpiry->format('d M Y') }}
                </div>

            @endif
        </td>

        <!-- PASSPORT (nationalid) + expiry countdown -->
        <td>
            <div class="visa-expiry-date">{{ $employee->nationalid ?? '—' }}</div>

            @if($passportExpiry)
                @if($passportExpired)
                    <span class="ui red label">Expired</span>
                @else
                    <span class="ui {{ $passportMonths > 6 ? 'green' : ($passportMonths > 3 ? 'yellow' : 'red') }} label">
                        {{ $passportMonths }} months {{ $passportDays }} days left
                    </span>
                @endif
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