@extends('layouts.default')
 @php
            // Branding: pulled from the Settings page (App name / logo).
            // Falls back to existing static defaults if nothing has been
            // configured yet, so this is safe even before anyone touches
            // the new fields.
            $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
            $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
            $appLogo = !empty($appSettings->app_logo)
                ? asset('storage/'.$appSettings->app_logo)
                : asset('/assets/images/img/logo.png');
        @endphp
@section('meta')
    <title>Edit Department | {{ $appName }}</title>
    <meta name="description" content="Edit department details.">
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title uppercase">{{ __("Edit Department") }}</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
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

                    <form id="edit_department_form" action="{{ url('fields/department/update') }}" class="ui form" method="post" accept-charset="utf-8">
                        @csrf
                        <input type="hidden" name="id" value="{{ $e_id }}">

                        <div class="field">
                            <label>{{ __("Company") }}</label>
                            <select name="company" class="ui search dropdown">
                                <option value="">{{ __("Select Company") }}</option>
                                @isset($company)
                                    @foreach ($company as $comp)
                                        <option value="{{ $comp->id }}"
                                            {{ (string) old('company', $department->company_id) === (string) $comp->id ? 'selected' : '' }}>
                                            {{ $comp->company }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="field">
                            <label>{{ __("Department Name") }}</label>
                            <input class="uppercase" name="department" value="{{ old('department', $department->department) }}" type="text">
                        </div>

                        <div class="field">
                            <div class="ui error message">
                                <i class="close icon"></i>
                                <div class="header"></div>
                                <ul class="list">
                                    <li class=""></li>
                                </ul>
                            </div>
                        </div>
                        <div class="actions">
                            <button type="submit" class="ui positive button small"><i class="ui icon check"></i> {{ __("Save Changes") }}</button>
                            <a href="{{ url('fields/department') }}" class="ui grey button small"><i class="ui times icon"></i> {{ __("Cancel") }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$('.ui.dropdown').dropdown();
</script>
@endsection