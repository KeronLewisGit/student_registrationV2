@extends('layouts.app')

@section('title', 'Recently Deleted - SLSS')

@section('page-title', 'Recently Deleted')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
    <li class="breadcrumb-item active">Recently Deleted</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-trash-restore me-2" aria-hidden="true"></i>Recently Deleted Students</span>
        <a href="{{ route('students.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1" aria-hidden="true"></i> Back to Students
        </a>
    </div>
    <div class="card-body">
        @if($students->isEmpty())
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                No deleted students. Records deleted from the student list appear here and can be restored.
            </div>
        @else
            <p class="text-muted">
                Deleted records are kept and can be restored at any time. Restoring puts the student
                back in the main list with all of their information intact.
            </p>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Student Name</th>
                            <th scope="col">Form Class</th>
                            <th scope="col">Registration Date</th>
                            <th scope="col">Deleted</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td><strong>{{ ucwords(strtolower($student->student_name)) }}</strong></td>
                            <td>{{ $student->form_1_class ?: '—' }}</td>
                            <td>{{ $student->formatted_registration_date }}</td>
                            <td>
                                <span title="{{ $student->deleted_at->format('d/m/Y H:i') }}">
                                    {{ $student->deleted_at->diffForHumans() }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('students.restore', $student->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-trash-restore me-1" aria-hidden="true"></i>Restore
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
