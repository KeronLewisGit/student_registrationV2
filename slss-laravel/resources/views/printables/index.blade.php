@extends('layouts.app')

@section('title', 'Printables - SLSS')
@section('page-title', 'Printables')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Printables</li>
@endsection

@push('styles')
<style>
    .printable-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--border-color);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .printable-icon {
        width: 44px; height: 44px; border-radius: 10px;
        background: var(--primary-light); color: var(--primary-color);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; margin-bottom: 0.75rem;
    }
    .printable-card h3 { font-size: 1.05rem; font-weight: 700; }
    .printable-card p { color: var(--text-muted); font-size: 0.9rem; flex-grow: 1; }
</style>
@endpush

@section('content')
<div class="mb-3">
    <h2 class="mb-1">Printables for teachers and the office</h2>
    <p class="text-muted mb-0">Pick a class (or all classes) and open a ready-to-print sheet in a new tab.</p>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form id="printableForm" class="row g-3 align-items-end" target="_blank">
            <div class="col-md-4">
                <label for="class" class="form-label">Class</label>
                <select name="class" id="class" class="form-select">
                    <option value="all">All classes ({{ array_sum($classes) }} students)</option>
                    @foreach($classes as $class => $total)
                        <option value="{{ $class }}">{{ $class }} ({{ $total }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Students</label>
                <select name="status" id="status" class="form-select">
                    <option value="active">Active only</option>
                    <option value="all">All statuses</option>
                </select>
            </div>
            <div class="col-md-4 text-muted small">
                Each sheet opens in a new tab and starts the print dialog from its Print button.
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    @foreach($printables as $key => $printable)
        <div class="col-md-6 col-lg-4">
            <div class="printable-card">
                <div class="printable-icon"><i class="fas {{ $printable['icon'] }}"></i></div>
                <h3>{{ $printable['name'] }}</h3>
                <p>{{ $printable['description'] }}</p>
                <button type="button" class="btn btn-primary mt-2 open-printable" data-url="{{ route('printables.show', $key) }}">
                    <i class="fas fa-print me-1"></i> Open
                </button>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.open-printable').forEach(function (button) {
        button.addEventListener('click', function () {
            const params = new URLSearchParams(new FormData(document.getElementById('printableForm')));
            window.open(this.dataset.url + '?' + params.toString(), '_blank');
        });
    });
</script>
@endpush
