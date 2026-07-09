@extends('layouts.app')

@section('title', 'Import Students - SLSS')

@push('styles')
<style>
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .form-control {
            min-height: 44px;
            font-size: 16px; /* Prevents iOS zoom */
        }

        .btn {
            min-height: 44px;
            font-size: 0.95rem;
        }

        .alert {
            padding: 0.875rem;
            font-size: 0.9rem;
        }

        .alert h5 {
            font-size: 1rem;
        }

        .alert ul {
            font-size: 0.875rem;
            padding-left: 1.25rem;
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 0.75rem;
        }

        .card-header {
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
        }

        .alert {
            padding: 0.75rem;
            font-size: 0.85rem;
        }

        .alert h5 {
            font-size: 0.95rem;
        }

        .alert ul {
            font-size: 0.8rem;
            padding-left: 1rem;
        }

        /* Stack buttons vertically */
        .d-flex.gap-2 {
            flex-direction: column;
        }

        .d-flex.gap-2 .btn {
            width: 100%;
        }

        .form-label {
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-file-import me-2"></i>Import Students from CSV
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle me-2"></i>Instructions</h5>
            <ul class="mb-0">
                <li>Upload a CSV file with student data</li>
                <li>The CSV must include a header row with column names</li>
                <li>Duplicate students (by Birth Certificate PIN) will be skipped</li>
                <li>Maximum file size: 10MB</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="csv_file" class="form-label fw-bold">Select CSV File</label>
                <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i> Upload and Import
                </button>
                <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
