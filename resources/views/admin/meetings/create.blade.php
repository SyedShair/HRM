@extends('layouts.default')

@section('meta')
    <title>Schedule Meeting | Jpingos</title>
@endsection

@section('styles')
<link href="{{ asset('/assets/vendor/air-datepicker/dist/css/datepicker.min.css') }}" rel="stylesheet">
<link href="{{ asset('/assets/vendor/mdtimepicker/mdtimepicker.min.css') }}" rel="stylesheet">
<style>
    .datepicker { z-index: 999 !important; }
    .datepickers-container { z-index: 9999 !important; }
    .participant-row { border:1px solid #e5e7eb; border-radius:6px; padding:12px; margin-bottom:10px; background:#fafafa; }
    .participant-row .remove-participant { cursor:pointer; color:#dc2626; font-size:12px; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <h2 class="page-title">{{ __('Schedule Meeting') }}</h2>
    </div>

    @if(session('error'))<div class="ui red message">{{ session('error') }}</div>@endif

    <div class="row">
        <div class="box box-success">
            <div class="box-body">
                <form action="{{ url('meetings') }}" method="POST" class="ui form">
                    @csrf

                    <div class="two fields">
                        <div class="field">
                            <label>{{ __('Topic') }}</label>
                            <input type="text" name="topic" placeholder="e.g. Interview - Backend Developer" value="{{ old('topic') }}" required>
                        </div>
                        <div class="field">
                            <label>{{ __('Category') }}</label>
                            <select name="category" class="ui dropdown" required>
                                <option value="interview">{{ __('Interview / Hiring') }}</option>
                                <option value="internal">{{ __('Internal') }}</option>
                                <option value="client">{{ __('Client') }}</option>
                                <option value="other">{{ __('Other') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label>{{ __('Agenda / Notes') }}</label>
                        <textarea name="agenda" rows="3" placeholder="What is this meeting about?">{{ old('agenda') }}</textarea>
                    </div>

                    <div class="three fields">
                        <div class="field">
                            <label>{{ __('Date') }}</label>
                            <input type="text" name="start_date" class="airdatepicker" autocomplete="off" required>
                        </div>
                        <div class="field">
                            <label>{{ __('Time') }}</label>
                            <input type="text" name="start_time" class="jtimepicker" autocomplete="off" required>
                        </div>
                        <div class="field">
                            <label>{{ __('Duration (minutes)') }}</label>
                            <input type="number" name="duration" value="30" min="5" max="600" required>
                        </div>
                    </div>

                    <div class="two fields">
                        <div class="field">
                            <label>{{ __('Timezone') }}</label>
                            <input type="text" name="timezone" value="Europe/London">
                        </div>
                        <div class="field">
                            <label>{{ __('Host (Employee)') }}</label>
                            <select name="host_employee_id" class="ui search dropdown">
                                <option value="">{{ __('Select host') }}</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->lastname }}, {{ $emp->firstname }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="ui divider"></div>

                    <h4>{{ __('Participants') }}</h4>
                    <p style="color:#6b7280; font-size:12px;">{{ __('Add existing employees or outside guests (e.g. job candidates) by name and email.') }}</p>

                    <div id="participant-list"></div>

                    <button type="button" class="ui basic button" id="add-participant"><i class="plus icon"></i>{{ __('Add Participant') }}</button>

                    <div class="ui divider"></div>

                    <button type="submit" class="ui positive button"><i class="video icon"></i> {{ __('Create Zoom Meeting') }}</button>
                    <a href="{{ url('meetings') }}" class="ui grey button">{{ __('Cancel') }}</a>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Participant row template -->
<script type="text/template" id="participant-template">
    <div class="participant-row">
        <div class="four fields">
            <div class="field">
                <label>{{ __('Employee (optional)') }}</label>
                <select class="ui search dropdown participant-employee">
                    <option value="">{{ __('Outside guest') }}</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" data-name="{{ $emp->lastname }}, {{ $emp->firstname }}">{{ $emp->lastname }}, {{ $emp->firstname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>{{ __('Name') }}</label>
                <input type="text" class="participant-name" placeholder="Full name">
            </div>
            <div class="field">
                <label>{{ __('Email') }}</label>
                <input type="email" class="participant-email" placeholder="email@example.com">
            </div>
            <div class="field">
                <label>{{ __('Role') }}</label>
                <select class="ui dropdown participant-role">
                    <option value="attendee">{{ __('Attendee') }}</option>
                    <option value="interviewer">{{ __('Interviewer') }}</option>
                    <option value="candidate">{{ __('Candidate') }}</option>
                </select>
            </div>
        </div>
        <span class="remove-participant"><i class="trash alternate outline icon"></i> {{ __('Remove') }}</span>
    </div>
</script>
@endsection

@section('scripts')
<script src="{{ asset('/assets/vendor/air-datepicker/dist/js/datepicker.min.js') }}"></script>
<script src="{{ asset('/assets/vendor/air-datepicker/dist/js/i18n/datepicker.en.js') }}"></script>
<script src="{{ asset('/assets/vendor/mdtimepicker/mdtimepicker.min.js') }}"></script>
<script>
    $('.airdatepicker').datepicker({ language: 'en', dateFormat: 'yyyy-mm-dd' });
    $('.jtimepicker').mdtimepicker({format:'h:mm tt', theme: 'blue', hourPadding: true});
    $('.ui.dropdown').dropdown();

    let participantIndex = 0;

    function addParticipantRow() {
        let html = $('#participant-template').html();
        let $row = $(html);
        let idx = participantIndex++;

        $row.find('.participant-employee').attr('name', 'participant_employee_id['+idx+']');
        $row.find('.participant-name').attr('name', 'participant_name['+idx+']');
        $row.find('.participant-email').attr('name', 'participant_email['+idx+']');
        $row.find('.participant-role').attr('name', 'participant_role['+idx+']');

        $('#participant-list').append($row);
        $row.find('.ui.dropdown').dropdown();
    }

    $('#add-participant').click(function () { addParticipantRow(); });

    $(document).on('change', '.participant-employee', function () {
        let $row = $(this).closest('.participant-row');
        let selected = $(this).find('option:selected');
        if ($(this).val()) {
            $row.find('.participant-name').val(selected.data('name'));
        }
    });

    $(document).on('click', '.remove-participant', function () {
        $(this).closest('.participant-row').remove();
    });

    // start with one empty row
    addParticipantRow();
</script>
@endsection