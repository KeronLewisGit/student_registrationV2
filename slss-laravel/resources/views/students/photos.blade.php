@extends('layouts.app')

@section('title', 'Bulk Photo Upload - SLSS')
@section('page-title', 'Bulk Photo Upload')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Bulk Photo Upload</li>
@endsection

@section('content')
<div class="form-card">
    <h2 class="mb-1">Upload passport photos in bulk</h2>
    <p class="text-muted">
        <strong>{{ $withoutPhoto }}</strong> active students currently have no usable photo.
        Select many image files at once; each one is matched to a student by its file name.
    </p>

    <div class="alert alert-info">
        <strong>Name each file so it can be matched</strong>, using any of these:
        <ul class="mb-0 mt-1">
            <li>the birth certificate PIN, e.g. <code>7235365011.jpg</code></li>
            <li>the student ID shown on the profile page, e.g. <code>id-123.jpg</code> or <code>123.jpg</code></li>
            <li>the student's full name, e.g. <code>Jah-Marley Lewis.jpg</code> (only when exactly one student matches)</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('students.photos.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="photos" class="form-label">Photo files (JPG, PNG or WEBP, up to 5 MB each, up to 200 files)</label>
            <input type="file" name="photos[]" id="photos" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required>
            @error('photos')<div class="text-danger small">{{ $message }}</div>@enderror
            @error('photos.*')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="replace" id="replace" value="1">
            <label class="form-check-label" for="replace">Replace photos for students who already have one</label>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload and match</button>
    </form>
</div>

@if($results)
    <div class="row g-3 mt-1">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header text-success"><i class="fas fa-check-circle me-1"></i> Attached ({{ count($results['matched']) }})</div>
                <ul class="list-group list-group-flush small">
                    @forelse($results['matched'] as $line)<li class="list-group-item">{{ $line }}</li>@empty<li class="list-group-item text-muted">None</li>@endforelse
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header text-warning"><i class="fas fa-forward me-1"></i> Skipped ({{ count($results['skipped']) }})</div>
                <ul class="list-group list-group-flush small">
                    @forelse($results['skipped'] as $line)<li class="list-group-item">{{ $line }}</li>@empty<li class="list-group-item text-muted">None</li>@endforelse
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header text-danger"><i class="fas fa-question-circle me-1"></i> Not matched ({{ count($results['unmatched']) }})</div>
                <ul class="list-group list-group-flush small">
                    @forelse($results['unmatched'] as $line)<li class="list-group-item">{{ $line }}</li>@empty<li class="list-group-item text-muted">None</li>@endforelse
                </ul>
                @if($results['unmatched'])
                    <div class="card-footer small text-muted">Rename these files with the PIN or student ID and upload them again.</div>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection
