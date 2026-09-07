@extends('printables.layout')

@section('sheet')
@php($v = fn ($value, $format = null) => \App\Models\Student::printValue($value, $format))
<table class="sheet-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Student</th>
            @if($class === 'all')<th>Class</th>@endif
            <th>Gender</th>
            <th>Date of birth</th>
            <th>PIN</th>
            <th>Parent / guardian phone</th>
            <th style="width: 9%"></th>
            <th style="width: 9%"></th>
            <th style="width: 9%"></th>
            <th style="width: 9%"></th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $i => $student)
            @php($parent = $v($student->mother_contact) ?? $v($student->father_contact) ?? $v($student->emergency_contact_number))
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td><strong>{{ $v($student->student_name, 'name') ?? 'Unnamed' }}</strong></td>
                @if($class === 'all')<td>{{ $student->current_class ?? '—' }}</td>@endif
                <td>{{ $v($student->student_gender) ?? '—' }}</td>
                <td>{{ $v($student->student_dob) ?? '—' }}</td>
                <td>{{ $v($student->student_birth_certificate_pin) ?? '—' }}</td>
                <td class="phone">{{ $parent ?? '—' }}</td>
                <td class="blank"></td><td class="blank"></td><td class="blank"></td><td class="blank"></td>
            </tr>
        @empty
            <tr><td colspan="11" class="muted">No students match.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
