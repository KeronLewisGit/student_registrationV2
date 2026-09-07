@extends('printables.layout')

@section('page-size', 'Letter landscape')

@section('sheet')
@php($v = fn ($value, $format = null) => \App\Models\Student::printValue($value, $format))
@php($isNone = fn ($text) => $text === null || preg_match('/^(none|no|nil|n\/a|unknown)$/i', trim($text)))
<table class="sheet-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Student</th>
            @if($class === 'all')<th>Class</th>@endif
            <th>Mother</th>
            <th>Mother phone</th>
            <th>Father</th>
            <th>Father phone</th>
            <th>Emergency contact</th>
            <th>Relation</th>
            <th>Emergency phone</th>
            <th>Medical notes</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $i => $student)
            @php($medical = array_filter([$v($student->student_allergies), $v($student->student_medical_condition)], fn ($t) => !$isNone($t)))
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td><strong>{{ $v($student->student_name, 'name') ?? 'Unnamed' }}</strong></td>
                @if($class === 'all')<td>{{ $student->current_class ?? '—' }}</td>@endif
                <td>{{ $v($student->mother_name, 'name') ?? '—' }}@if($v($student->is_mother_active_or_deceased) === 'Deceased') <span class="muted">(deceased)</span>@endif</td>
                <td class="phone">{{ $v($student->mother_contact) ?? '—' }}</td>
                <td>{{ $v($student->father_name, 'name') ?? '—' }}@if($v($student->is_father_active_or_deceased) === 'Deceased') <span class="muted">(deceased)</span>@endif</td>
                <td class="phone">{{ $v($student->father_contact) ?? '—' }}</td>
                <td>{{ $v($student->emergency_contact_name, 'name') ?? '—' }}</td>
                <td>{{ $v($student->emergency_contact_relation_to_student, 'name') ?? '—' }}</td>
                <td class="phone">{{ $v($student->emergency_contact_number) ?? '—' }}</td>
                <td>{{ $medical ? implode('; ', $medical) : '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="11" class="muted">No students match.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
