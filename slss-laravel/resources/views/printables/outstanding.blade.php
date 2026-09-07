@extends('printables.layout')

@section('page-size', 'Letter landscape')

@section('sheet')
@php($v = fn ($value, $format = null) => \App\Models\Student::printValue($value, $format))
<p class="mb-2" style="font-size: 0.8rem;">
    {{ $rows->count() }} of {{ $students->count() }} students are missing at least one essential item.
    A dot marks an item still to be collected.
</p>
<table class="sheet-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Student</th>
            @if($class === 'all')<th>Class</th>@endif
            @php($short = ['photo' => 'Photo', 'dob' => 'DOB', 'gender' => 'Gender', 'pin' => 'PIN', 'birth_cert' => 'Birth cert.', 'address' => 'Address', 'class' => 'Class', 'sea' => 'SEA no.', 'parent' => 'Parent contact', 'emergency' => 'Emerg. contact', 'medical' => 'Medical'])
            @foreach($items as $key => $item)
                <th style="text-align: center;" title="{{ $item['label'] }}">{{ $short[$key] ?? $item['label'] }}</th>
            @endforeach
            <th>Parent phone</th>
            <th style="width: 10%">Done</th>
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $i => $row)
            @php($student = $row['student'])
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td><strong>{{ $v($student->student_name, 'name') ?? 'Unnamed' }}</strong><br><span class="muted">{{ $row['completeness']['percent'] }}% complete</span></td>
                @if($class === 'all')<td>{{ $student->current_class ?? '—' }}</td>@endif
                @foreach($items as $key => $item)
                    <td style="text-align: center;">{!! isset($row['completeness']['missing'][$key]) ? '&#9679;' : '' !!}</td>
                @endforeach
                <td class="phone">{{ $v($student->mother_contact) ?? $v($student->father_contact) ?? $v($student->emergency_contact_number) ?? '—' }}</td>
                <td class="blank"></td>
            </tr>
        @empty
            <tr><td colspan="{{ 5 + count($items) }}" class="muted">Every record in this selection has all essential items.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
