@extends('layouts.default')

@section('meta')
    <title>Edit Company |Jpingos</title>
    <meta name="description" content="Edit company details and manage attached documents.">
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
    .existing-doc-list {
        margin: 0 0 16px 0;
        padding-left: 18px;
        font-size: 13px;
    }
    .existing-doc-list li {
        margin-bottom: 6px;
    }
    .existing-doc-list .doc-delete-link {
        color: #dc2626;
        margin-left: 8px;
    }
    .no-docs-label {
        color: #9ca3af;
        font-size: 12.5px;
        font-style: italic;
    }
    .ui.dividing.header.small-heading {
        font-size: 13px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-top: 20px;
        margin-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 6px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title uppercase">{{ __("Edit Company") }}</h2>
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

                    <form id="edit_company_form" action="{{ url('fields/company/update') }}" class="ui form" method="post" accept-charset="utf-8" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ $e_id }}">

                        <div class="field">
                            <label>{{ __("Company Name") }}</label>
                            <input class="uppercase" name="company" value="{{ old('company', $company->company) }}" type="text">
                        </div>
                        <div class="field">
                            <label>{{ __("Sponsor License Number") }}</label>
                            <input class="uppercase" name="licenceNo" value="{{ old('licenceNo', $company->licenceNo) }}" type="text">
                        </div>
                        <div class="field">
                            <label>{{ __("Address") }}</label>
                            <input class="uppercase" name="address" value="{{ old('address', $company->address) }}" type="text">
                        </div>

                        <h4 class="ui dividing header small-heading">{{ __('Existing Documents') }}</h4>
                        @if(isset($documents) && count($documents) > 0)
                            <ul class="existing-doc-list">
                                @foreach($documents as $doc)
                                    <li>
                                        <a href="{{ asset('storage/'.$doc->doc_file) }}" target="_blank">{{ $doc->doc_label }}</a>
                                        <a href="{{ url('fields/company/document/delete/'.$doc->id) }}" class="doc-delete-link" title="{{ __('Delete this document') }}" onclick="return confirm('{{ __('Delete this document?') }}');"><i class="icon trash alternate outline"></i></a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="no-docs-label">{{ __('No documents attached yet.') }}</p>
                        @endif

                        <h4 class="ui dividing header small-heading">{{ __('Add New Documents') }}</h4>
                        <div id="doc-entries"></div>
                        <button type="button" id="add-doc-entry" class="ui button small basic">
                            <i class="ui plus icon"></i>{{ __('Add Document') }}
                        </button>

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
                            <a href="{{ url('fields/company') }}" class="ui grey button small"><i class="ui times icon"></i> {{ __("Cancel") }}</a>
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