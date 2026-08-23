@extends('layouts.default')

@section('meta')
    <title>Meetings | Jpingos</title>
    <meta name="description" content="Schedule and manage Zoom meetings for employees and outside guests">
@endsection

@section('styles')
<style>
    .badge-scheduled { color:#2563eb; font-weight:600; }
    .badge-started { color:#d97706; font-weight:600; }
    .badge-ended { color:#16a34a; font-weight:600; }
    .badge-cancelled { color:#dc2626; font-weight:600; }
    .category-pill { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; white-space:nowrap; }
    .cat-interview { background:#ede9fe; color:#6d28d9; }
    .cat-internal { background:#e0f2fe; color:#0369a1; }
    .cat-client { background:#dcfce7; color:#166534; }
    .cat-other { background:#f3f4f6; color:#4b5563; }

    /* ================= RESPONSIVE HEADER ================= */
    .meetings-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        width: 100%;
    }
    .meetings-header .page-title {
        margin: 0;
    }
    .meetings-header a.ui.button {
        margin: 0;
        white-space: nowrap;
    }

    /* ================= RESPONSIVE FILTER TABS ================= */
    .filter-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 15px;
    }
    .filter-tabs a.ui.button {
        margin: 0;
    }
    .filter-tabs a.active { background:#1f2937 !important; color:#fff !important; }

    /* ================= RESPONSIVE TABLE ================= */
    /* Guaranteed fallback regardless of whether DataTables' own
       Responsive extension is actually bundled in datatables.min.js -
       this wraps the table in a horizontally-scrollable container so
       nothing is ever cut off or forces the whole page to scroll
       sideways, without fighting DataTables' own DOM/markup at all. */
    .table-responsive-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 768px) {
        /* DataTables' own generated controls (length select, search
           box, info text, pagination) float side-by-side by default
           and don't wrap gracefully on narrow screens - stack them
           full-width instead of letting them overflow or overlap. */
        #dataTables-example_wrapper .dataTables_length,
        #dataTables-example_wrapper .dataTables_filter,
        #dataTables-example_wrapper .dataTables_info,
        #dataTables-example_wrapper .dataTables_paginate {
            float: none !important;
            width: 100%;
            text-align: left !important;
            margin-bottom: 10px;
        }
        #dataTables-example_wrapper .dataTables_filter input {
            width: 100%;
            margin-left: 0 !important;
            box-sizing: border-box;
        }
        #dataTables-example_wrapper .dataTables_length select {
            width: auto;
        }

        table#dataTables-example td,
        table#dataTables-example th {
            font-size: 13px;
            padding: 8px 6px;
            white-space: nowrap;
        }
    }

    @media (max-width: 480px) {
        .meetings-header a.ui.button.mini {
            width: 100%;
            text-align: center;
        }
        .filter-tabs a.ui.button.tiny {
            flex: 1 1 calc(50% - 8px);
            text-align: center;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="meetings-header">
            <h2 class="page-title">{{ __('Meetings') }}</h2>
            <a href="{{ url('meetings/create') }}" class="ui positive button mini">
                <i class="ui icon video"></i>{{ __('Schedule Meeting') }}
            </a>
        </div>
    </div>

    @if(session('success'))<div class="ui green message">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="ui red message">{{ session('error') }}</div>@endif

    <div class="row">
        <div class="box box-success">
            <div class="box-body">
                <div class="filter-tabs">
                    <a class="ui button tiny {{ $filter=='upcoming'?'active':'' }}" href="{{ url('meetings?filter=upcoming') }}">{{ __('Upcoming') }}</a>
                    <a class="ui button tiny {{ $filter=='past'?'active':'' }}" href="{{ url('meetings?filter=past') }}">{{ __('Past') }}</a>
                    <a class="ui button tiny {{ $filter=='cancelled'?'active':'' }}" href="{{ url('meetings?filter=cancelled') }}">{{ __('Cancelled') }}</a>
                    <a class="ui button tiny {{ $filter=='all'?'active':'' }}" href="{{ url('meetings?filter=all') }}">{{ __('All') }}</a>
                </div>

                <div class="table-responsive-wrapper">
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
                                    {{-- Confirmation now goes through the same SweetAlert2 modal
                                         pattern used for message deletion in the chat feature,
                                         instead of a native confirm() popup. href still points at
                                         the real cancel URL as a no-JS fallback - if the click
                                         handler below can't run for any reason, this still works
                                         as a plain (unconfirmed) link rather than a dead one. --}}
                                    <a href="{{ url('meetings/'.$m->id.'/cancel') }}"
                                       class="ui circular basic icon button tiny cancel-meeting-btn"
                                       data-topic="{{ $m->topic }}"
                                       title="Cancel"><i class="icon times"></i></a>
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
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$('#dataTables-example').DataTable({responsive: true, pageLength: 15, lengthChange: true, searching: true, ordering: true});

// Cancel-meeting confirmation modal - replaces the native confirm()
// popup. Reads the target URL straight off the link's own href and the
// meeting's topic from its data attribute (Blade-escaped, so no manual
// JS string-escaping needed), rather than building the confirmation
// text via inline onclick string concatenation.
$(document).on('click', '.cancel-meeting-btn', function (e) {
    e.preventDefault();

    var url = $(this).attr('href');
    var topic = $(this).data('topic');

    Swal.fire({
        title: 'Cancel this meeting?',
        text: 'This will cancel "' + topic + '". This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, cancel it',
        cancelButtonText: 'Keep meeting'
    }).then(function (result) {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
});
</script>
@endsection