@extends('layouts.default')
 @php
    $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
    $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
@endphp
@section('meta')
    <title>Edit Schedule | {{ $appName }}</title>
@endsection

@section('styles')
<link href="{{ asset('/assets/vendor/mdtimepicker/mdtimepicker.min.css') }}" rel="stylesheet">
<link href="{{ asset('/assets/vendor/air-datepicker/dist/css/datepicker.min.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title">{{ __('Edit Schedule') }}</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-body">

                    @if ($errors->any())
                        <div class="ui error message">
                            <i class="close icon"></i>
                            <div class="header">{{ __("There were some errors with your submission") }}</div>
                            <ul class="list">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p style="color:#6b7280; font-size:13px; margin-bottom:16px;">
                        {{ __('Updating and saving this schedule will automatically rebuild the full weekly rota to match the open/close time and rest days below.') }}
                    </p>

                    <form id="edit_schedule_form" action="{{ url('schedules/update') }}" class="ui form" method="post" accept-charset="utf-8">
                        @csrf
                        <input type="hidden" name="id" value="{{ $e_id }}">

                        <div class="field">
                            <label>{{ __('Employee') }}</label>
                            <input type="text" value="{{ $s->employee }}" readonly>
                        </div>

                        <div class="two fields">
                            <div class="field">
                                <label>{{ __('Company Open Time') }}</label>
                                <input type="text" name="intime" class="jtimepicker" value="{{ $s->intime }}">
                            </div>
                            <div class="field">
                                <label>{{ __('Company Close Time') }}</label>
                                <input type="text" name="outime" class="jtimepicker" value="{{ $s->outime }}">
                            </div>
                        </div>

                        <div class="field">
                            <label>{{ __('Schedule Valid From') }}</label>
                            <input type="text" name="datefrom" class="airdatepicker" value="{{ $s->datefrom }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Schedule Valid To') }}</label>
                            <input type="text" name="dateto" class="airdatepicker" value="{{ $s->dateto }}">
                        </div>

                        <div class="field">
                            <label>{{ __('Total hours (per day)') }}</label>
                            <input type="text" name="hours" value="{{ $s->hours }}">
                        </div>

                        <div class="grouped fields field">
                            <label>{{ __('Choose Rest days') }}</label>
                            @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                                <div class="field">
                                    <div class="ui checkbox">
                                        <input type="checkbox" name="restday[]" value="{{ $day }}" {{ in_array($day, $r) ? 'checked' : '' }}>
                                        <label>{{ __($day) }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="actions">
                            <a href="{{ url('schedules') }}" class="ui button small">{{ __('Cancel') }}</a>
                            <button type="submit" class="ui positive button small">
                                <i class="ui icon check"></i> {{ __('Update & Rebuild Weekly Rota') }}
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="{{ asset('/assets/vendor/air-datepicker/dist/js/datepicker.min.js') }}"></script>
<script src="{{ asset('/assets/vendor/air-datepicker/dist/js/i18n/datepicker.en.js') }}"></script>
<script src="{{ asset('/assets/vendor/mdtimepicker/mdtimepicker.min.js') }}"></script>
<script>
$('.airdatepicker').datepicker({ language: 'en', dateFormat: 'yyyy-mm-dd' });

@isset($tf)
    @if($tf == 1)
        $('.jtimepicker').mdtimepicker({format:'h:mm tt', theme: 'blue', hourPadding: true});
    @else
        $('.jtimepicker').mdtimepicker({format:'hh:mm', theme: 'blue', hourPadding: true});
    @endif
@endisset
</script>
@endsection