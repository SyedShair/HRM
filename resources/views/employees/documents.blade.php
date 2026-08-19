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
    <title>{{ $employee->firstname ?? '' }} {{ $employee->lastname ?? '' }} - Documents | {{ $appName }}</title>
    <meta name="description" content="Employee document management and Home Office compliance checklist">
@endsection

@section('styles')
<style>
    .compliance-bar-track {
        background: #e5e7eb;
        border-radius: 999px;
        height: 10px;
        overflow: hidden;
        margin: 10px 0 16px;
    }

    .compliance-bar-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.3s ease;
    }

    .required-badge {
        font-size: 10px;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-md-12">
            <h2 class="page-title uppercase">
                Documents — {{ $employee->firstname ?? '' }} {{ $employee->lastname ?? '' }}
                <a href="{{ url('employees') }}" class="ui grey button mini float-right">
                    <i class="ui icon arrow left"></i> Back to Employees
                </a>
            </h2>
        </div>
    </div>

    {{-- ================= REQUIRED DOCUMENTS CHECKLIST ================= --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Home Office / Employer Compliance Checklist</h3>
                </div>
                <div class="box-body">

                    @php
                        $required = collect($requiredDocuments ?? [])->where('required', true);
                        $requiredTotal = $required->count();
                        $requiredFound = $required->where('found', true)->count();
                        $percent = $requiredTotal > 0 ? round(($requiredFound / $requiredTotal) * 100) : 100;
                        $barColor = $percent == 100 ? '#16a34a' : ($percent >= 50 ? '#d97706' : '#dc2626');
                    @endphp

                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <strong>{{ $requiredFound }} of {{ $requiredTotal }} required documents on file</strong>
                        <span style="font-weight:600; color:{{ $barColor }};">{{ $percent }}%</span>
                    </div>

                    <div class="compliance-bar-track">
                        <div class="compliance-bar-fill" style="width:{{ $percent }}%; background:{{ $barColor }};"></div>
                    </div>

                    <table class="table table-striped" style="margin-bottom:0;">
                        <thead>
                            <tr>
                                <th>Document</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th>Value / File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requiredDocuments ?? [] as $req)
                                <tr>
                                    <td>
                                        {{ $req['label'] }}
                                        @if(!$req['required'])
                                            <div class="required-badge">Conditional — not always required</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($req['found'])
                                            <span class="ui green label"><i class="check icon"></i> On File</span>
                                        @elseif($req['required'])
                                            <span class="ui red label"><i class="times icon"></i> Missing</span>
                                        @else
                                            <span class="ui grey label">Not Provided</span>
                                        @endif
                                    </td>
                                    <td style="font-size:12px; color:#6b7280;">
                                        {{ $req['source'] === 'profile' ? 'Profile field' : 'Uploaded document' }}
                                    </td>
                                    <td>{{ $req['matched_file'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    {{-- ================= UPLOAD FORM ================= --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Upload Document</h3>
                </div>
                <div class="box-body">

                    @if(session('success'))
                        <div class="ui positive message">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="ui negative message">{{ session('error') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="ui error message">
                            <ul class="list">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ url('employee/document/upload') }}" method="post" enctype="multipart/form-data" class="ui form">
                        @csrf
                        <input type="hidden" name="people_id" value="{{ $employee->id }}">

                        <div class="two fields">
                            <div class="field">
                                <label>Document Type</label>
                                <select name="document_type_id" class="ui dropdown">
                                    <option value="">Other / Not Listed</option>
                                    @foreach($documentTypes ?? [] as $type)
                                        <option value="{{ $type->id }}">{{ $type->label }}{{ $type->is_required ? '' : ' (conditional)' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>Document Name / Description</label>
                                <input type="text" name="file_name" placeholder="e.g. Passport, Right to Work Share Code" required>
                            </div>
                        </div>

                        <div class="field">
                            <label>File</label>
                            <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                        </div>

                        {{--
                            Storage method - matches EmployeeDocumentController@store's
                            `storage_method` input ('storage' default via the Storage
                            disk, 'legacy' for the raw-move fallback). Defaults to
                            'storage'; legacy only exists for compatibility with the
                            old folder layout and shouldn't normally be needed.
                        --}}
                        <div class="field">
                            <label>Storage Method</label>
                            <div class="grouped fields">
                                <div class="field">
                                    <div class="ui radio checkbox">
                                        <input type="radio" name="storage_method" value="storage" checked>
                                        <label>Storage disk (recommended)</label>
                                    </div>
                                </div>
                                <div class="field">
                                    <div class="ui radio checkbox">
                                        <input type="radio" name="storage_method" value="legacy">
                                        <label>Legacy folder (compatibility fallback)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="ui green button small">
                            <i class="ui icon upload"></i> Upload
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- ================= DOCUMENT LIST ================= --}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Uploaded Documents</h3>
                </div>
                <div class="box-body">

                    <table class="ui celled table">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Type</th>
                                <th>Format</th>
                                <th>Preview</th>
                                <th>Download</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents ?? [] as $doc)
                                @php
                                    $ext = strtolower($doc->file_type);
                                    $isImage = $ext === 'image';
                                    $isPdf = $ext === 'pdf';
                                    $typeLabel = collect($documentTypes ?? [])->firstWhere('id', $doc->document_type_id)->label ?? '—';

                                    // FIX: EmployeeDocumentController@index already
                                    // resolves the correct URL per document (handling
                                    // both the Storage-disk and legacy raw-move cases)
                                    // and attaches it as $doc->file_url. This view was
                                    // instead calling asset($doc->file_path) directly,
                                    // which only ever produces a working link for the
                                    // legacy case - every Storage-disk upload (the
                                    // default method) rendered a broken Preview/
                                    // Download link here, same bug already fixed on
                                    // the personal documents page.
                                    $docUrl = $doc->file_url ?? asset($doc->file_path);
                                @endphp
                                <tr>
                                    <td>{{ $doc->file_name }}</td>
                                    <td>{{ $typeLabel }}</td>
                                    <td><span class="ui label">{{ strtoupper($doc->file_type) }}</span></td>
                                    <td>
                                        <a href="{{ $docUrl }}"
                                           class="doc-preview"
                                           style="color:#4183c4; cursor:pointer; text-decoration:none;"
                                           data-src="{{ $docUrl }}"
                                           data-type="{{ $isImage ? 'image' : ($isPdf ? 'pdf' : 'other') }}"
                                           data-name="{{ $doc->file_name }}">
                                            View {{ $isImage ? 'Image' : ($isPdf ? 'PDF' : 'File') }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ $docUrl }}" download
                                           class="ui green circular icon button tiny" title="Download">
                                            <i class="download icon"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <form action="{{ url('employee/document/'.$doc->id) }}" method="post" style="display:inline;"
                                              onsubmit="return confirm('Delete this document? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ui red circular icon button tiny" title="Delete">
                                                <i class="trash alternate outline icon"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align:center; font-style:italic; color:#999;">
                                        No documents uploaded yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

</div>

{{-- DOCUMENT PREVIEW MODAL --}}
<div id="imageModal" class="ui modal">
    <i class="close icon"></i>
    <div class="header" id="previewTitle">Document Preview</div>
    <div class="content" style="text-align:center;">
        <img id="previewImage" src="" style="max-width:100%; max-height:500px; display:none;">
        <iframe id="previewPdf" src="" style="width:100%; height:500px; border:none; display:none;"></iframe>
        <div id="previewFallback" style="display:none; padding: 40px 0;">
            <i class="file outline icon" style="font-size:48px; color:#999;"></i>
            <p>Preview isn't available for this file type.</p>
            <a id="previewFallbackLink" href="" target="_blank" class="ui blue button">Open in new tab</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

    $('.ui.dropdown').dropdown();
    $('.ui.radio.checkbox').checkbox();

    $('.doc-preview').on('click', function (e) {
        e.preventDefault();

        const src = $(this).data('src');
        const type = $(this).data('type');
        const name = $(this).data('name');

        $('#previewImage').attr('src', '').hide();
        $('#previewPdf').attr('src', '').hide();
        $('#previewFallback').hide();
        $('#previewFallbackLink').attr('href', '');

        $('#previewTitle').text(name || 'Document Preview');

        if (type === 'image') {
            $('#previewImage').attr('src', src).show();
        } else if (type === 'pdf') {
            $('#previewPdf').attr('src', src).show();
        } else {
            $('#previewFallbackLink').attr('href', src);
            $('#previewFallback').show();
        }

        $('#imageModal').modal('show');
    });

    $('#imageModal').modal({
        onHide: function () {
            $('#previewImage').attr('src', '');
            $('#previewPdf').attr('src', '');
        }
    });

});
</script>
@endsection