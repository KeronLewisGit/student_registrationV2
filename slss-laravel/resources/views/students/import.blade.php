@extends('layouts.app')

@section('title', 'Import Students - SLSS')

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
