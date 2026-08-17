@extends('layouts.personal')
@section('content')
<h3>My Documents</h3>
<table class="ui celled table">
    <thead>
        <tr>
            <th>File Name</th>
            <th>Type</th>
            <th>Preview</th>
            <th>Download</th>
        </tr>
    </thead>
    <tbody>
        @forelse($documents as $doc)
            @php
                $ext = strtolower($doc->file_type);
                $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
                $isImage = in_array($ext, $imageExts);
                $isPdf = $ext === 'pdf';

                // FIX: file_path can come from either of two storage
                // methods (see EmployeeDocumentController@store):
                //   - 'uploads/employee_documents/...' -> saved directly
                //     under public/, so a plain asset() URL is correct.
                //   - 'employee_documents/...' (no 'uploads/' prefix)
                //     -> saved via the Storage 'public' disk, which is
                //     exposed at public/storage/... via the storage:link
                //     symlink, so it needs the 'storage/' prefix added.
                // Previously this always used asset($doc->file_path)
                // directly, which only ever worked for the first case -
                // any Storage-disk upload rendered a broken preview/
                // download link.
                $docUrl = str_starts_with($doc->file_path, 'uploads/')
                    ? asset($doc->file_path)
                    : asset('storage/' . $doc->file_path);
            @endphp
            <tr>
                {{-- FILE NAME --}}
                <td>{{ $doc->file_name }}</td>
                {{-- TYPE --}}
                <td>
                    <span class="ui label">{{ strtoupper($doc->file_type) }}</span>
                </td>
                {{-- PREVIEW --}}
                <td>
                    <a href="{{ $docUrl }}"
                       class="doc-preview"
                       style="color:#4183c4; cursor:pointer; text-decoration:none;"
                       data-src="{{ $docUrl }}"
                       data-type="{{ $isImage ? 'image' : ($isPdf ? 'pdf' : 'other') }}"
                       data-name="{{ $doc->file_name }}"
                       title="Preview">
                        View {{ $isImage ? 'Image' : ($isPdf ? 'PDF' : 'File') }}
                    </a>
                </td>
                {{-- DOWNLOAD --}}
                <td>
                    <a href="{{ $docUrl }}" download
                       class="ui green circular icon button tiny"
                       title="Download">
                        <i class="download icon"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align:center;">
                    No documents found
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- DOCUMENT PREVIEW MODAL --}}
<div id="imageModal" class="ui modal">
    <i class="close icon"></i>
    <div class="header" id="previewTitle">Document Preview</div>
    <div class="content" style="text-align:center;">

        {{-- Image preview --}}
        <img id="previewImage" src="" style="max-width:100%; max-height:500px; display:none;">

        {{-- PDF preview --}}
        <iframe id="previewPdf" src="" style="width:100%; height:500px; border:none; display:none;"></iframe>

        {{-- Fallback for unsupported types --}}
        <div id="previewFallback" style="display:none; padding: 40px 0;">
            <i class="file outline icon" style="font-size:48px; color:#999;"></i>
            <p>Preview isn't available for this file type.</p>
            <a id="previewFallbackLink" href="" target="_blank" class="ui blue button">
                Open in new tab
            </a>
        </div>

    </div>
</div>
@endsection
@section('scripts')
{{-- jQuery + Semantic UI must be loaded in layout --}}
<script>
$(document).ready(function () {

    $('.doc-preview').on('click', function (e) {
        e.preventDefault();

        const src = $(this).data('src');
        const type = $(this).data('type');
        const name = $(this).data('name');

        // Reset all preview elements first
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

    // Stop PDF from continuing to load in the background once modal is closed
    $('#imageModal').modal({
        onHide: function () {
            $('#previewImage').attr('src', '');
            $('#previewPdf').attr('src', '');
        }
    });

});
</script>
@endsection