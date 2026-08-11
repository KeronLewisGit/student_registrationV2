@extends('layouts.app')

@section('title', 'All Students Report - SLSS')

@section('page-title', 'All Students Report')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">All Students</li>
@endsection

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
    }
    .summary-box {
        background: var(--primary-light);
        border-radius: 8px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }

    @media (max-width: 768px) {
        .form-card {
            padding: 1rem;
        }
        .form-control,
        .form-select {
            min-height: 44px;
            font-size: 16px; /* Prevents iOS zoom */
        }
        .btn {
            min-height: 44px;
        }
    }

    @media (max-width: 576px) {
        .d-flex.gap-2 {
            flex-direction: column;
        }
        .d-flex.gap-2 .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">All Students</h2>
            <p class="text-muted mb-0">Export student records to a spreadsheet</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="summary-box">
        <i class="fas fa-info-circle me-2"></i>
        There {{ $totalStudents === 1 ? 'is' : 'are' }} currently
        <strong>{{ number_format($totalStudents) }}</strong>
        {{ Str::plural('student', $totalStudents) }} in the system.
        Leave the filters blank to export all of them.
    </div>

    <form method="GET" action="{{ route('reports.all-students.export') }}">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="year" class="form-label">Registration Year</label>
                <select name="year" id="year" class="form-select">
                    <option value="">All Years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ old('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="student_class" class="form-label">Form Class</label>
                <select name="student_class" id="student_class" class="form-select">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class }}" {{ old('student_class') == $class ? 'selected' : '' }}>{{ $class }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="format" class="form-label">File Format</label>
                <select name="format" id="format" class="form-select">
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="csv" {{ old('format') === 'csv' ? 'selected' : '' }}>CSV (.csv)</option>
                </select>
            </div>

            <div class="col-md-12">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                       value="{{ old('search') }}"
                       placeholder="Filter by name, birth certificate PIN, or SEA number">
                <small class="text-muted">Optional. Only students matching this term will be included.</small>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Generate &amp; Download
            </button>
        </div>
    </form>
</div>
@endsection
