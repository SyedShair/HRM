<div class="table-responsive-wrap">
<table class="table responsive nobordertop">
    <thead>
        <tr>
            <th class="text-left">{{ __('Name') }}</th>
            <th class="text-left">{{ __('Type') }}</th>
            <th class="text-left">{{ __('Time') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($a ?? [] as $v)
            @if($v->timein != null && $v->timeout == null)
                <tr>
                    <td class="name-title">{{ $v->employee }}</td>
                    <td>Time-In</td>
                    <td>
                        {{ $tf == 1 ? date('h:i:s A', strtotime($v->timein)) : date('H:i:s', strtotime($v->timein)) }}
                    </td>
                </tr>
            @elseif($v->timein != null && $v->timeout != null)
                <tr>
                    <td class="name-title">{{ $v->employee }}</td>
                    <td>Time-Out</td>
                    <td>
                        {{ $tf == 1 ? date('h:i:s A', strtotime($v->timeout)) : date('H:i:s', strtotime($v->timeout)) }}
                    </td>
                </tr>
            @endif
        @empty
            <tr>
                <td colspan="3" class="text-center text-muted">{{ __('No recent attendance') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>