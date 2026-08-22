@extends('layouts.default')

@section('meta')
    <title>{{ $meeting->topic }} | Jpingos</title>
@endsection

@section('styles')
<style>
    .meet-header { background:#1f2937; color:#fff; padding:20px; border-radius:6px 6px 0 0; }
    .meet-header h3 { margin:0; color:#fff; }
    .meet-meta { color:#d1d5db; font-size:13px; margin-top:6px; }
    .link-box { background:#f3f4f6; padding:10px 12px; border-radius:6px; font-family:monospace; font-size:12px; word-break:break-all; }
    .role-pill { padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }
    .role-interviewer { background:#ede9fe; color:#6d28d9; }
    .role-candidate { background:#fee2e2; color:#b91c1c; }
    .role-attendee { background:#e0f2fe; color:#0369a1; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="box">
            <div class="meet-header">
                <h3>{{ $meeting->topic }}</h3>
                <div class="meet-meta">
                    {{ \Carbon\Carbon::parse($meeting->start_time)->format('D, d M Y g:i A') }}
                    &middot; {{ $meeting->duration }} {{ __('min') }}
                    &middot; {{ ucfirst($meeting->category) }}
                    &middot; {{ __('Status') }}: {{ ucfirst($meeting->status) }}
                    @if($host)
                        &middot; {{ __('Host') }}: {{ $host->lastname }}, {{ $host->firstname }}
                    @endif
                </div>
            </div>

            <div class="box-body" style="padding:20px;">

                @if(session('success'))<div class="ui green message">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="ui red message">{{ session('error') }}</div>@endif

                @if($meeting->agenda)
                    <p><strong>{{ __('Agenda') }}:</strong> {{ $meeting->agenda }}</p>
                @endif

                <div class="ui two column stackable grid">
                    <div class="column">
                        <h4>{{ __('Join / Start Links') }}</h4>
                        @if($meeting->join_url)
                            <p><strong>{{ __('Join URL') }}</strong></p>
                            <div class="link-box">{{ $meeting->join_url }}</div>
                            <a href="{{ $meeting->join_url }}" target="_blank" class="ui blue button mini offsettop5"><i class="video icon"></i>{{ __('Join') }}</a>
                        @endif

                        @if($meeting->start_url)
                            <p style="margin-top:12px;"><strong>{{ __('Start URL (host only — keep private)') }}</strong></p>
                            <div class="link-box">{{ $meeting->start_url }}</div>
                            <a href="{{ $meeting->start_url }}" target="_blank" class="ui purple button mini offsettop5"><i class="video icon"></i>{{ __('Start as Host') }}</a>
                        @endif

                        @if($meeting->password)
                            <p style="margin-top:12px;"><strong>{{ __('Passcode') }}:</strong> {{ $meeting->password }}</p>
                        @endif
                    </div>

                    <div class="column">
                        <h4>{{ __('Recording') }}</h4>
                        @if($meeting->recording_url)
                            <div class="link-box">{{ $meeting->recording_url }}</div>
                            <a href="{{ $meeting->recording_url }}" target="_blank" class="ui teal button mini offsettop5"><i class="play icon"></i>{{ __('View Recording') }}</a>
                            @if($meeting->recording_password)
                                <p style="margin-top:8px;"><strong>{{ __('Recording passcode') }}:</strong> {{ $meeting->recording_password }}</p>
                            @endif
                            @if($meeting->transcript_url)
                                <p style="margin-top:8px;"><a href="{{ $meeting->transcript_url }}" target="_blank">{{ __('Download Transcript') }}</a></p>
                            @endif
                        @else
                            <p style="color:#6b7280;">{{ __('No recording saved yet.') }}</p>
                            @if($meeting->zoom_meeting_id)
                                <a href="{{ url('meetings/'.$meeting->id.'/sync-recording') }}" class="ui basic button mini"><i class="sync icon"></i>{{ __('Check for Recording') }}</a>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="ui divider"></div>

                <h4>{{ __('Participants') }}</h4>
                <table class="ui celled table">
                    <thead>
                        <tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Role') }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $p)
                        <tr>
                            <td>{{ $p->name }}</td>
                            <td>{{ $p->email }}</td>
                            <td><span class="role-pill role-{{ $p->role }}">{{ ucfirst($p->role) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="3">{{ __('No participants added.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="ui divider"></div>

                <h4>{{ __('Meeting Notes / Outcome') }}</h4>
                <form action="{{ url('meetings/'.$meeting->id.'/notes') }}" method="POST" class="ui form">
                    @csrf
                    <div class="field">
                        <textarea name="notes" rows="5" placeholder="{{ __('Interview feedback, decisions, action items...') }}">{{ $meeting->notes }}</textarea>
                    </div>
                    <button type="submit" class="ui primary button mini"><i class="save icon"></i>{{ __('Save Notes') }}</button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection