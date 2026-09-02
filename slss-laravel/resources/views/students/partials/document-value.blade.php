{{-- Renders an uploaded-document field as a link when it points at a real file,
     otherwise as plain text. Usage: @include('students.partials.document-value', ['value' => $student->student_sea_slip]) --}}
@php($documentUrl = \App\Models\Student::documentUrl($value ?? null))
@if($documentUrl)
    <a href="{{ $documentUrl }}" target="_blank" rel="noopener">
        <i class="fas fa-file-alt me-1"></i>View document
    </a>
@else
    {{ $value ?? 'N/A' }}
@endif
