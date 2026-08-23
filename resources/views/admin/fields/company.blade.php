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
        <title>Companies | {{ $appName }}</title>
        <meta name="description" content="Workday companies, view companies, and export or download companies.">
    @endsection

    @section('styles')
    <style>
        .doc-entry {
            position: relative;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 12px 4px 12px;
            margin-bottom: 12px;
            background: #fcfcfd;
        }
        .doc-entry .remove-doc-entry {
            position: absolute;
            top: 8px;
            right: 10px;
            cursor: pointer;
            color: #9ca3af;
            font-size: 13px;
        }
        .doc-entry .remove-doc-entry:hover {
            color: #dc2626;
        }
        #add-doc-entry {
            margin-bottom: 16px;
        }
    </style>
    @endsection

    @section('content')
    @include('admin.modals.modal-import-company')

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h2 class="page-title uppercase">{{ __("Add Company") }}
                    <button class="ui basic button mini offsettop5 btn-import float-right"><i class="ui icon upload"></i> {{ __("Import") }}</button>
                    <a href="{{ url('export/fields/company' )}}" class="ui basic button mini offsettop5 btn-export float-right"><i class="ui icon download"></i> {{ __("Export") }}</a>
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
                        <form id="add_company_form" action="{{ url('fields/company/add') }}" class="ui form" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                            @csrf
                            <div class="field">
                                <label>{{ __("Company Name") }} <span class="help">e.g. "Apple Corporation"</span></label>
                                <input class="uppercase" name="company" value="" type="text">
                            </div>
                             <div class="field">
                                <label>{{ __("Sponsor License Number") }} <span class="help">e.g. "Apple296623Ft"</span></label>
                                <input class="uppercase" name="licenceNo" value="" type="text">
                            </div>
                             <div class="field">
                                <label>{{ __("Address") }} <span class="help">e.g. "Ng8 Nottingham"</span></label>
                                <input class="uppercase" name="address" value="" type="text">
                            </div>

                            <div class="field">
                                <label>{{ __("Company Documents") }} <span class="help">{{ __("e.g. sponsor licence certificate, incorporation certificate") }}</span></label>
                                <div id="doc-entries"></div>
                                <button type="button" id="add-doc-entry" class="ui button small basic">
                                    <i class="ui plus icon"></i>{{ __('Add Document') }}
                                </button>
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
                    <table width="100%" class="table table-striped table-hover" id="dataTables-example">
                        <thead>
                            <tr>
                                <th>{{ __("Company") }}</th>
                                 <th>{{ __("Sponsor Licence No") }}</th>
                                 <th>{{ __("Address") }}</th>
                                 <th>{{ __("Documents") }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @isset($data)
                                @foreach ($data as $company)
                                <tr>
                                    <td>{{ $company->company }}</td>
                                    <td>{{ $company->licenceNo }}</td>
                                    <td>{{ $company->address }}</td>
                                    <td>
                                        @php $companyDocs = $documents[$company->id] ?? []; @endphp
                                        <a href="{{ url('fields/company/'.$company->id.'/documents') }}" class="ui tiny basic button">
                                            <i class="icon folder open outline"></i> {{ __('Manage') }} ({{ count($companyDocs) }})
                                        </a>
                                    </td>
                                    <td class="align-right"> 
                                        <a href="{{ url('fields/company/edit/'.$company->id) }}" class="ui circular basic icon button tiny" title="{{ __('Edit') }}"><i class="icon pencil alternate"></i></a>
                                        <a href="{{ url('fields/company/delete/'.$company->id) }}" class="ui circular basic icon button tiny" title="{{ __('Delete') }}" onclick="return confirm('{{ __('Delete this company and all its documents?') }}');"><i class="icon trash alternate outline"></i></a>
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

    @endsection

    @section('scripts')
    <script type="text/javascript">
    $('#dataTables-example').DataTable({responsive: true,pageLength: 15,lengthChange: false,searching: true,ordering: true});
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

    /* ================================================================
       MULTIPLE COMPANY DOCUMENTS (Add Company form)
       Repeatable label + file rows, same pattern as the employee
       address-history documents.
       ================================================================ */
    let docEntryCount = 0;

    document.addEventListener("DOMContentLoaded", function () {
        addDocEntry();
        document.getElementById('add-doc-entry').addEventListener('click', function () {
            addDocEntry();
        });
    });

    function addDocEntry() {
        docEntryCount++;

        const wrapper = document.createElement('div');
        wrapper.className = 'doc-entry';

        wrapper.innerHTML = `
            <span class="remove-doc-entry" title="Remove"><i class="ui trash icon"></i></span>
            <div class="field">
                <label>{{ __('Document Label') }}</label>
                <input type="text" name="doc_label[]" class="uppercase" placeholder="e.g. Sponsor Licence Certificate">
            </div>
            <div class="field">
                <label>{{ __('File') }}</label>
                <input type="file" name="company_doc[]" accept="image/png, image/jpeg, image/jpg, application/pdf" onchange="validateDocFile(this)">
            </div>
        `;

        document.getElementById('doc-entries').appendChild(wrapper);

        wrapper.querySelector('.remove-doc-entry').addEventListener('click', function () {
            wrapper.remove();
        });
    }

    function validateDocFile(input) {
        var f = input.value;
        var d = f.lastIndexOf(".") + 1;
        var ext = f.substr(d, f.length).toLowerCase();
        if (ext == "jpg" || ext == "jpeg" || ext == "png" || ext == "pdf") {
            // valid format
        } else {
            input.value = "";
            $.notify({
                icon: 'ui icon times',
                message: "Please upload only jpg/jpeg, png, or pdf files."
            }, { type: 'danger', timer: 400 });
        }
    }
    </script>

    @endsection