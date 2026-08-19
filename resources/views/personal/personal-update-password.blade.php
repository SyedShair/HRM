@extends('layouts.personal')
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
        <title>Update Personal Password | {{ $appName }}</title>
        <meta name="description" content="Workday update personal password">
    @endsection

    @section('content')
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="page-title">{{ __("Change Password") }}</h2>
            </div>    
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-content">
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
                    <form id="edit_personal_password_form" action="{{ url('personal/update/password') }}" class="ui form" method="post" accept-charset="utf-8">
                        @csrf
                        <div class="field">
                            <label>{{ __("Current Password") }}</label>
                            <input type="password" name="currentpassword" value="" placeholder="Enter Current Password">
                        </div>
                        <div class="field">
                            <label for="">{{ __("New Password") }}</label>
                            <input type="password" name="newpassword" value="" placeholder="Enter Password">
                        </div>
                        <div class="field">
                            <label for="">{{ __("Confirm Password") }}</label>
                            <input type="password" name="confirmpassword" value="" placeholder="Enter Password Confirmation">
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
                        </div>
                        <div class="box-footer">
                            <button class="ui positive small button" type="submit" name="submit"><i class="ui checkmark icon"></i> {{ __("Update") }}</button>
                            <a class="ui grey small button" href="{{ url('personal/dashboard') }}"><i class="ui times icon"></i> {{ __("Cancel") }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @endsection
    