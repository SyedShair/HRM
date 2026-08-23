@extends('layouts.default')
 @php
            $appSettings = \App\Classes\table::settings()->where('id', 1)->first();
            $appName = !empty($appSettings->app_name) ? $appSettings->app_name : 'Comapny';
        @endphp
@section('meta')
    <title>{{ $company->company }} - Documents | {{ $appName }}</title>
    <meta name="description" content="Manage company documents">
@endsection

@section('styles')
<style>
    .box.box-success {
        border-radius: 8px;
        border-top: 3px solid #16a34a;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .box-header.with-border {
        font-size: 15px;
        font-weight: 600;
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        background: #fafafa;
    }
    .box-body { padding: 20px; }

    .company-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .company-banner .company-meta {
        font-size: 13px;
        color: #6b7280;
    }

    .doc-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 12px;
        background: #fcfcfd;
        transition: box-shadow .15s ease;
    }
    .doc-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .doc-card .doc-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: #eef2ff;
        color: #4f46e5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .doc-card .doc-info {
        flex: 1;
        min-width: 0;
    }
    .doc-card .doc-label {
        font-weight: 600;
        font-size: 14px;
        color: #1f2937;
    }
    .doc-card .doc-filename {
        font-size: 12px;
        color: #9ca3af;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .doc-card .doc-actions {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #9ca3af;
    }
    .empty-state i.icon {
        font-size: 32px;
        margin-bottom: 8px;
        display: block;
    }

    #upload-entries .doc-entry {
        position: relative;
        border: 1px solid #e5e7eb;
        border-radius: 6px;
        padding: 12px 12px 4px 12px;
        margin-bottom: 12px;
        background: #fcfcfd;
    }
    #upload-entries .doc-entry .remove-doc-entry {
        position: absolute;
        top: 8px;
        right: 10px;
        cursor: pointer;
        color: #9ca3af;
        font-size: 13px;
    }
    #upload-entries .doc-entry .remove-doc-entry:hover {
        color: #dc2626;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="company-banner">
                <div>
                    <h2 class="page-title uppercase" style="margin-bottom:2px;">{{ $company->company }}</h2>
                    <div class="company-meta">
                        {{ __('Document Management') }}
                        @if($company->licenceNo) &middot; {{ __('Licence') }}: {{ $company->licenceNo }} @endif
                    </div>
                </div>
                <a href="{{ url('fields/company') }}" class="ui grey button small">
                    <i class="ui arrow left icon"></i>{{ __('Back to Companies') }}
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="ui positive message"><i class="close icon"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="ui negative message"><i class="close icon"></i>{{ session('error') }}</div>
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

    <div class="row">
        <div class="col-md-5">
            <div class="box box-success">
                <div class="box-header with-border">{{ __('Upload Documents') }}</div>
                <div class="box-body">
                    <form action="{{ url('fields/company/'.$company->id.'/documents/add') }}" method="post" class="ui form" enctype="multipart/form-data">
                        @csrf
                        <div id="upload-entries"></div>
                        <button type="button" id="add-upload-entry" class="ui button small basic" style="margin-bottom:16px;">
                            <i class="ui plus icon"></i>{{ __('Add Another Document') }}
                        </button>
                        <div class="actions">
                            <button type="submit" class="ui positive button small">
                                <i class="ui upload icon"></i>{{ __('Upload') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="box box-success">
                <div class="box-header with-border">
                    {{ __('Documents') }} <span style="font-weight:400; color:#9ca3af;">({{ $documents->count() }})</span>
                </div>
                <div class="box-body">
                    @forelse($documents as $doc)
                        <div class="doc-card">
                            <div class="doc-icon">
                                <i class="icon file alternate outline"></i>
                            </div>
                            <div class="doc-info">
                                <div class="doc-label">{{ $doc->doc_label }}</div>
                                <div class="doc-filename">{{ basename($doc->doc_file) }}</div>
                            </div>
                            <div class="doc-actions">
                                <a href="{{ asset('storage/'.$doc->doc_file) }}" target="_blank" class="ui circular basic icon button tiny" title="{{ __('View') }}">
                                    <i class="icon eye"></i>
                                </a>
                                <button type="button" class="ui circular basic icon button tiny edit-doc-btn"
                                        data-id="{{ $doc->id }}" title="{{ __('Edit') }}">
                                    <i class="icon pencil alternate"></i>
                                </button>
                                <a href="{{ url('fields/company/document/delete/'.$doc->id) }}" class="ui circular basic icon button tiny" title="{{ __('Delete') }}"
                                   onclick="return confirm('{{ __('Delete this document?') }}');">
                                    <i class="icon trash alternate outline"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="icon file outline"></i>
                            {{ __('No documents uploaded for this company yet.') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============== EDIT DOCUMENT MODAL ============== -->
<div class="ui modal" id="edit-doc-modal">
    <div class="header">{{ __('Edit Document') }}</div>
    <div class="content">
        <form id="edit-doc-form" action="{{ url('fields/company/document/update') }}" method="post" class="ui form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" id="edit-doc-id">
            <div class="field">
                <label>{{ __('Document Label') }}</label>
                <input type="text" name="doc_label" id="edit-doc-label" class="uppercase" required>
            </div>
            <div class="field">
                <label>{{ __('Current File') }}</label>
                <div>
                    <a href="#" id="edit-doc-current-file" target="_blank" style="font-size:13px;"></a>
                </div>
            </div>
            <div class="field">
                <label>{{ __('Replace File') }} <span class="help">{{ __('Leave empty to keep the current file') }}</span></label>
                <input type="file" name="company_doc" accept="image/png, image/jpeg, image/jpg, application/pdf" onchange="validateDocFile(this)">
            </div>
        </form>
    </div>
    <div class="actions">
        <button class="ui grey button" type="button" onclick="$('#edit-doc-modal').modal('hide')">{{ __('Cancel') }}</button>
        <button class="ui positive button" type="submit" form="edit-doc-form">
            <i class="check icon"></i>{{ __('Save Changes') }}
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
let uploadEntryCount = 0;

document.addEventListener('DOMContentLoaded', function () {
    addUploadEntry();
    document.getElementById('add-upload-entry').addEventListener('click', function () {
        addUploadEntry();
    });
});

function addUploadEntry() {
    uploadEntryCount++;

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

    document.getElementById('upload-entries').appendChild(wrapper);

    wrapper.querySelector('.remove-doc-entry').addEventListener('click', function () {
        // Always keep at least one row so the form never submits with
        // zero file inputs.
        if (document.querySelectorAll('#upload-entries .doc-entry').length > 1) {
            wrapper.remove();
        }
    });
}

function validateDocFile(input) {
    var f = input.value;
    var d = f.lastIndexOf(".") + 1;
    var ext = f.substr(d, f.length).toLowerCase();
    if (ext == "jpg" || ext == "jpeg" || ext == "png" || ext == "pdf") {
        // valid
    } else {
        input.value = "";
        $.notify({
            icon: 'ui icon times',
            message: "Please upload only jpg/jpeg, png, or pdf files."
        }, { type: 'danger', timer: 400 });
    }
}

/* ================= EDIT DOCUMENT MODAL ================= */
$(document).on('click', '.edit-doc-btn', function () {
    const docId = $(this).data('id');

    $.ajax({
        url: '{{ url("fields/company/document/edit") }}/' + docId,
        type: 'GET',
        dataType: 'json',
        success: function (doc) {
            $('#edit-doc-id').val(doc.id);
            $('#edit-doc-label').val(doc.doc_label);
            $('#edit-doc-current-file').attr('href', doc.file_url).text(doc.file_name);
            $('#edit-doc-form input[type="file"]').val('');
            $('#edit-doc-modal').modal('show');
        },
        error: function () {
            $.notify({
                icon: 'ui icon times',
                message: "Could not load this document. Please try again."
            }, { type: 'danger', timer: 400 });
        }
    });
});
</script>
@endsection