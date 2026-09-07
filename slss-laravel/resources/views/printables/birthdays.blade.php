@extends('printables.layout')

@section('sheet')
@php($v = fn ($value, $format = null) => \App\Models\Student::printValue($value, $format))
@php($yearStart = \App\Models\Student::currentAcademicYear())
@forelse($months as $month => $group)
    <h2 class="group">{{ \Carbon\Carbon::create(null, $month, 1)->format('F') }}</h2>
    <table class="sheet-table">
        <thead>
            <tr>
                <th style="width: 3rem">Day</th>
                <th>Student</th>
                <th style="width: 5rem">Class</th>
                <th style="width: 7rem">Date of birth</th>
                <th style="width: 7rem">Turns</th>
            </tr>
        </thead>
        <tbody>
            @foreach($group as $student)
                @php($birthdayYear = $month >= 9 ? $yearStart : $yearStart + 1)
                <tr>
                    <td>{{ $student->student_dob->format('j') }}</td>
                    <td><strong>{{ $v($student->student_name, 'name') ?? 'Unnamed' }}</strong></td>
                    <td>{{ $student->current_class ?? '—' }}</td>
                    <td>{{ $student->student_dob->format('d/m/Y') }}</td>
                    <td>{{ $birthdayYear - (int) $student->student_dob->format('Y') }} in {{ $birthdayYear }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@empty
    <p class="muted">No students with a recorded date of birth.</p>
@endforelse

@if($noDob->isNotEmpty())
    <h2 class="group">No date of birth recorded</h2>
    <p style="font-size: 0.8rem;">{{ $noDob->map(fn ($s) => $v($s->student_name, 'name'))->implode(', ') }}</p>
@endif
@endsection
