<div class="table-responsive-wrap">
<table class="table responsive nobordertop">
    <thead>
        <tr>
            <th class="text-left">{{ __('Name') }}</th>
            <th class="text-left">{{ __('Position') }}</th>
            <th class="text-left">{{ __('Start Date') }}</th>
            <th class="text-left">{{ __('Visa Validity') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($emp_all_type ?? [] as $data)
            @php
                $visaEnd = $data->visaend ? \Carbon\Carbon::parse($data->visaend) : null;
                // Carbon 3 (Laravel 12) changed diffInMonths() to return a float
                // with full decimal precision by default, unlike Carbon 2 which
                // returned a truncated integer. Round to whole months.
                $monthsRemaining = $visaEnd ? (int) round(\Carbon\Carbon::now()->diffInMonths($visaEnd, false)) : null;

                if ($visaEnd === null) {
                    $visaBadgeClass = null; // no visa on file
                } elseif ($visaEnd->isPast()) {
                    $visaBadgeClass = 'badge bg-dark'; // expired
                } elseif ($monthsRemaining > 6) {
                    $visaBadgeClass = 'badge bg-success'; // green
                } elseif ($monthsRemaining > 3) {
                    $visaBadgeClass = 'badge bg-warning'; // yellow
                } else {
                    $visaBadgeClass = 'badge bg-danger'; // red
                }
            @endphp
            <tr>
                <td class="text-left name-title">{{ $data->lastname }}, {{ $data->firstname }}</td>
                <td class="text-left">{{ $data->jobposition }}</td>
                <td class="text-left">{{ date('M d, Y', strtotime($data->startdate)) }}</td>
                <td>
                    <p class="uppercase">
                        @if($visaEnd)
                            {{ $visaEnd->format('d F Y') }}
                            <br>
                            @if($visaEnd->isPast())
                                <span class="badge bg-dark" style="color:#fff;">Expired</span>
                            @else
                                <span class="{{ $visaBadgeClass }}" style="color:#fff;">
                                    {{ $monthsRemaining }} {{ Str::plural('month', $monthsRemaining) }} remaining
                                </span>
                            @endif
                        @else
                            <span class="badge bg-success" style="color:#fff;">British Citizen</span>
                        @endif
                    </p>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">{{ __('No employees found') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>