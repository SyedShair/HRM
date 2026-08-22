@extends('layouts.default')

@section('meta')
    <title>Meetings | Jpingos</title>
    <meta name="description" content="Schedule and manage Zoom meetings for employees and outside guests">
@endsection

@section('styles')
<style>
    .filter-tabs a.active { background:#1f2937 !important; color:#fff !important; }
    .badge-scheduled { color:#2563eb; font-weight:600; }
    .badge-started { color:#d97706; font-weight:600; }
    .badge-ended { color:#16a34a; font-weight:600; }
    .badge-cancelled { color:#dc2626; font-weight:600; }
    .category-pill { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }
    .cat-interview { background:#ede9fe; color:#6d28d9; }
    .cat-internal { background:#e0f2fe; color:#0369a1; }
    .cat-client { background:#dcfce7; color:#166534; }
    .cat-other { background:#f3f4f6; color:#4b5563; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <h2 class="page-title">{{ __('Meetings') }}
            <a href="{{ url('meetings/create') }}" class="ui positive button mini offsettop5 float-right">
                <i class="ui icon video"></i>{{ __('Schedule Meeting') }}
            </a>
        </h2>
    </div>

    @if(session('success'))<div class="ui green message">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="ui red message">{{ session('error') }}</div>@endif

    <div class="row">
        <div class="box box-success">
            <div class="box-body">
                <div class="filter-tabs" style="margin-bottom:15px;">
                    <a class="ui button tiny {{ $filter=='upcoming'?'active':'' }}" href="{{ url('meetings?filter=upcoming') }}">{{ __('Upcoming') }}</a>
                    <a class="ui button tiny {{ $filter=='past'?'active':'' }}" href="{{ url('meetings?filter=past') }}">{{ __('Past') }}</a>
                    <a class="ui button tiny {{ $filter=='cancelled'?'active':'' }}" href="{{ url('meetings?filter=cancelled') }}">{{ __('Cancelled') }}</a>
                    <a class="ui button tiny {{ $filter=='all'?'active':'' }}" href="{{ url('meetings?filter=all') }}">{{ __('All') }}</a>
                </div>

                <table width="100%" class="table table-striped table-hover" id="dataTables-example">
                    <thead>
                        <tr>
                            <th>{{ __('Topic') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Date & Time') }}</th>
                            <th>{{ __('Duration') }}</th>
                            <th>{{ __('Participants') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($meetings as $m)
                        <tr>
                            <td>{{ $m->topic }}</td>
                            <td><span class="category-pill cat-{{ $m->category }}">{{ ucfirst($m->category) }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($m->start_time)->format('D, d M Y g:i A') }}</td>
                            <td>{{ $m->duration }} min</td>
                            <td>{{ $m->participant_count }}</td>
                            <td><span class="badge-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
                            <td class="align-right">
                                <a href="{{ url('meetings/'.$m->id) }}" class="ui circular basic icon button tiny" title="View"><i class="icon eye"></i></a>
                                @if($m->status !== 'cancelled' && $m->join_url)
                                <a href="{{ $m->join_url }}" target="_blank" class="ui blue circular icon button tiny" title="Join"><i class="icon video"></i></a>
                                @endif
                                @if($m->status === 'scheduled')
                                <a href="{{ url('meetings/'.$m->id.'/cancel') }}" onclick="return confirm('Cancel this meeting?')" class="ui circular basic icon button tiny" title="Cancel"><i class="icon times"></i></a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7">{{ __('No meetings found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$('#dataTables-example').DataTable({responsive: true, pageLength: 15, lengthChange: true, searching: true, ordering: true});
</script>
@endsection