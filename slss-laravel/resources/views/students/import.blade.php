@extends('layouts.app')

@section('title', 'Import Students - SLSS')

@push('styles')
<style>
    .template-box {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        background: var(--bg-body, #f8fafc);
    }

    .column-list {
        max-height: 200px;
        overflow-y: auto;
        font-size: 0.8rem;
        line-height: 1.9;
        word-break: break-word;
    }

    .column-list code {
        background: rgba(0,0,0,0.05);
        padding: 0.1rem 0.35rem;
        border-radius: 4px;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .template-box .btn {
            width: 100%;
        }
    }

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
                <li>Download the template below and fill in one student per row</li>
                <li>Keep the header row exactly as provided &mdash; renamed columns are ignored</li>
                <li><strong>student_name</strong> is required; rows without it are skipped</li>
                <li>Dates use the format <code>YYYY-MM-DD</code> (e.g. 2012-05-04)</li>
                <li>Duplicate students (by Birth Certificate PIN) will be skipped</li>
                <li>Maximum file size: 10MB</li>
            </ul>
        </div>

        <div class="template-box">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="mb-1"><i class="fas fa-file-csv me-2"></i>Import Template</h6>
                    <p class="text-muted mb-0 small">
                        A blank CSV with all {{ count($expectedHeaders) }} supported columns and one example row.
                    </p>
                </div>
                <a href="{{ route('import.template') }}" class="btn btn-outline-primary">
                    <i class="fas fa-download me-1"></i> Download Template
                </a>
            </div>

            <details class="mt-3">
                <summary class="small text-muted" style="cursor: pointer;">
                    View the {{ count($expectedHeaders) }} supported column names
                </summary>
                <div class="column-list mt-2">
                    @foreach($expectedHeaders as $header)
                        <code>{{ $header }}</code>@if(!$loop->last), @endif
                    @endforeach
                </div>
            </details>
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
