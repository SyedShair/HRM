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
        <title>Departments | {{ $appName }}</title>
        <meta name="description" content="Workday departments, view departments, and export or download departments.">
    @endsection

    @section('content')
    @include('admin.modals.modal-import-department')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="page-title uppercase">{{ __("Add Department") }}
                <button class="ui basic button mini offsettop5 btn-import float-right"><i class="ui icon upload"></i> {{ __("Import") }}</button>
                <a href="{{ url('export/fields/department' )}}" class="ui basic button mini offsettop5 btm-export float-right"><i class="ui icon download"></i> {{ __("Export") }}</a>
                </h2>
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
                        <form id="add_department_form" action="{{ url('fields/department/add') }}" class="ui form" method="post" accept-charset="utf-8">
                            @csrf
                            <div class="field">
                                <label>{{ __("Company") }} <span class="help">{{ __("Which company this department belongs to") }}</span></label>
                                <select name="company" class="ui search dropdown">
                                    <option value="">{{ __("Select Company") }}</option>
                                    @isset($company)
                                        @foreach ($company as $comp)
                                            <option value="{{ $comp->id }}">{{ $comp->company }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="field">
                                <label>{{ __("Department Name") }} <span class="help">e.g. "Accounting"</span></label>
                                <input class="uppercase" name="department" value="" type="text">
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
                                <button type="submit" class="ui positive button small"><i class="ui icon check"></i> {{ __("Save") }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
            <div class="box box-success">
                <div class="box-body">

                <!-- ================= FILTER ================= -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
                        <label style="display:block; font-size:11px; color:#6b7280; text-transform:uppercase; margin-bottom:4px;">
                            {{ __('Filter by Company') }}
                        </label>
                        <select id="companyFilterSelect" class="ui fluid dropdown">
                            @isset($company)
                                @foreach ($company as $comp)
                                    <option value="{{ $comp->company }}">{{ $comp->company }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                </div>

                <table width="100%" class="table table-striped table-hover" id="dataTables-example">
                    <thead>
                        <tr>
                            <th>{{ __("Company") }}</th>
                            <th>{{ __("Department") }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($data)
                            @foreach ($data as $department)
                            <tr>
                                <td>{{ $department->company_name }}</td>
                                <td>{{ $department->department }}</td>
                                <td class="align-right">
                                    <a href="{{ url('fields/department/edit/'.$department->id) }}" class="ui circular basic icon button tiny" title="{{ __('Edit') }}"><i class="icon pencil alternate"></i></a>
                                    <a href="{{ url('fields/department/delete/'.$department->id) }}"
                                       class="ui circular basic icon button tiny js-delete-trigger"
                                       title="{{ __('Delete') }}"
                                       data-name="{{ $department->department }}"
                                       data-type="{{ __('department') }}"
                                       onclick="return false;">
                                        <i class="icon trash alternate outline"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        @endisset
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- ================= DELETE CONFIRMATION MODAL ================= -->
    <div class="ui basic modal" id="deleteConfirmModal">
        <div class="ui icon header" style="border:none;">
            <i class="trash alternate outline icon" style="color:#d93025;"></i>
            {{ __('Delete Record') }}
        </div>
        <div class="content" style="text-align:center; color:#e5e7eb;">
            <p style="font-size:15px; margin:0;">
                {{ __('Are you sure you want to delete') }}
                <strong id="deleteConfirmName" style="color:#fff;"></strong>?
            </p>
            <p style="font-size:12px; color:#9ca3af; margin-top:8px;">
                {{ __('This action cannot be undone.') }}
            </p>
        </div>
        <div class="actions" style="text-align:center; border:none; padding-bottom:20px;">
            <div class="ui red basic inverted cancel button">
                <i class="times icon"></i> {{ __('Cancel') }}
            </div>
            <a href="#" id="deleteConfirmButton" class="ui red inverted ok button">
                <i class="checkmark icon"></i> {{ __('Yes, Delete') }}
            </a>
        </div>
    </div>

    @endsection

    @section('scripts')
    <script type="text/javascript">
    $(document).ready(function () {

        var deptTable = $('#dataTables-example').DataTable({
            responsive: true,
            pageLength: 15,
            lengthChange: false,
            searching: true,
            ordering: true
        });

        $('#companyFilterSelect').dropdown();

        function applyDepartmentFilter() {
            var val = $('#companyFilterSelect').val();

            // Exact match on the Company column (index 0) so one company
            // name never accidentally matches a similarly-named one
            // (e.g. "JPINGOS FLAME GRILL" vs "JPININGOS FLAME GRILL NEW").
            var regex = val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '';

            deptTable.column(0).search(regex, true, false).draw();
        }

        $('#companyFilterSelect').on('change', applyDepartmentFilter);

        // No "All Companies" option - the first company in the list is
        // selected by default, so apply that filter immediately on load.
        applyDepartmentFilter();


        /*
        |--------------------------------------------------------------------------
        | Delete confirmation modal (replaces native confirm())
        |--------------------------------------------------------------------------
        */

        // Delegated on document so it keeps working for rows that live
        // inside the DataTable's redrawn/paginated DOM.
        $(document).on('click', '.js-delete-trigger', function (e) {
            e.preventDefault();

            var href = $(this).attr('href');
            var name = $(this).data('name');
            var type = $(this).data('type') || 'record';

            $('#deleteConfirmName').text(name || ('this ' + type));
            $('#deleteConfirmButton').attr('href', href);

            $('#deleteConfirmModal').modal('show');
        });

    });

    function validateFile() {
        var f = document.getElementById("csvfile").value;
        var d = f.lastIndexOf(".") + 1;
        var ext = f.substr(d, f.length).toLowerCase();
        if (ext == "csv") { } else {
            document.getElementById("csvfile").value="";
            $.notify({
            icon: 'ui icon times',
            message: "Please upload only CSV file format."},
            {type: 'danger',timer: 400});
        }
    }
    </script>

    @endsection