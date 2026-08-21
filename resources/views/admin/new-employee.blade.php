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
    <title>New Employee | {{ $appName }}</title>
    <meta name="description" content="Workday add new employee, delete employee, edit employee">
@endsection

@section('styles')
    <link href="{{ asset('/assets/vendor/air-datepicker/dist/css/datepicker.min.css') }}" rel="stylesheet">
    <style>
        /* ================= PROFESSIONAL FORM STYLING ================= */

        .box.box-success {
            border-radius: 8px;
            border-top: 3px solid #16a34a;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .box-header.with-border {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.02em;
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            background-color: #fafafa;
        }

        .box-body {
            padding: 20px;
        }

        .box-body .field > label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
            display: block;
        }

        .box-body input,
        .box-body select,
        .box-body textarea {
            font-size: 14px;
        }

        .ui.dividing.header {
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 24px;
            margin-bottom: 12px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
        }

        .action.align-right {
            text-align: right;
            padding: 16px 0;
        }

        input[readonly] {
            background-color: #f3f4f6;
            cursor: not-allowed;
        }

        /* ================= ADDRESS HISTORY ================= */

        #address-history-intro {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 14px;
            line-height: 1.5;
        }

        .address-entry {
            position: relative;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px 14px 6px 14px;
            margin-bottom: 14px;
            background: #fcfcfd;
        }

        .address-entry .entry-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #16a34a;
            margin-bottom: 8px;
        }

        .address-entry .remove-entry {
            position: absolute;
            top: 10px;
            right: 12px;
            cursor: pointer;
            color: #9ca3af;
            font-size: 13px;
        }
        .address-entry .remove-entry:hover {
            color: #dc2626;
        }

        .current-address-toggle {
            font-size: 13px;
            color: #374151;
            margin: 4px 0 10px 0;
        }

        #add-address-entry {
            margin-bottom: 16px;
        }

        #address-coverage-status {
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid transparent;
        }
        #address-coverage-status.status-incomplete {
            background: #fef3c7;
            border-color: #fde68a;
            color: #92400e;
        }
        #address-coverage-status.status-complete {
            background: #dcfce7;
            border-color: #bbf7d0;
            color: #166534;
        }
        #address-coverage-status.status-error {
            background: #fee2e2;
            border-color: #fecaca;
            color: #991b1b;
        }

        #address-coverage-bar-track {
            height: 6px;
            border-radius: 3px;
            background: #e5e7eb;
            margin-top: 8px;
            overflow: hidden;
        }
        #address-coverage-bar-fill {
            height: 100%;
            width: 0%;
            background: #16a34a;
            transition: width 0.2s ease;
        }

        .reason-field {
            display: none;
        }
        .reason-field.visible {
            display: block;
        }
    </style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title">{{ __('Employee Profile') }}</h2>
        </div>    
    </div>

    <div class="row">
        <div class="col-md-12">
        @if ($errors->any())
        <div class="ui error message">
            <i class="close icon"></i>
            <div class="header">{{ __('There were some errors with your submission') }}</div>
            <ul class="list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        </div>
        <form id="add_employee_form" action="{{ url('employee/add') }}" class="ui form custom" method="post" accept-charset="utf-8" enctype="multipart/form-data">
        @csrf
            <div class="col-md-6 float-left">
                <div class="box box-success">
                    <div class="box-header with-border">{{ __('Personal Information') }}</div>
                    <div class="box-body">
                        <!-- Personal Information Fields -->
                        <div class="two fields">
                            <div class="field">
                                <label>{{ __('First Name') }}</label>
                                <input type="text" class="uppercase" name="firstname" value="{{ old('firstname') }}">
                            </div>
                            <div class="field">
                                <label>{{ __('Middle Name') }}</label>
                                <input type="text" class="uppercase" name="mi" value="{{ old('mi') }}">
                            </div>
                        </div>
                        <div class="field">
                            <label>{{ __('Last Name') }}</label>
                            <input type="text" class="uppercase" name="lastname" value="{{ old('lastname') }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Gender') }}</label>
                            <select name="gender" class="ui dropdown uppercase">
                                <option value="">Select Gender</option>
                                <option value="MALE" @selected(old('gender') == 'MALE')>MALE</option>
                                <option value="FEMALE" @selected(old('gender') == 'FEMALE')>FEMALE</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>{{ __('Civil Status') }}</label>
                            <select name="civilstatus" class="ui dropdown uppercase">
                                <option value="">Select Civil Status</option>
                                <option value="SINGLE" @selected(old('civilstatus') == 'SINGLE')>SINGLE</option>
                                <option value="MARRIED" @selected(old('civilstatus') == 'MARRIED')>MARRIED</option>
                                <option value="ANULLED" @selected(old('civilstatus') == 'ANULLED')>ANULLED</option>
                                <option value="WIDOWED" @selected(old('civilstatus') == 'WIDOWED')>WIDOWED</option>
                                <option value="LEGALLY SEPARATED" @selected(old('civilstatus') == 'LEGALLY SEPARATED')>LEGALLY SEPARATED</option>
                            </select>
                        </div>
                      
                        <div class="two fields">
                            <div class="field">
                                <label>{{ __('Email Address (Personal)') }}</label>
                                <input type="email" name="emailaddress" value="{{ old('emailaddress') }}" class="lowercase">
                            </div>
                            <div class="field">
                                <label>{{ __('Mobile Number') }}</label>
                                <input type="text" class="" name="mobileno" value="{{ old('mobileno') }}">
                            </div>
                        </div>
                        <div class="two fields">
                            <div class="field">
                                <label>{{ __('Height') }}</label>
                                <input type="text" name="height" value="{{ old('height') }}" placeholder="e.g. 175cm">
                            </div>
                            <div class="field">
                                <label>{{ __('Weight') }}</label>
                                <input type="text" name="weight" value="{{ old('weight') }}" placeholder="e.g. 70kg">
                            </div>
                        </div>
                        <div class="two fields">
                            <div class="field">
                                <label>{{ __('Age') }}</label>
                                <input type="text" name="age" value="{{ old('age') }}" placeholder="00" readonly>
                            </div>
                            <div class="field">
                                <label>{{ __('Date of Birth') }}</label>
                                <input type="text" name="birthday" value="{{ old('birthday') }}" class="airdatepicker" data-position="top right" placeholder="Date"> 
                            </div>
                        </div>
                        <div class="field">
                            <label>{{ __('Passport No') }}</label>
                            <input type="text" class="uppercase" name="nationalid" value="{{ old('nationalid') }}" placeholder="">
                        </div>
                        <div class="two fields">
                            <div class="field">
                                <label>{{ __('Passport Issue Date') }}</label>
                                <input type="date" name="idissuedate" value="{{ old('idissuedate') }}">
                            </div>
                            <div class="field">
                                <label>{{ __('Passport Expiry Date') }}</label>
                                <input type="date" name="idexpirydate" value="{{ old('idexpirydate') }}">
                            </div>
                        </div>
                        <div class="field">
                            <label>{{ __('Share Code') }}</label>
                            <input type="text" class="uppercase" name="sharecode" value="{{ old('sharecode') }}" placeholder="Leave blank if not applicable">
                        </div>
                        <div class="field">
                            <label>{{ __('Share Code Expiry Date') }}</label>
                            <input type="date" name="sharecodeexpiry" value="{{ old('sharecodeexpiry') }}">
                        </div>
                        <div class="field">
                            <label>{{ __('National Insurance') }}</label>
                            <input type="text" class="uppercase" name="ni" value="{{ old('ni') }}" placeholder="">
                        </div>
                        <div class="field">
                            <label>{{ __('Place of Birth') }}</label>
                            <input type="text" class="uppercase" name="birthplace" value="{{ old('birthplace') }}" placeholder="City, Province, Country">
                        </div>
                        <div class="field">
                            <label>{{ __('Upload Profile Photo') }}</label>
                            <input class="ui file upload" value="" id="imagefile" name="image" type="file" accept="image/png, image/jpeg, image/jpg" onchange="validateFile()">
                        </div>
                        <br>
                    </div>
                </div>

                <!-- ============== 5-YEAR ADDRESS HISTORY ============== -->
                <div class="box box-success">
                    <div class="box-header with-border">{{ __('Address History (Last 5 Years)') }}</div>
                    <div class="box-body">
                        <p id="address-history-intro">
                            {{ __('Please provide a continuous address history covering the last 5 years, as required for right-to-work verification. Add each address you\'ve lived at, starting with your current one, with a supporting document reference and (where available) a scanned copy of that document. There should be no gaps between addresses. Partial history can be saved now and completed later.') }}
                        </p>

                        <div id="address-coverage-status" class="status-incomplete">
                            <span id="address-coverage-text">{{ __('0 years 0 months covered of 5 years required.') }}</span>
                            <div id="address-coverage-bar-track">
                                <div id="address-coverage-bar-fill"></div>
                            </div>
                        </div>

                        <div id="address-entries"></div>

                        <button type="button" id="add-address-entry" class="ui button small basic">
                            <i class="ui plus icon"></i>{{ __('Add Another Address') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6 float-left">
                <div class="box box-success">
                    <div class="box-header with-border">{{ __('Employee Details') }}</div>
                    <div class="box-body">
                        <h4 class="ui dividing header">{{ __('Designation') }}</h4>
                        <div class="field">
                            <label>{{ __('Company') }}</label>
                            {{-- Hidden input posts the company's id (tbl_form_company.id).
                                 EmployeesController@add resolves the display name from
                                 this id when writing tbl_company_data. --}}
                            <div class="ui search dropdown selection uppercase company-dropdown">
                                <input type="hidden" name="company_id">
                                <i class="dropdown icon" tabindex="1"></i>
                                <div class="default text">{{ __('Select Company') }}</div>
                                <div class="menu">
                                @isset($company)
                                    @foreach ($company as $data)
                                        <div class="item" data-value="{{ $data->id }}">{{ $data->company }}</div>
                                    @endforeach
                                @endisset
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label>{{ __('Department') }}</label>
                            {{-- Department options are loaded live via AJAX from
                                 tbl_form_department, filtered by the selected company's id
                                 (see EmployeesController@departmentsByCompany). The menu
                                 starts empty and is rebuilt in JS on every company change. --}}
                            <div class="ui search dropdown selection uppercase department-dropdown disabled">
                                <input type="hidden" name="department">
                                <i class="dropdown icon" tabindex="1"></i>
                                <div class="default text">{{ __('Select Company First') }}</div>
                                <div class="menu"></div>
                            </div>
                        </div>
                        <div class="field">
                            <label>{{ __('Job Title / Position') }}</label>
                            {{-- NOTE: this dropdown's data-value is the job title's
                                 TEXT (e.g. "CHEF"), not an id - it posts as
                                 jobposition and is saved as free text on
                                 tbl_company_data.jobposition. EmployeesController@add
                                 separately resolves the real jobtitle_id FK server-side
                                 by matching this text + the selected department against
                                 tbl_form_jobtitle, for Job Duties lookups. --}}
                            <div class="ui search dropdown selection uppercase jobposition">
                                <input type="hidden" name="jobposition">
                                <i class="dropdown icon" tabindex="1"></i>
                                <div class="default text">{{ __('Select Department First') }}</div>
                                <div class="menu">
                                @isset($jobtitle)
                                    @isset($department)
                                        @foreach ($jobtitle as $data)
                                            @foreach ($department as $dept)
                                                @if($dept->id == $data->dept_code)
                                                    <div class="item hide disabled" data-value="{{ $data->jobtitle }}" data-dept="{{ $dept->department }}">{{ $data->jobtitle }}</div>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    @endisset
                                @endisset
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label>{{ __('Job Duties') }}</label>
                            <textarea id="jobduties" class="uppercase" name="jobduties">{{ old('jobduties') }}</textarea>
                        </div>

                        <h4 class="ui dividing header">{{ __('Visa & Compliance') }}</h4>

                        <div class="field">
                            <label>{{ __('COS Certificate Number') }}</label>
                            <input type="text" class="uppercase" name="COSCertificateNo" value="{{ old('COSCertificateNo') }}" placeholder="e.g. C2G--------">
                        </div>
                        <div class="field">
                            <label>{{ __('COS Expiry') }}</label>
                            <input type="date" name="cosexpiry" value="{{ old('cosexpiry') }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Visa Status') }}</label>
                            <input type="text" class="uppercase" name="visastatus" value="{{ old('visastatus') }}" placeholder="e.g. Work Visa">
                        </div>
                        <div class="two fields">
                            <div class="field">
                                <label>{{ __('Visa Issue Date') }}</label>
                                <input type="date" name="visastart" value="{{ old('visastart') }}">
                            </div>
                            <div class="field">
                                <label>{{ __('Visa Expiry Date') }}</label>
                                <input type="date" name="visaend" value="{{ old('visaend') }}">
                            </div>
                        </div>
                        <div class="field">
                            <label>{{ __('Job SOC Code') }}</label>
                            <input type="text" class="uppercase" name="jobtype" value="{{ old('jobtype') }}" placeholder="e.g. Chef">
                        </div>

                        <!-- ID Number, auto-generated on page load but editable -->
                        <div class="field">
                            <label>{{ __('ID Number') }}</label>
                            <input type="text" class="uppercase" name="idno" id="idno" value="{{ old('idno') }}">
                        </div>

                        <h4 class="ui dividing header">{{ __('Next of Kin') }}</h4>

                        <div class="field">
                            <label>{{ __('Next of Kin Number') }}</label>
                            <input type="text" class="uppercase" name="kinno" value="{{ old('kinno') }}" placeholder="e.g. Uncle's phone number">
                        </div>
                        <div class="field">
                            <label>{{ __('Next of Kin Name and Relationship') }}</label>
                            <input type="text" class="uppercase" name="kinname" value="{{ old('kinname') }}" placeholder="e.g. Name (Uncle)">
                        </div>

                        <div class="field">
                            <label>{{ __('Work Checks') }}</label>
                            <textarea id="workchecks" name="workchecks">{{ old('workchecks') }}</textarea>
                        </div>

                        <h4 class="ui dividing header">{{ __('Employment Information') }}</h4>

                        <div class="field">
                            <label>{{ __('Email Address (Company)') }}</label>
                            <input type="email" name="companyemail" value="{{ old('companyemail') }}" class="lowercase">
                        </div>
                        <div class="field">
                            <label>{{ __('Leave Group') }}</label>
                            <select name="leaveprivilege" class="ui dropdown uppercase">
                                <option value="">Select Leave Privilege</option>
                                @isset($leavegroup) 
                                    @foreach($leavegroup as $lg)
                                        <option value="{{ $lg->id }}" @selected(old('leaveprivilege') == $lg->id)>{{ $lg->leavegroup }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="two fields">
                            <div class="field">
                                <label>{{ __('Per Hour Pay') }}</label>
                                <input type="number" name="perhourpay" value="{{ old('perhourpay') }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="field">
                                <label>{{ __('Account Pay') }}</label>
                                <input type="number" name="accountpay" value="{{ old('accountpay') }}" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        <div class="field">
                            <label>{{ __('Employment Type') }}</label>
                            <select name="employmenttype" class="ui dropdown uppercase">
                                <option value="">Select Type</option>
                                <option value="Regular" @selected(old('employmenttype') == 'Regular')>Regular</option>
                                <option value="Part-Time" @selected(old('employmenttype') == 'Part-Time')>Part-Time</option>
                                <option value="Trainee" @selected(old('employmenttype') == 'Trainee')>Trainee</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>{{ __('Employment Status') }}</label>
                            {{-- Value is "Archived" (not "Archive") so this matches
                                 every other place employmentstatus is set/read:
                                 ProfileController::archive(), the Edit Employee form,
                                 and EmployeesController::index()'s Archived summary
                                 card. Previously this dropdown alone posted "Archive",
                                 which meant a brand-new employee saved directly as
                                 archived here would silently never be counted by that
                                 card. --}}
                            <select name="employmentstatus" id="employmentstatus-select" class="ui dropdown uppercase">
                                <option value="">Select Status</option>
                                <option value="Active" @selected(old('employmentstatus') == 'Active')>Active</option>
                                <option value="Archived" @selected(old('employmentstatus') == 'Archived')>Archived</option>
                            </select>
                        </div>
                        <div class="field reason-field" id="reason-field">
                            <label>{{ __('Reason') }}</label>
                            <textarea name="reason" rows="2" class="uppercase" placeholder="Reason for archiving, if applicable">{{ old('reason') }}</textarea>
                        </div>
                        <div class="two fields">
                            <div class="field">
                                <label>{{ __('Official Start Date') }}</label>
                                <input type="text" name="startdate" value="{{ old('startdate') }}" class="airdatepicker uppercase" data-position="top right" placeholder="Date">
                            </div>
                            <div class="field">
                                <label>{{ __('Date Regularized') }}</label>
                                <input type="text" name="dateregularized" value="{{ old('dateregularized') }}" class="airdatepicker uppercase" data-position="top right" placeholder="Date">
                            </div>
                        </div>
                        <br>
                    </div>
                </div>
            </div>
            

            <div class="col-md-12 float-left">
                <div class="ui error message">
                    <i class="close icon"></i>
                    <div class="header"></div>
                    <ul class="list">
                        <li class=""></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-12 float-left">
                <div class="action align-right">
                    <button type="submit" name="submit" id="submit_employee_btn" class="ui green button small"><i class="ui checkmark icon"></i>{{ __('Save') }}</button>
                    <a href="{{ url('employees') }}" class="ui grey button small"><i class="ui times icon"></i>{{ __('Cancel') }}</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('/assets/vendor/air-datepicker/dist/js/datepicker.min.js') }}"></script>
<script src="{{ asset('/assets/vendor/air-datepicker/dist/js/i18n/datepicker.en.js') }}"></script>
<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('jobduties');
    CKEDITOR.replace('workchecks');
</script>
<script type="text/javascript">
$('.airdatepicker').datepicker({ 
    language: 'en', 
    dateFormat: 'yyyy-mm-dd', 
    autoClose: true,
    onSelect: function(formattedDate, date, inst) {
        calculateAge(date);
    }
});

/* ================================================================
   COMPANY -> DEPARTMENT (live AJAX) -> JOB TITLE CASCADE
   Company dropdown value is the company's id (tbl_form_company.id) -
   matches EmployeesController@departmentsByCompany, which filters
   tbl_form_department by company_id.

   Dropdowns are initialized bare (no onChange option passed at init,
   no chained 'change values'/'set value' API calls after init).
   Selection handling is done via plain jQuery .on('change', ...)
   bound to each dropdown's hidden <input>, since both Semantic UI
   and Fomantic UI fire a native 'change' event there on selection,
   regardless of which version/library is actually loaded. This is
   what avoids the "The method you called is not defined" errors.
   ================================================================ */
$('.company-dropdown').dropdown();
$('.department-dropdown').dropdown();
$('.jobposition').dropdown();
$('.jobposition').addClass('disabled');

$(document).on('change', '.company-dropdown input[name="company_id"]', function () {
    var value = $(this).val();

    resetDepartmentDropdown();
    resetJobpositionDropdown();

    if (!value) {
        return;
    }

    $('.department-dropdown').addClass('loading');

    $.ajax({
        url: '{{ url("employees/departments-by-company") }}',
        type: 'GET',
        data: { company_id: value },
        dataType: 'json',
        success: function (departments) {
            var $menu = $('.department-dropdown .menu');
            $menu.empty();

            departments.forEach(function (dept) {
                $menu.append(
                    $('<div class="item"></div>')
                        .attr('data-value', dept.department)
                        .text(dept.department)
                );
            });

            $('.department-dropdown').removeClass('loading disabled');
            $('.department-dropdown .default.text').text('{{ __('Select Department') }}');

            // Re-scan the DOM so the freshly injected .item nodes are wired up
            $('.department-dropdown').dropdown('refresh');

            if (departments.length === 0) {
                $.notify({
                    icon: 'ui icon info',
                    message: 'No departments found for this company yet.'
                }, { type: 'info', timer: 500 });
            }
        },
        error: function () {
            $('.department-dropdown').removeClass('loading');
            $.notify({
                icon: 'ui icon times',
                message: 'Could not load departments for this company. Please try again.'
            }, { type: 'danger', timer: 500 });
        }
    });
});

$(document).on('change', '.department-dropdown input[name="department"]', function () {
    var value = $(this).val();

    resetJobpositionDropdown();

    if (!value) {
        return;
    }

    $('.jobposition .menu .item').each(function () {
        var dept = $(this).attr('data-dept');
        if (dept === value) {
            $(this).removeClass('hide disabled');
        }
    });

    $('.jobposition').removeClass('disabled');
    $('.jobposition .default.text').text('{{ __('Select Job Title') }}');
});

function resetDepartmentDropdown() {
    $('.department-dropdown').addClass('disabled').dropdown('clear');
    $('.department-dropdown .menu').empty();
    $('.department-dropdown .default.text').text('{{ __('Select Company First') }}');
    $('input[name="department"]').val('');
}

function resetJobpositionDropdown() {
    $('.jobposition').addClass('disabled').dropdown('clear');
    $('.jobposition .menu .item').addClass('hide disabled');
    $('.jobposition .default.text').text('{{ __('Select Department First') }}');
    $('input[name="jobposition"]').val('');
}

function validateFile() {
    var f = document.getElementById("imagefile").value;
    var d = f.lastIndexOf(".") + 1;
    var ext = f.substr(d, f.length).toLowerCase();
    if (ext == "jpg" || ext == "jpeg" || ext == "png") {
        // valid format
    } else {
        document.getElementById("imagefile").value = "";
        $.notify({
            icon: 'ui icon times',
            message: "Please upload only jpg/jpeg and png image formats."
        }, { type: 'danger', timer: 400 });
    }
}

// Auto-generate 6-digit random ID on page load
document.addEventListener("DOMContentLoaded", function() {
    const idField = document.getElementById('idno');
    if (idField && idField.value.trim() === '') {
        const randomID = Math.floor(100000 + Math.random() * 900000);
        idField.value = randomID;
    }

    // If birthday is already filled, calculate age
    const birthdayField = document.querySelector('input[name="birthday"]');
    if (birthdayField && birthdayField.value) {
        const date = new Date(birthdayField.value);
        if (!isNaN(date)) calculateAge(date);
    }

    initAddressHistory();
    initReasonField();
});

// Age Calculation
function calculateAge(birthDate) {
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();

    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    document.querySelector('input[name="age"]').value = age;
}

/* ================================================================
   REASON FIELD
   Only meaningful when the employee is being saved as Archived, so
   it's kept hidden the rest of the time to avoid confusing anyone
   filling in a brand-new, active employee.
   ================================================================ */
function initReasonField() {
    var statusSelect = document.getElementById('employmentstatus-select');
    var reasonField = document.getElementById('reason-field');

    function syncReasonVisibility() {
        var status = $(statusSelect).val();
        reasonField.classList.toggle('visible', status === 'Archived');
    }

    $(statusSelect).dropdown('setting', 'onChange', syncReasonVisibility);
    syncReasonVisibility();
}

/* ================================================================
   5-YEAR ADDRESS HISTORY
   NOTE: the address textarea below posts as address_line[] to match
   what EmployeesController@add reads ($request->input('address_line', [])).

   IMPORTANT: coverage calculation below is advisory only. The backend
   (EmployeesController::buildAddressEntries) does NOT require full
   5-year continuity - it saves whatever non-blank rows are submitted.
   The submit handler therefore WARNS on incomplete coverage but never
   calls e.preventDefault(), so the form can always be saved and the
   address history completed later.
   ================================================================ */
let addressEntryCount = 0;

function initAddressHistory() {
    addAddressEntry(true); // seed with one "current address" row
    document.getElementById('add-address-entry').addEventListener('click', function () {
        addAddressEntry(false);
    });
    document.getElementById('add_employee_form').addEventListener('submit', function (e) {
        const coverage = calculateAddressCoverage();
        if (!coverage.complete) {
            // Non-blocking: the backend accepts partial address history,
            // so the form must not be prevented from submitting here.
            $.notify({
                icon: 'ui icon warning',
                message: "Note: address history doesn't cover a full continuous 5 years yet. Saving anyway — you can complete it later."
            }, { type: 'warning', timer: 800 });
        }
    });
}

function addAddressEntry(isCurrent) {
    addressEntryCount++;
    const idx = addressEntryCount;

    const wrapper = document.createElement('div');
    wrapper.className = 'address-entry';
    wrapper.dataset.entryId = idx;

    wrapper.innerHTML = `
        <span class="remove-entry" title="Remove this address"><i class="ui trash icon"></i></span>
        <div class="entry-label">${isCurrent ? 'Current Address' : 'Previous Address'}</div>
        <div class="field">
            <label>Address</label>
            <textarea name="address_line[]" class="uppercase address-line-input" rows="2" placeholder="House/Unit Number, Building, Street, City, Province, Country"></textarea>
        </div>
        <div class="current-address-toggle">
            <input type="checkbox" class="is-current-checkbox" ${isCurrent ? 'checked' : ''}> This is my current address
        </div>
        <div class="two fields">
            <div class="field">
                <label>Living Here From</label>
                <input type="date" name="address_from[]" class="address-from-input">
            </div>
            <div class="field">
                <label>Living Here To</label>
                <input type="date" name="address_to[]" class="address-to-input" ${isCurrent ? 'disabled' : ''}>
            </div>
        </div>
        <div class="two fields">
            <div class="field">
                <label>Supporting Document Reference / ID</label>
                <input type="text" name="doc_reference[]" class="uppercase" placeholder="e.g. bank statement / utility bill reference no.">
            </div>
            <div class="field">
                <label>Upload Supporting Document</label>
                <input type="file" name="address_doc[]" class="address-doc-input" accept="image/png, image/jpeg, image/jpg, application/pdf">
            </div>
        </div>
    `;

    document.getElementById('address-entries').appendChild(wrapper);

    const removeBtn = wrapper.querySelector('.remove-entry');
    removeBtn.addEventListener('click', function () {
        wrapper.remove();
        calculateAddressCoverage();
    });

    const currentCheckbox = wrapper.querySelector('.is-current-checkbox');
    const toInput = wrapper.querySelector('.address-to-input');
    currentCheckbox.addEventListener('change', function () {
        toInput.disabled = this.checked;
        if (this.checked) toInput.value = '';
        calculateAddressCoverage();
    });

    wrapper.querySelectorAll('input[type="date"]').forEach(function (el) {
        el.addEventListener('change', calculateAddressCoverage);
    });

    calculateAddressCoverage();
}

function calculateAddressCoverage() {
    const statusBox = document.getElementById('address-coverage-status');
    const statusText = document.getElementById('address-coverage-text');
    const barFill = document.getElementById('address-coverage-bar-fill');
    const submitBtn = document.getElementById('submit_employee_btn');

    const entries = [];
    document.querySelectorAll('.address-entry').forEach(function (row) {
        const from = row.querySelector('.address-from-input').value;
        const isCurrent = row.querySelector('.is-current-checkbox').checked;
        const to = isCurrent ? null : row.querySelector('.address-to-input').value;
        if (from) {
            entries.push({ from: new Date(from), to: to ? new Date(to) : null, isCurrent: isCurrent });
        }
    });

    if (entries.length === 0) {
        setCoverageStatus('incomplete', '0 years 0 months covered of 5 years required.', 0);
        return { complete: false };
    }

    entries.sort(function (a, b) { return a.from - b.from; });

    const today = new Date();
    const fiveYearsAgo = new Date();
    fiveYearsAgo.setFullYear(today.getFullYear() - 5);

    // Check continuity
    for (let i = 0; i < entries.length - 1; i++) {
        const currentTo = entries[i].to;
        const nextFrom = entries[i + 1].from;
        if (!currentTo) {
            setCoverageStatus('error', 'Only the most recent address can be left without an end date.', 0);
            return { complete: false };
        }
        const gapDays = Math.round((nextFrom - currentTo) / (1000 * 60 * 60 * 24));
        if (gapDays > 1) {
            setCoverageStatus('error', 'There is a gap in your address history — please add the missing period.', 0);
            return { complete: false };
        }
    }

    const earliestFrom = entries[0].from;
    const latest = entries[entries.length - 1];
    const latestReachesToday = latest.isCurrent || (latest.to && daysBetween(latest.to, today) <= 31);

    const coverageStart = earliestFrom < fiveYearsAgo ? fiveYearsAgo : earliestFrom;
    const coverageEnd = latestReachesToday ? today : (latest.to || latest.from);
    const coveredMonths = Math.max(0, monthsBetween(coverageStart, coverageEnd));
    const requiredMonths = 60;
    const percent = Math.min(100, Math.round((coveredMonths / requiredMonths) * 100));

    const years = Math.floor(coveredMonths / 12);
    const months = coveredMonths % 12;
    const label = years + ' year' + (years === 1 ? '' : 's') + ' ' + months + ' month' + (months === 1 ? '' : 's') + ' covered of 5 years required.';

    const complete = earliestFrom <= fiveYearsAgo && latestReachesToday;

    setCoverageStatus(complete ? 'complete' : 'incomplete', complete ? 'Full 5-year address history captured.' : label, percent);

    if (submitBtn) submitBtn.disabled = false; // never hard-lock the button; server accepts partial history too

    return { complete: complete };
}

function setCoverageStatus(type, text, percent) {
    const statusBox = document.getElementById('address-coverage-status');
    const statusText = document.getElementById('address-coverage-text');
    const barFill = document.getElementById('address-coverage-bar-fill');

    statusBox.classList.remove('status-incomplete', 'status-complete', 'status-error');
    statusBox.classList.add('status-' + type);
    statusText.textContent = text;
    barFill.style.width = percent + '%';
}

function daysBetween(a, b) {
    return Math.abs(Math.round((b - a) / (1000 * 60 * 60 * 24)));
}

function monthsBetween(a, b) {
    return (b.getFullYear() - a.getFullYear()) * 12 + (b.getMonth() - a.getMonth());
}
</script>
@endsection