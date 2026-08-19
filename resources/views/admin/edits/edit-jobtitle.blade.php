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
    <title>Edit Job Title | {{ $appName }}</title>
    <meta name="description" content="Edit a job title, its duties, company, and department.">
@endsection

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title">{{ __("Edit Job Title") }}</h2>
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

                    @isset($data)

                        <form id="edit_jobtitle_form"
                              action="{{ url('fields/jobtitle/'.$data->id.'/update') }}"
                              class="ui form"
                              method="POST"
                              accept-charset="utf-8">

                            @csrf

                            {{-- COMPANY --}}
                            <div class="field">
                                <label for="jobtitle_edit_company">{{ __("Company") }}</label>

                                <select name="company_id" class="ui search dropdown" id="jobtitle_edit_company">
                                    <option value="">{{ __("Select Company") }}</option>

                                    @isset($company)
                                        @foreach ($company as $c)
                                            <option value="{{ $c->id }}"
                                                {{ (string) old('company_id', $selectedCompanyId ?? '') === (string) $c->id ? 'selected' : '' }}>
                                                {{ $c->company }}
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>

                            {{-- DEPARTMENT --}}
                            <div class="field">
                                <label for="jobtitle_edit_department">{{ __("Department") }}</label>

                                <select name="dept_code" class="ui search dropdown" id="jobtitle_edit_department">
                                    <option value="">{{ __("Select Company first") }}</option>
                                </select>
                            </div>

                            {{-- JOB TITLE --}}
                            <div class="field">
                                <label for="jobtitle">
                                    {{ __("Job Title") }}
                                    <span class="help">e.g. "Chief Executive Officer"</span>
                                </label>

                                <input id="jobtitle" class="uppercase" name="jobtitle" type="text"
                                       value="{{ old('jobtitle', $data->jobtitle) }}">
                            </div>

                            {{-- JOB DUTIES --}}
                            <div class="field">
                                <label for="jobduties">
                                    {{ __("Job Duties") }}
                                    <span class="help">e.g. "Making food"</span>
                                </label>

                                <textarea id="jobduties" class="uppercase" name="jobduties">{{ old('jobduties', $data->jobduties) }}</textarea>
                            </div>

                            <div class="actions">
                                <a href="{{ url('fields/jobtitle') }}" class="ui button small">{{ __("Cancel") }}</a>

                                <button type="submit" class="ui positive button small">
                                    <i class="ui icon check"></i> {{ __("Update") }}
                                </button>
                            </div>

                        </form>

                    @else

                        <div class="ui warning message">
                            {{ __("Job title not found.") }}
                        </div>

                    @endisset

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

    var $companySelect = $('#jobtitle_edit_company');
    var $deptSelect = $('#jobtitle_edit_department');

    var currentCompanyId = @json($selectedCompanyId ?? null);
    var currentDeptCode = @json($selectedDeptCode ?? null);

    $companySelect.dropdown();
    $deptSelect.dropdown();

    function resetDepartment(text) {
        $deptSelect.dropdown('clear');
        $deptSelect.empty();
        $deptSelect.append($('<option>', { value: '', text: text }));
        $deptSelect.dropdown('refresh');
    }

    function loadDepartments(companyId, selectedDeptId) {

        resetDepartment('Loading...');

        if (!companyId) {
            resetDepartment('Select Company first');
            return;
        }

        $.ajax({
            url: '{{ url("employees/departments-by-company") }}',
            type: 'GET',
            data: { company_id: companyId },
            dataType: 'json',

            success: function (departments) {

                $deptSelect.dropdown('clear');
                $deptSelect.empty();

                if (!departments || !Array.isArray(departments) || departments.length === 0) {
                    $deptSelect.append($('<option>', { value: '', text: 'No departments found' }));
                    $deptSelect.dropdown('refresh');
                    return;
                }

                $deptSelect.append($('<option>', { value: '', text: 'Select Department' }));

                $.each(departments, function (index, dept) {
                    $deptSelect.append($('<option>', { value: String(dept.id), text: dept.department }));
                });

                $deptSelect.dropdown('refresh');

                if (selectedDeptId !== null && selectedDeptId !== undefined && selectedDeptId !== '') {

                    var deptValue = String(selectedDeptId);
                    var optionExists = $deptSelect.find('option[value="' + deptValue + '"]').length > 0;

                    if (optionExists) {
                        $deptSelect.dropdown('set selected', deptValue);
                    }
                }

            },

            error: function (xhr) {
                resetDepartment('Could not load departments');

                if (typeof $.notify === 'function') {
                    $.notify({
                        icon: 'ui icon times',
                        message: 'Could not load departments for the selected company.'
                    }, { type: 'danger', timer: 3000 });
                }
            }
        });
    }

    // Company changed by the user -> load its departments, nothing
    // pre-selected (they're picking fresh).
    $companySelect.on('change', function () {
        loadDepartments($(this).val(), null);
    });

    // Initial page load: select the existing company, then load its
    // departments and pre-select the job title's current one.
    // 'set selected' may or may not fire the native change event
    // depending on the Fomantic version in use, so we call
    // loadDepartments() explicitly here rather than relying on that -
    // worst case this fires one harmless extra GET request, which is
    // safer than the department list silently never loading.
    if (currentCompanyId) {
        $companySelect.dropdown('set selected', String(currentCompanyId));
        loadDepartments(currentCompanyId, currentDeptCode);
    }

    $(document).on('click', '.message .close', function () {
        $(this).closest('.message').transition('fade');
    });

});
</script>
@endsection