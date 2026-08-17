@extends('layouts.default')

@section('meta')
    <title>Edit Employee | Jpingos</title>
    <meta name="description" content="Edit employee information">
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

        .current-avatar {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e5e7eb;
            display: block;
            margin-bottom: 10px;
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

        .hide {
            display: none !important;
        }
    </style>
@endsection

@section('content')

@php
    // Match the employee's currently-saved company name (text) back to
    // its id, since the AJAX cascade below - same as the New Employee
    // form - is keyed on company id, not name.
    $selectedCompany = $company->firstWhere('company', $company_details->company);
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title">{{ __('Edit Employee Profile') }}</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="ui positive message">
            <i class="close icon"></i>
            {{ session('success') }}
        </div>
    @endif

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

    <form id="edit_employee_form" action="{{ url('profile/update') }}" class="ui form custom" method="post" accept-charset="utf-8" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{ $e_id }}">

        <div class="col-md-6 float-left">
            <div class="box box-success">
                <div class="box-header with-border">{{ __('Personal Information') }}</div>
                <div class="box-body">
                    <div class="two fields">
                        <div class="field">
                            <label>{{ __('First Name') }}</label>
                            <input type="text" class="uppercase" name="firstname" value="{{ old('firstname', $person_details->firstname) }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Middle Name') }}</label>
                            <input type="text" class="uppercase" name="mi" value="{{ old('mi', $person_details->mi) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label>{{ __('Last Name') }}</label>
                        <input type="text" class="uppercase" name="lastname" value="{{ old('lastname', $person_details->lastname) }}">
                    </div>
                    <div class="field">
                        <label>{{ __('Gender') }}</label>
                        <select name="gender" class="ui dropdown uppercase">
                            <option value="">Select Gender</option>
                            <option value="MALE" {{ old('gender', $person_details->gender) == 'MALE' ? 'selected' : '' }}>MALE</option>
                            <option value="FEMALE" {{ old('gender', $person_details->gender) == 'FEMALE' ? 'selected' : '' }}>FEMALE</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>{{ __('Civil Status') }}</label>
                        <select name="civilstatus" class="ui dropdown uppercase">
                            <option value="">Select Civil Status</option>
                            @foreach(['SINGLE', 'MARRIED', 'ANULLED', 'WIDOWED', 'LEGALLY SEPARATED'] as $status)
                                <option value="{{ $status }}" {{ old('civilstatus', $person_details->civilstatus) == $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="two fields">
                        <div class="field">
                            <label>{{ __('Email Address (Personal)') }}</label>
                            <input type="email" name="emailaddress" class="lowercase" value="{{ old('emailaddress', $person_details->emailaddress) }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Mobile Number') }}</label>
                            <input type="text" name="mobileno" value="{{ old('mobileno', $person_details->mobileno) }}">
                        </div>
                    </div>
                    <div class="two fields">
                        <div class="field">
                            <label>{{ __('Age') }}</label>
                            <input type="text" name="age" value="{{ old('age', $person_details->age) }}" readonly>
                        </div>
                        <div class="field">
                            <label>{{ __('Date of Birth') }}</label>
                            <input type="text" name="birthday" class="airdatepicker" data-position="top right" placeholder="Date" value="{{ old('birthday', $person_details->birthday) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label>{{ __('Passport No') }}</label>
                        <input type="text" class="uppercase" name="nationalid" value="{{ old('nationalid', $person_details->nationalid) }}">
                    </div>
                    <div class="two fields">
                        <div class="field">
                            <label>{{ __('Passport Issue Date') }}</label>
                            <input type="date" name="idissuedate" value="{{ old('idissuedate', $person_details->idissuedate) }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Passport Expiry Date') }}</label>
                            <input type="date" name="idexpirydate" value="{{ old('idexpirydate', $person_details->idexpirydate) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label>{{ __('Share Code') }}</label>
                        <input type="text" class="uppercase" name="sharecode" value="{{ old('sharecode', $person_details->sharecode) }}">
                    </div>
                    <div class="field">
                        <label>{{ __('National Insurance') }}</label>
                        <input type="text" class="uppercase" name="ni" value="{{ old('ni', $person_details->NI) }}">
                    </div>
                    <div class="field">
                        <label>{{ __('Place of Birth') }}</label>
                        <input type="text" class="uppercase" name="birthplace" value="{{ old('birthplace', $person_details->birthplace) }}">
                    </div>
                    <div class="field">
                        <label>{{ __('Profile Photo') }}</label>
                        @if(!empty($person_details->avatar))
                            {{-- Avatars are stored on the "public" disk (storage/app/public/avatars),
                                 not the old public/assets/faces path - Storage::url() resolves that
                                 correctly whether php artisan storage:link has aliased it or not. --}}
                            <img src="{{ Storage::disk('public')->url($person_details->avatar) }}" class="current-avatar" alt="{{ $person_details->firstname }}">
                        @endif
                        <input class="ui file upload" id="imagefile" name="image" type="file" accept="image/png, image/jpeg, image/jpg" onchange="validateFile()">
                        <small class="text-muted">Leave empty to keep the current photo.</small>
                    </div>
                    <br>
                </div>
            </div>

            <!-- ============== 5-YEAR ADDRESS HISTORY ============== -->
            <div class="box box-success">
                <div class="box-header with-border">{{ __('Address History (Last 5 Years)') }}</div>
                <div class="box-body">
                    <p id="address-history-intro">
                        {{ __('Add each address this employee has lived at, starting with their current one, with a supporting document reference and (where available) a scanned copy of that document.') }}
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
                        {{-- Hidden input posts the company's id (tbl_form_company.id),
                             matching FieldsController@departmentsByCompany, and
                             pre-selected to the employee's current company. --}}
                        <div class="ui search dropdown selection uppercase company-dropdown">
                            <input type="hidden" name="company_id" value="{{ old('company_id', optional($selectedCompany)->id) }}">
                            <i class="dropdown icon" tabindex="1"></i>
                            <div class="{{ $company_details->company ? 'text' : 'default text' }}">{{ $company_details->company ?: __('Select Company') }}</div>
                            <div class="menu">
                            @isset($company)
                                @foreach ($company as $data)
                                    <div class="item {{ optional($selectedCompany)->id == $data->id ? 'active selected' : '' }}" data-value="{{ $data->id }}">{{ $data->company }}</div>
                                @endforeach
                            @endisset
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label>{{ __('Department') }}</label>
                        {{-- Populated via AJAX on page load (for the current company)
                             and again on every company change, same as New Employee. --}}
                        <div class="ui search dropdown selection uppercase department-dropdown">
                            <input type="hidden" name="department" value="{{ old('department', $company_details->department) }}">
                            <i class="dropdown icon" tabindex="1"></i>
                            <div class="default text">{{ $company_details->department ?: __('Select Company First') }}</div>
                            <div class="menu"></div>
                        </div>
                    </div>

                    <div class="field">
                        <label>{{ __('Job Title / Position') }}</label>
                        <div class="ui search dropdown selection uppercase jobposition">
                            <input type="hidden" name="jobposition" value="{{ old('jobposition', $company_details->jobposition) }}">
                            <i class="dropdown icon" tabindex="1"></i>
                            <div class="default text">{{ $company_details->jobposition ?: __('Select Department First') }}</div>
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
                        <textarea id="jobduties" class="uppercase" name="jobduties">{{ old('jobduties', $company_details->jobduties) }}</textarea>
                    </div>

                    <h4 class="ui dividing header">{{ __('Visa & Compliance') }}</h4>

                    <div class="field">
                        <label>{{ __('COS Certificate Number') }}</label>
                        <input type="text" class="uppercase" name="COSCertificateNo" value="{{ old('COSCertificateNo', $company_details->COSCertificateNo) }}" placeholder="e.g. C2G--------">
                    </div>
                    <div class="field">
                        <label>{{ __('COS Expiry') }}</label>
                        <input type="date" name="cosexpiry" value="{{ old('cosexpiry', $company_details->cosexpiry) }}">
                    </div>
                    <div class="field">
                        <label>{{ __('Visa Status') }}</label>
                        <input type="text" class="uppercase" name="visastatus" value="{{ old('visastatus', $company_details->visastatus) }}">
                    </div>
                    <div class="two fields">
                        <div class="field">
                            <label>{{ __('Visa Issue Date') }}</label>
                            <input type="date" name="visastart" value="{{ old('visastart', $company_details->visastart) }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Visa Expiry Date') }}</label>
                            <input type="date" name="visaend" value="{{ old('visaend', $company_details->visaend) }}">
                        </div>
                    </div>
                    <div class="field">
                        <label>{{ __('Job Type') }}</label>
                        <input type="text" class="uppercase" name="jobtype" value="{{ old('jobtype', $company_details->jobtype) }}" placeholder="e.g. Chef">
                    </div>
                    <div class="field">
                        <label>{{ __('ID Number') }}</label>
                        <input type="text" class="uppercase" name="idno" id="idno" value="{{ old('idno', $company_details->idno) }}">
                    </div>

                    <h4 class="ui dividing header">{{ __('Next of Kin') }}</h4>

                    <div class="field">
                        <label>{{ __('Next of Kin Number') }}</label>
                        <input type="text" class="uppercase" name="kinno" value="{{ old('kinno', $company_details->kinno) }}">
                    </div>
                    <div class="field">
                        <label>{{ __('Next of Kin Name and Relationship') }}</label>
                        <input type="text" class="uppercase" name="kinname" value="{{ old('kinname', $company_details->kinname) }}">
                    </div>
                    <div class="field">
                        <label>{{ __('Work Checks') }}</label>
                        <textarea id="workchecks" name="workchecks">{{ old('workchecks', $company_details->workchecks) }}</textarea>
                    </div>

                    <h4 class="ui dividing header">{{ __('Employment Information') }}</h4>

                    <div class="field">
                        <label>{{ __('Email Address (Company)') }}</label>
                        <input type="email" name="companyemail" class="lowercase" value="{{ old('companyemail', $company_details->companyemail) }}">
                    </div>
                    <div class="field">
                        <label>{{ __('Leave Group') }}</label>
                        <select name="leaveprivilege" class="ui dropdown uppercase">
                            <option value="">Select Leave Privilege</option>
                            @foreach($leavegroup as $lg)
                                <option value="{{ $lg->id }}" {{ old('leaveprivilege', $company_details->leaveprivilege) == $lg->id ? 'selected' : '' }}>{{ $lg->leavegroup }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="two fields">
                        <div class="field">
                            <label>{{ __('Per Hour Pay') }}</label>
                            <input type="number" name="perhourpay" value="{{ old('perhourpay', $person_details->perhourpay) }}" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="field">
                            <label>{{ __('Account Pay') }}</label>
                            <input type="number" name="accountpay" value="{{ old('accountpay', $person_details->accountpay) }}" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="field">
                        <label>{{ __('Employment Type') }}</label>
                        <select name="employmenttype" class="ui dropdown uppercase">
                            <option value="">Select Type</option>
                            @foreach(['Regular', 'Part-Time', 'Trainee'] as $type)
                                <option value="{{ $type }}" {{ old('employmenttype', $person_details->employmenttype) == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>{{ __('Employment Status') }}</label>
                        <select name="employmentstatus" class="ui dropdown uppercase">
                            <option value="">Select Status</option>
                            <option value="Active" {{ old('employmentstatus', $person_details->employmentstatus) == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Archived" {{ old('employmentstatus', $person_details->employmentstatus) == 'Archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </div>
                    <div class="two fields">
                        <div class="field">
                            <label>{{ __('Official Start Date') }}</label>
                            <input type="text" name="startdate" class="airdatepicker uppercase" data-position="top right" placeholder="Date" value="{{ old('startdate', $company_details->startdate) }}">
                        </div>
                        <div class="field">
                            <label>{{ __('Date Regularized') }}</label>
                            <input type="text" name="dateregularized" class="airdatepicker uppercase" data-position="top right" placeholder="Date" value="{{ old('dateregularized', $company_details->dateregularized) }}">
                        </div>
                    </div>
                    <br>
                </div>
            </div>
        </div>

        <div class="col-md-12 float-left">
            <div class="action align-right">
                <button type="submit" name="submit" id="submit_employee_btn" class="ui green button small"><i class="checkmark icon"></i>{{ __('Update Employee') }}</button>
                <a href="{{ url('employees') }}" class="ui grey button small"><i class="times icon"></i>{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
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
        if (date) calculateAge(date);
    }
});

/* ================================================================
   COMPANY -> DEPARTMENT (live AJAX) -> JOB TITLE CASCADE
   ================================================================ */
$('.company-dropdown').dropdown();
@if($selectedCompany)
    markDropdownAsSelected($('.company-dropdown'));
@endif
$('.department-dropdown').dropdown();
$('.jobposition').dropdown();
$('.jobposition').dropdown('disable');

$(document).on('change', '.company-dropdown input[name="company_id"]', function () {
    var value = $(this).val();
    resetDepartmentDropdown();
    resetJobpositionDropdown();
    if (value) {
        loadDepartments(value);
    }
});

$(document).on('change', '.department-dropdown input[name="department"]', function () {
    var value = $(this).val();
    resetJobpositionDropdown();
    if (value) {
        revealJobTitlesForDepartment(value);
    }
});

function markDropdownAsSelected($dropdown) {
    $dropdown.find('> .text').removeClass('default');
}

function loadDepartments(companyId, opts) {
    opts = opts || {};

    $('.department-dropdown').addClass('loading');

    $.ajax({
        url: '{{ url("employees/departments-by-company") }}',
        type: 'GET',
        data: { company_id: companyId },
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

            $('.department-dropdown').removeClass('loading');
            $('.department-dropdown').dropdown('refresh');

            if (opts.preselectDepartment) {
                $('.department-dropdown').dropdown('set selected', opts.preselectDepartment);
                markDropdownAsSelected($('.department-dropdown'));
                revealJobTitlesForDepartment(opts.preselectDepartment);
                if (opts.preselectJobtitle) {
                    $('.jobposition').dropdown('set selected', opts.preselectJobtitle);
                    markDropdownAsSelected($('.jobposition'));
                    // Fallback: 'set selected' can silently no-op if Fomantic's
                    // internal item cache is still stale for any reason beyond
                    // the refresh() call above already covers - this guarantees
                    // the visible text/value are correct regardless.
                    forceSelectJobTitle(opts.preselectJobtitle);
                }
            } else {
                $('.department-dropdown .default.text').text('{{ __('Select Department') }}');
            }

            if (departments.length === 0 && !opts.preselectDepartment) {
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
}

function revealJobTitlesForDepartment(departmentName) {
    $('.jobposition .menu .item').each(function () {
        var dept = $(this).attr('data-dept');
        if (dept === departmentName) {
            $(this).removeClass('hide disabled');
        }
    });
    $('.jobposition').dropdown('enable');
    // IMPORTANT: the items above just had classes stripped via plain
    // jQuery, not through Fomantic's own API. Fomantic's dropdown module
    // can hold a stale internal snapshot of "selectable" items from init
    // time, so a subsequent .dropdown('set selected', ...) call can
    // silently fail to match an item that was only just unhidden here.
    // refresh() tells it to re-scan the current menu DOM before we try
    // to select anything - this is what was missing and is why job
    // title selection wasn't sticking even though department was.
    $('.jobposition').dropdown('refresh');
    if (!$('input[name="jobposition"]').val()) {
        $('.jobposition .default.text').text('{{ __('Select Job Title') }}');
    }
}

// Selects a job title item directly by data-value, bypassing Fomantic's
// set-selected lookup entirely. Used as a guaranteed-correct fallback
// after the normal dropdown('set selected', ...) call, in case that
// call still can't find the item for any reason not covered by the
// refresh() fix above.
function forceSelectJobTitle(value) {
    if (!value) return;

    var $item = $('.jobposition .menu .item').filter(function () {
        return $(this).attr('data-value') === value;
    }).first();

    if ($item.length === 0) {
        return; // no matching job title for this department - nothing to select
    }

    $('.jobposition .menu .item').removeClass('active selected');
    $item.addClass('active selected');

    $('input[name="jobposition"]').val(value).trigger('change');
    $('.jobposition > .text').removeClass('default').text(value);
}

function resetDepartmentDropdown() {
    $('.department-dropdown').dropdown('clear');
    $('.department-dropdown .menu').empty();
    $('.department-dropdown .default.text').text('{{ __('Select Company First') }}');
    $('input[name="department"]').val('');
}

function resetJobpositionDropdown() {
    $('.jobposition').dropdown('clear').dropdown('disable');
    $('.jobposition .menu .item').addClass('hide disabled');
    $('.jobposition .default.text').text('{{ __('Select Department First') }}');
    $('input[name="jobposition"]').val('');
}

function validateFile() {
    var f = document.getElementById("imagefile").value;
    if (!f) return;
    var d = f.lastIndexOf(".") + 1;
    var ext = f.substr(d, f.length).toLowerCase();
    if (ext == "jpg" || ext == "jpeg" || ext == "png") {
        // valid
    } else {
        document.getElementById("imagefile").value = "";
        $.notify({
            icon: 'ui icon times',
            message: "Please upload only jpg/jpeg and png image formats."
        }, { type: 'danger', timer: 400 });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Age from existing birthday
    const birthdayField = document.querySelector('input[name="birthday"]');
    if (birthdayField && birthdayField.value) {
        const date = new Date(birthdayField.value);
        if (!isNaN(date)) calculateAge(date);
    }

    // Pre-load departments/job title for the employee's current company
    var companyId = $('input[name="company_id"]').val();
    var currentDepartment = @json($company_details->department);
    var currentJobtitle = @json($company_details->jobposition);

    if (companyId) {
        loadDepartments(companyId, {
            preselectDepartment: currentDepartment || null,
            preselectJobtitle: currentJobtitle || null
        });
    }

    initAddressHistory();
});

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
   5-YEAR ADDRESS HISTORY
   ================================================================ */
const existingAddressHistory = @json($addressHistory ?? []);
let addressEntryCount = 0;

function updateAddressLabels() {
    document.querySelectorAll('.address-entry').forEach(function (row) {
        const isCurrent = row.querySelector('.is-current-checkbox').checked;
        row.querySelector('.entry-label').textContent = isCurrent ? 'Current Address' : 'Previous Address';
    });
}

function initAddressHistory() {
    if (existingAddressHistory.length > 0) {
        existingAddressHistory.forEach(function (row, index) {
            addAddressEntry(row.is_current == 1 || row.is_current === true, {
                id: row.id,
                address: row.address_line,
                from: row.date_from,
                to: row.date_to,
                docReference: row.doc_reference
            });
        });
    } else {
        addAddressEntry(true);
    }

    document.getElementById('add-address-entry').addEventListener('click', function () {
        addAddressEntry(false);
    });
    document.getElementById('edit_employee_form').addEventListener('submit', function (e) {
        const coverage = calculateAddressCoverage();
        if (!coverage.complete) {
            e.preventDefault();
            $.notify({
                icon: 'ui icon times',
                message: "Please complete a continuous 5-year address history before saving."
            }, { type: 'danger', timer: 500 });
            document.getElementById('address-entries').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}

function addAddressEntry(isCurrent, prefill) {
    addressEntryCount++;
    const idx = addressEntryCount;
    prefill = prefill || {};

    const wrapper = document.createElement('div');
    wrapper.className = 'address-entry';
    wrapper.dataset.entryId = idx;

    wrapper.innerHTML = `
        <span class="remove-entry" title="Remove this address"><i class="ui trash icon"></i></span>
        <div class="entry-label">${isCurrent ? 'Current Address' : 'Previous Address'}</div>
        <input type="hidden" name="address_id[]" value="${prefill.id || ''}">
        <div class="field">
            <label>Address</label>
            <textarea name="address_line[]" class="uppercase address-line-input" rows="2" placeholder="House/Unit Number, Building, Street, City, Province, Country">${prefill.address || ''}</textarea>
        </div>
        <div class="current-address-toggle">
            <input type="checkbox" class="is-current-checkbox" ${isCurrent ? 'checked' : ''}> This is my current address
        </div>
        <div class="two fields">
            <div class="field">
                <label>Living Here From</label>
                <input type="date" name="address_from[]" class="address-from-input" value="${prefill.from || ''}">
            </div>
            <div class="field">
                <label>Living Here To</label>
                <input type="date" name="address_to[]" class="address-to-input" value="${prefill.to || ''}" ${isCurrent ? 'disabled' : ''}>
            </div>
        </div>
        <div class="two fields">
            <div class="field">
                <label>Supporting Document Reference / ID</label>
                <input type="text" name="doc_reference[]" class="uppercase" placeholder="e.g. bank statement / utility bill reference no." value="${prefill.docReference || ''}">
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
        if (this.checked) {
            document.querySelectorAll('.address-entry').forEach(function (row) {
                const cb = row.querySelector('.is-current-checkbox');
                if (cb !== currentCheckbox && cb.checked) {
                    cb.checked = false;
                    row.querySelector('.address-to-input').disabled = false;
                }
            });
        }
        toInput.disabled = this.checked;
        if (this.checked) toInput.value = '';
        updateAddressLabels();
        calculateAddressCoverage();
    });

    wrapper.querySelectorAll('input[type="date"]').forEach(function (el) {
        el.addEventListener('change', calculateAddressCoverage);
    });

    updateAddressLabels();
    calculateAddressCoverage();
}

function parseDateOnly(str) {
    if (!str) return null;
    const parts = str.split('-');
    return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
}

function calculateAddressCoverage() {
    const submitBtn = document.getElementById('submit_employee_btn');

    const entries = [];
    document.querySelectorAll('.address-entry').forEach(function (row) {
        const from = row.querySelector('.address-from-input').value;
        const isCurrent = row.querySelector('.is-current-checkbox').checked;
        const to = isCurrent ? null : row.querySelector('.address-to-input').value;
        if (from) {
            entries.push({ from: parseDateOnly(from), to: to ? parseDateOnly(to) : null, isCurrent: isCurrent });
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

    for (let i = 0; i < entries.length - 1; i++) {
        const currentTo = entries[i].to;
        const nextFrom = entries[i + 1].from;
        if (!currentTo) {
            setCoverageStatus('error', 'Only the most recent address can be left without an end date.', 0);
            return { complete: false };
        }
        const gapDays = Math.round((nextFrom - currentTo) / (1000 * 60 * 60 * 24));
        if (gapDays > 1) {
            setCoverageStatus('error', 'There is a gap in the address history — please add the missing period.', 0);
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

    if (submitBtn) submitBtn.disabled = false;

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