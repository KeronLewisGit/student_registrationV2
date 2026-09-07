{{-- Renders an uploaded-document field as a link when it points at a real file,
     otherwise as plain text. Usage: @include('students.partials.document-value', ['value' => $student->student_sea_slip]) --}}
@php($documentUrl = \App\Models\Student::documentUrl($value ?? null))
@if($documentUrl)
    @can('view-sensitive')
        <a href="{{ $documentUrl }}" target="_blank" rel="noopener" class="no-print">
            <i class="fas fa-file-alt me-1"></i>View document
        </a><span class="print-only">On file</span>
    @else
        On file
    @endcan
@else
    {{ filled($value) && !\App\Models\Student::isPlaceholder(trim((string) $value)) ? $value : 'N/A' }}
@endif
