<div class="table-responsive-wrap">
<table class="table responsive nobordertop">
    <thead>
        <tr>
            <th class="text-left">{{ __('Name') }}</th>
            <th class="text-left">{{ __('Date') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($emp_approved_leave ?? [] as $leaves)
            <tr>
                <td class="text-left name-title">{{ $leaves->employee }}</td>
                <td class="text-left">{{ date('M d, Y', strtotime($leaves->leavefrom)) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="text-center text-muted">{{ __('No recent leaves') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>