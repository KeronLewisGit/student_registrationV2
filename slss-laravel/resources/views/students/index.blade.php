@extends('layouts.app')

@section('title', 'Student Records - SLSS')

@push('styles')
<style>
    .filter-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .profile-card {
        position: relative;
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        min-height: 800px;
    }

    .profile-card::after {
        content: "";
        position: absolute;
        inset: 80px;
        background: url('{{ asset('images/Official Document1.png') }}') center/contain no-repeat;
        opacity: 0.06;
        pointer-events: none;
        z-index: 0;
    }

    .profile-inner {
        position: relative;
        z-index: 1;
    }

    .passport-photo {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 12px;
        border: 2px solid #e5e7eb;
    }

    .school-logo {
        width: 160px;
        height: auto;
    }

    .section-card {
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.25rem;
        margin-top: 1.25rem;
    }

    .section-card h5 {
        font-size: 0.875rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .section-card p {
        font-size: 0.875rem;
        color: #1f2937;
        margin: 0;
    }

    .action-buttons {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 10;
        display: flex;
        gap: 0.5rem;
    }

    @media print {
        .filter-card, .action-buttons {
            display: none !important;
        }
        .profile-card {
            box-shadow: none;
            page-break-after: always;
            padding-top: 140px !important;
        }
        .profile-card:last-child {
            page-break-after: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="filter-card no-print">
    <form method="GET" action="{{ route('students.index') }}" class="row g-3" id="filterForm">
        <div class="col-md-3">
            <label for="year" class="form-label">Registration Year</label>
            <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                <option value="">All Years</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label for="student_class" class="form-label">Form Class</label>
            <select name="student_class" id="student_class" class="form-select" onchange="this.form.submit()">
                <option value="0">All Students</option>
                @foreach($classes as $class)
                    <option value="{{ $class }}" {{ request('student_class') == $class ? 'selected' : '' }}>
                        Form {{ $class }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4">
            <label for="student_name" class="form-label">Student Name</label>
            <select name="student_name" id="student_name" class="form-select" onchange="this.form.submit()">
                <option value="0">Student Name</option>
                @foreach($studentNames as $name)
                    <option value="{{ $name }}" {{ request('student_name') == $name ? 'selected' : '' }}>
                        {{ ucwords(strtolower($name)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="button" class="btn btn-secondary flex-fill" onclick="window.print()">
                <i class="fas fa-print"></i> Print All
            </button>
            <a href="{{ route('students.bulk-pdf', request()->all()) }}" class="btn btn-primary flex-fill">
                <i class="fas fa-file-pdf"></i> Bulk PDF
            </a>
        </div>
    </form>

    @can('edit-students')
    <div class="mt-3">
        <a href="{{ route('students.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Add New Student
        </a>
    </div>
    @endcan
</div>

@if($students->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>No students found for the selected filter(s).
    </div>
@else
    @foreach($students as $student)
        <div class="profile-card">
            <div class="action-buttons no-print">
                <a href="{{ route('students.pdf', $student) }}" class="btn btn-sm btn-success" title="Generate PDF">
                    <i class="fas fa-file-pdf"></i>
                </a>
                <a href="{{ route('students.print', $student) }}" class="btn btn-sm btn-secondary" title="Print" target="_blank">
                    <i class="fas fa-print"></i>
                </a>
                @can('edit-students')
                <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-primary" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>
                @endcan
            </div>

            <div class="profile-inner">
                <!-- Header Row -->
                <div class="row align-items-start mb-4">
                    <div class="col-md-3">
                        <h6 class="fw-bold mb-2">Passport Size Photo</h6>
                        @if($student->student_passport_photo)
                            <img src="{{ asset($student->student_passport_photo) }}" alt="Passport" class="passport-photo">
                        @else
                            <img src="{{ asset('images/noimage.jpg') }}" alt="No Image" class="passport-photo">
                        @endif
                    </div>
                    <div class="col-md-6 text-center">
                        <h2 class="fw-bold mb-2" style="font-size: 2rem; color: #1f2937;">
                            Success Laventille Secondary School<br>Eastern Main Road
                        </h2>
                        <p class="text-muted">Official Student Record</p>
                    </div>
                    <div class="col-md-3 text-end">
                        <img src="{{ asset('images/successlogo.png') }}" alt="SLSS Logo" class="school-logo">
                    </div>
                </div>

                @include('students.partials.profile-sections', ['student' => $student])
            </div>
        </div>
    @endforeach
@endif
@endsection
