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
    <title>Job Titles | {{ $appName }}</title>
    <meta name="description" content="Workday job titles, view job titles, and export or download job titles.">
@endsection

@section('content')

@include('admin.modals.modal-import-jobtitle')

<div class="container-fluid">

    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title">
                {{ __("Add Job Title") }}

                <button class="ui basic button mini offsettop5 btn-import float-right">
                    <i class="ui icon upload"></i> {{ __("Import") }}
                </button>

                <a href="{{ url('export/fields/jobtitle') }}"
                   class="ui basic button mini offsettop5 btm-export float-right">
                    <i class="ui icon download"></i> {{ __("Export") }}
                </a>
            </h2>
        </div>
    </div>


    <div class="row">

        {{-- =========================
             ADD JOB TITLE FORM
        ========================== --}}
        <div class="col-md-4">

            <div class="box box-success">
                <div class="box-body">

                    @if ($errors->any())
                        <div class="ui error message">
                            <i class="close icon"></i>

                            <div class="header">
                                {{ __("There were some errors with your submission") }}
                            </div>

                            <ul class="list">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <form id="add_jobtitle_form"
                          action="{{ url('fields/jobtitle/add') }}"
                          class="ui form"
                          method="POST"
                          accept-charset="utf-8">

                        @csrf


                        {{-- =========================
                             COMPANY
                        ========================== --}}
                        <div class="field">

                            <label>
                                {{ __('Company') }}
                            </label>

                            <div class="ui search dropdown selection uppercase company-dropdown">

                                {{-- THIS VALUE IS SUBMITTED --}}
                                <input type="hidden"
                                       name="company_id"
                                       id="company_id"
                                       value="{{ old('company_id') }}">

                                <i class="dropdown icon" tabindex="1"></i>

                                <div class="default text">
                                    {{ __('Select Company') }}
                                </div>

                                <div class="menu">

                                    @isset($company)

                                        @foreach ($company as $companyItem)

                                            <div class="item"
                                                 data-value="{{ $companyItem->id }}">

                                                {{ $companyItem->company }}

                                            </div>

                                        @endforeach

                                    @endisset

                                </div>

                            </div>

                        </div>


                        {{-- =========================
                             DEPARTMENT
                        ========================== --}}
                        <div class="field">

                            <label>
                                {{ __('Department') }}
                            </label>

                            <div class="ui search dropdown selection uppercase department-dropdown">

                                {{-- THIS VALUE IS SUBMITTED --}}
                                <input type="hidden"
                                       name="department"
                                       id="department"
                                       value="{{ old('department') }}">

                                <i class="dropdown icon" tabindex="1"></i>

                                <div class="default text">
                                    {{ __('Select Company First') }}
                                </div>

                                <div class="menu">
                                    {{-- Populated using AJAX --}}
                                </div>

                            </div>

                        </div>


                        {{-- =========================
                             JOB TITLE
                        ========================== --}}
                        <div class="field">

                            <label>
                                {{ __("Job Title") }}

                                <span class="help">
                                    e.g. "Chief Executive Officer"
                                </span>
                            </label>

                            <input
                                class="uppercase"
                                name="jobtitle"
                                id="jobtitle"
                                type="text"
                                value="{{ old('jobtitle') }}"
                            >

                        </div>


                        {{-- =========================
                             JOB DUTIES
                        ========================== --}}
                        <div class="field">

                            <label>
                                {{ __("Job Duties") }}

                                <span class="help">
                                    e.g. "Making food"
                                </span>
                            </label>

                            <textarea
                                class="uppercase"
                                name="jobduties"
                                id="jobduties"
                            >{{ old('jobduties') }}</textarea>

                        </div>


                        {{-- =========================
                             ERROR MESSAGE
                        ========================== --}}
                        <div class="field">

                            <div class="ui error message">

                                <i class="close icon"></i>

                                <div class="header"></div>

                                <ul class="list">
                                    <li></li>
                                </ul>

                            </div>

                        </div>


                        {{-- =========================
                             SUBMIT
                        ========================== --}}
                        <div class="actions">

                            <button type="submit"
                                    class="ui positive button small">

                                <i class="ui icon check"></i>

                                {{ __("Save") }}

                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>


        {{-- =========================
             JOB TITLE LIST
        ========================== --}}
        <div class="col-md-8">

            <div class="box box-success">

                <div class="box-body">

                    {{-- ================= FILTER ================= --}}
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-4">
                            <label style="display:block; font-size:11px; color:#6b7280; text-transform:uppercase; margin-bottom:4px;">
                                {{ __('Filter by Company') }}
                            </label>
                            <select id="jtCompanyFilter" class="ui fluid dropdown">
                                @isset($company)
                                    @foreach ($company as $comp)
                                        <option value="{{ $comp->id }}">{{ $comp->company }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label style="display:block; font-size:11px; color:#6b7280; text-transform:uppercase; margin-bottom:4px;">
                                {{ __('Filter by Department') }}
                            </label>
                            <select id="jtDepartmentFilter" class="ui fluid dropdown">
                                {{-- Always lists every department, regardless of
                                     which company is selected in the Company
                                     filter - the two filters act independently. --}}

                                @isset($d)
                                    @foreach ($d as $dept)
                                        <option value="{{ $dept->department }}">{{ $dept->department }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                    </div>

                    <table
                        width="100%"
                        class="table table-striped table-hover"
                        id="dataTables-example"
                        data-order='[[ 1, "asc" ]]'
                    >

                        <thead>

                            <tr>

                                <th>
                                    {{ __("Job Title") }}
                                </th>

                                <th>
                                    {{ __("Job Duties") }}
                                </th>

                                <th>
                                    {{ __("Company") }}
                                </th>

                                <th>
                                    {{ __("Department") }}
                                </th>

                                <th></th>

                            </tr>

                        </thead>


                        <tbody>

                            @if(isset($data) && is_iterable($data))

                                @foreach ($data as $j)

                                    <tr>

                                        <td>{{ $j->jobtitle ?? '' }}</td>
                                        <td>{!! $j->jobduties ?? '' !!}</td>
                                        <td>{{ $j->company_name ?? '' }}</td>
                                        <td>{{ $j->department_name ?? '' }}</td>

                                        <td class="align-right">

                                            <a
                                                href="{{ url('fields/jobtitle/'.$j->id.'/edit') }}"
                                                class="ui circular basic icon button tiny"
                                            >
                                                <i class="icon edit outline"></i>
                                            </a>


                                            <a
                                                href="{{ url('fields/jobtitle/delete/'.$j->id) }}"
                                                class="ui circular basic icon button tiny"
                                                onclick="return confirm('{{ __('Delete this job title?') }}');"
                                            >
                                                <i class="icon trash alternate outline"></i>
                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                            @endif

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@section('scripts')

<script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('jobduties');
</script>

<script type="text/javascript">

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | DataTable
    |--------------------------------------------------------------------------
    */

    var jobtitleTable = $('#dataTables-example').DataTable({
        responsive: true,
        pageLength: 15,
        lengthChange: false,
        searching: true,
        ordering: true
    });


    /*
    |--------------------------------------------------------------------------
    | Initialize dropdowns (Add form)
    |--------------------------------------------------------------------------
    */

    $('.company-dropdown').dropdown();

    $('.department-dropdown').dropdown();


    /*
    |--------------------------------------------------------------------------
    | Initialize dropdowns (List filters)
    |--------------------------------------------------------------------------
    */

    $('#jtCompanyFilter').dropdown();
    $('#jtDepartmentFilter').dropdown();

    // Company and Department filters act INDEPENDENTLY - the Department
    // dropdown always lists every department (rendered once from $d in
    // Blade above) and is never rebuilt or scoped by the selected
    // company. Both filters just narrow the same table together.
    function applyJobtitleFilters() {
        var companyName = $('#jtCompanyFilter').val()
            ? $('#jtCompanyFilter option:selected').text().trim()
            : '';
        var departmentName = $('#jtDepartmentFilter').val();

        // Job Title, Job Duties, Company, Department, Actions
        // -> Company is column index 2, Department is column index 3.
        var companyRegex = companyName ? '^' + $.fn.dataTable.util.escapeRegex(companyName) + '$' : '';
        var departmentRegex = departmentName ? '^' + $.fn.dataTable.util.escapeRegex(departmentName) + '$' : '';

        jobtitleTable
            .column(2).search(companyRegex, true, false)
            .column(3).search(departmentRegex, true, false)
            .draw();
    }

    $('#jtCompanyFilter').on('change', applyJobtitleFilters);
    $('#jtDepartmentFilter').on('change', applyJobtitleFilters);

    // No "All" options on either filter - the first company and first
    // department are selected by default, so apply that combination
    // immediately on load.
    applyJobtitleFilters();


    /*
    |--------------------------------------------------------------------------
    | COMPANY SELECTED (Add form)
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'change',
        '.company-dropdown input[name="company_id"]',
        function () {

            var companyId = $(this).val();

            $('#company_id').val(companyId);

            console.log('Selected company:', companyId);

            resetDepartmentDropdown();

            if (!companyId) {
                return;
            }

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

                        var $item = $('<div class="item"></div>');

                        // IMPORTANT: data-value must be the department's
                        // numeric id (dept_code is a foreign key to
                        // tbl_form_department.id) - NOT its name text.
                        // Using the name here was the bug that made every
                        // saved job title store garbage into dept_code.
                        $item.attr('data-value', dept.id);
                        $item.text(dept.department);

                        $menu.append($item);

                    });

                    $('.department-dropdown').removeClass('loading');

                    $('.department-dropdown .default.text')
                        .text('{{ __("Select Department") }}');

                    $('.department-dropdown').dropdown('refresh');

                    $('#department').val('');

                    if (departments.length === 0) {

                        $.notify({
                            icon: 'ui icon info',
                            message: 'No departments found for this company yet.'
                        }, {
                            type: 'info',
                            timer: 500
                        });

                    }

                },

                error: function (xhr) {

                    $('.department-dropdown').removeClass('loading');

                    $.notify({
                        icon: 'ui icon times',
                        message: 'Could not load departments for this company. Please try again.'
                    }, {
                        type: 'danger',
                        timer: 500
                    });

                }

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | DEPARTMENT SELECTED (Add form)
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'change',
        '.department-dropdown input[name="department"]',
        function () {

            var department = $(this).val();

            $('#department').val(department);

            console.log('Selected department:', department);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | RESET DEPARTMENT (Add form)
    |--------------------------------------------------------------------------
    */

    function resetDepartmentDropdown() {

        $('.department-dropdown').dropdown('clear');
        $('.department-dropdown .menu').empty();
        $('.department-dropdown .default.text')
            .text('{{ __("Select Company First") }}');
        $('#department').val('');

    }


    /*
    |--------------------------------------------------------------------------
    | BEFORE SUBMIT (Add form)
    |--------------------------------------------------------------------------
    */

    $('#add_jobtitle_form').on(
        'submit',
        function (e) {

            var companyId = $('.company-dropdown input[name="company_id"]').val();
            var department = $('.department-dropdown input[name="department"]').val();

            $('#company_id').val(companyId);
            $('#department').val(department);

            console.log('Submitting:', {
                company_id: $('#company_id').val(),
                department: $('#department').val(),
                jobtitle: $('#jobtitle').val(),
                jobduties: $('#jobduties').val()
            });

            if (!companyId) {
                e.preventDefault();
                $.notify({
                    icon: 'ui icon times',
                    message: 'Please select a company.'
                }, { type: 'danger', timer: 1000 });
                return false;
            }

            if (!department) {
                e.preventDefault();
                $.notify({
                    icon: 'ui icon times',
                    message: 'Please select a department.'
                }, { type: 'danger', timer: 1000 });
                return false;
            }

            if (!$('#jobtitle').val().trim()) {
                e.preventDefault();
                $.notify({
                    icon: 'ui icon times',
                    message: 'Please enter a job title.'
                }, { type: 'danger', timer: 1000 });
                return false;
            }

            return true;

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CSV VALIDATION
    |--------------------------------------------------------------------------
    */

    function validateFile() {

        var fileInput = document.getElementById("csvfile");

        if (!fileInput) {
            return;
        }

        var file = fileInput.value;
        var dot = file.lastIndexOf(".") + 1;
        var extension = file.substr(dot, file.length).toLowerCase();

        if (extension === "csv") {
            return;
        }

        fileInput.value = "";

        $.notify({
            icon: 'ui icon times',
            message: "Please upload only CSV file format."
        }, {
            type: 'danger',
            timer: 400
        });

    }

});

</script>

@endsection