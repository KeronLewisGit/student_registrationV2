@extends('printables.layout')

@section('sheet')
@php($v = fn ($value, $format = null) => \App\Models\Student::printValue($value, $format))
@php($isNone = fn ($text) => $text === null || preg_match('/^(none|no|nil|n\/a|unknown)$/i', trim($text)))
<p class="mb-2" style="font-size: 0.8rem;">Rows highlighted in orange have a recorded allergy or medical condition.</p>
<table class="sheet-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Student</th>
            @if($class === 'all')<th>Class</th>@endif
            <th>Blood type</th>
            <th>Allergies</th>
            <th>Medical conditions</th>
            <th>Immunized</th>
            <th>Physical disabilities</th>
            <th>Parent phone</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $i => $student)
            @php($allergies = $v($student->student_allergies))
            @php($conditions = $v($student->student_medical_condition))
            @php($alert = !$isNone($allergies) || !$isNone($conditions))
            <tr class="{{ $alert ? 'alert-row' : '' }}">
                <td class="num">{{ $i + 1 }}</td>
                <td><strong>{{ $v($student->student_name, 'name') ?? 'Unnamed' }}</strong></td>
                @if($class === 'all')<td>{{ $student->current_class ?? '—' }}</td>@endif
                <td>{{ $v($student->student_bloodtype) ?? '—' }}</td>
                <td>{{ $allergies ?? '—' }}</td>
                <td>{{ $conditions ?? '—' }}</td>
                <td>{{ $v($student->student_immunization_status) ?? '—' }}</td>
                <td>{{ $v($student->student_physical_disabilities, 'yesno') ?? '—' }}</td>
                <td class="phone">{{ $v($student->mother_contact) ?? $v($student->father_contact) ?? $v($student->emergency_contact_number) ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="muted">No students match.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
