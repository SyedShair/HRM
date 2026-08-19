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
        <title>Edit My Profile | {{ $appName }}</title>
        <meta name="description" content="Workday edit my information.">
    @endsection

    @section('styles')
        <link href="{{ asset('/assets/vendor/air-datepicker/dist/css/datepicker.min.css') }}" rel="stylesheet">
    @endsection

    @section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="page-title">{{ __("Edit My Profile") }}
                    <a href="{{ url('personal/profile/view') }}" class="ui basic blue button mini offsettop5 float-right"><i class="ui icon chevron left"></i>{{ __("Return") }}</a>
                </h2>
            </div>    
        </div>
        <div class="col-md-12">
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
        </div>
        <div class="row">
            <form id="edit_personal_info_form" action="{{ url('personal/profile/update') }}" class="ui form custom" method="post"  enctype="multipart/form-data" >
            @csrf
                <div class="col-md-12 float-left">
                    <div class="box box-success">
                        <div class="box-header with-border">{{ __("Personal Information") }}</div>
                        <div class="box-body">
                            <div class="two fields">
                                <div class="field">
                                    <label>{{ __("First Name") }}</label>
                                    <input type="text" class="uppercase" name="firstname" value="@isset($person_details->firstname){{ $person_details->firstname }}@endisset">
                                </div>
                                <div class="field">
                                    <label>{{ __("Middle Name") }}</label>
                                    <input type="text" class="uppercase" name="mi" value="@isset($person_details->mi){{ $person_details->mi }}@endisset">
                                </div>
                            </div>
                            <div class="field">
                                <label>{{ __("Last Name") }}</label>
                                <input type="text" class="uppercase" name="lastname" value="@isset($person_details->lastname){{ $person_details->lastname }}@endisset">
                            </div>
                            <div class="field">
                                <label>{{ __("Gender") }}</label>
                                <select name="gender" class="ui dropdown uppercase">
                                    <option value="">Select Gender</option>
                                    <option value="MALE" @isset($person_details->gender) @if($person_details->gender == 'MALE') selected @endif @endisset>MALE</option>
                                    <option value="FEMALE" @isset($person_details->gender) @if($person_details->gender == 'FEMALE') selected @endif @endisset>FEMALE</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>{{ __("Civil Status") }}</label>
                                <select name="civilstatus" class="ui dropdown uppercase">
                                    <option value="">Select Civil Status</option>
                                    <option value="SINGLE" @isset($person_details->civilstatus) @if($person_details->civilstatus == 'SINGLE') selected @endif @endisset>SINGLE</option>
                                    <option value="MARRIED" @isset($person_details->civilstatus) @if($person_details->civilstatus == 'MARRIED') selected @endif @endisset>MARRIED</option>
                                    <option value="ANULLED" @isset($person_details->civilstatus) @if($person_details->civilstatus == 'ANULLED') selected @endif @endisset>ANULLED</option>
                                    <option value="WIDOWED" @isset($person_details->civilstatus) @if($person_details->civilstatus == 'WIDOWED') selected @endif @endisset>WIDOWED</option>
                                    <option value="LEGALLY SEPARATED" @isset($person_details->civilstatus) @if($person_details->civilstatus == 'LEGALLY SEPARATED') selected @endif @endisset>LEGALLY SEPARATED</option>
                                </select>
                            </div>
                            
                            <div class="two fields">
                            <div class="field">
                                <label>{{ __("Email Address (Personal)") }}</label>
                                <input type="email" name="emailaddress" value="@isset($person_details->emailaddress){{ $person_details->emailaddress }}@endisset"  class="lowercase">
                            </div>
                            
                             <div class="field">
                                <label>{{ __('Upload Profile photo') }}</label>
                                <input class="ui file upload" name="image" type="file" accept="image/png, image/jpeg, image/jpg" >
                            </div>
                            <div class="field">
                                <label>{{ __("Mobile Number") }}</label>
                                <input type="text" class="uppercase" name="mobileno" value="@isset($person_details->mobileno){{ $person_details->mobileno }}@endisset">
                            </div>
                            </div>
                            <div class="two fields">
                           
                            <div class="field">
                                <label>{{ __("Date of Birth") }}</label>
                                <input type="text" name="birthday" value="@isset($person_details->birthday){{ $person_details->birthday }}@endisset" class="airdatepicker" placeholder="Date">
                            </div>
                            </div>
                            <div class="field">
                                <label>{{ __("Place of Birth") }}</label>
                                <input type="text" class="uppercase" name="birthplace" value="@isset($person_details->birthplace){{ $person_details->birthplace }}@endisset" placeholder="City, Province, Country">
                            </div>
                            <div class="field">
                                <label>{{ __("Home Address") }}</label>
                                <input type="text" class="uppercase" name="homeaddress" value="@isset($person_details->homeaddress){{ $person_details->homeaddress }}@endisset" placeholder="House/Unit Number, Building, Street, City, Province, Country">
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
                            <br>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-12 float-left">
                    <div class="action align-right">
                        <button type="submit" name="submit" class="ui green button small"><i class="ui checkmark icon"></i> {{ __("Update") }}</button>
                        <a href="{{ url('personal/dashboard') }}" class="ui grey small button cancel"><i class="ui times icon"></i> {{ __("Cancel") }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @endsection

    @section('scripts')
    <script src="{{ asset('/assets/vendor/air-datepicker/dist/js/datepicker.min.js') }}"></script>
    <script src="{{ asset('/assets/vendor/air-datepicker/dist/js/i18n/datepicker.en.js') }}"></script>
    <script type="text/javascript">
        $('.airdatepicker').datepicker({ language: 'en', dateFormat: 'yyyy-mm-dd' });
         function validateFile() {
            var f = document.getElementById("imagefile").value;
            var d = f.lastIndexOf(".") + 1;
            var ext = f.substr(d, f.length).toLowerCase();
            if (ext == "jpg" || ext == "jpeg" || ext == "png") { } else {
                document.getElementById("imagefile").value="";
                $.notify({
                icon: 'ui icon times',
                message: "Please upload only jpg/jpeg and png image formats."},
                {type: 'danger',timer: 400});
            }
        }

    </script>
    @endsection