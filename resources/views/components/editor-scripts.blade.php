{{-- Load jQuery and Summernote only when editor is needed --}}
@once
@push('editor-scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet" media="print" onload="this.media='all'">
<noscript><link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet"></noscript>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js" defer></script>
@endpush
@endonce
