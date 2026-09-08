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
    /* Page-specific: report summary box (shared form styles live in css/slss.css) */
    .summary-box {
        background: var(--primary-light);
        border-radius: 8px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1">All Students</h2>
            <p class="text-muted mb-0">Export student records to a spreadsheet, filtered the same way as the student list.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Reports
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="summary-box">
        <i class="fas fa-info-circle me-2"></i>
        <strong>{{ number_format($activeStudents) }}</strong> active {{ Str::plural('student', $activeStudents) }}
        ({{ number_format($totalStudents) }} in total including those who have left or graduated).
        Leave the filters blank to export every active student.
    </div>

    <form method="GET" action="{{ route('reports.all-students.export') }}" id="reportForm">
        <div class="row g-3">
            <div class="col-md-3 col-sm-6">
                <label for="current_class" class="form-label">Class</label>
                <select name="current_class" id="current_class" class="form-select">
                    <option value="">All classes</option>
                    @foreach($currentClasses as $class)
                        <option value="{{ $class }}" {{ request('current_class') === $class ? 'selected' : '' }}>{{ $class }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 col-sm-6">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    @foreach($statuses as $code => $label)
                        <option value="{{ $code }}" {{ request('status', 'active') === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All statuses</option>
                </select>
            </div>

            <div class="col-md-2 col-sm-6">
                <label for="year" class="form-label">Registered</label>
                <select name="year" id="year" class="form-select">
                    <option value="">Any year</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 col-sm-6">
                <label for="student_class" class="form-label">Form 1 class (intake)</label>
                <select name="student_class" id="student_class" class="form-select">
                    <option value="">Any</option>
                    @foreach($classes as $class)
                        <option value="{{ $class }}" {{ request('student_class') == $class ? 'selected' : '' }}>{{ $class }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 col-sm-6">
                <label for="format" class="form-label">File format</label>
                <select name="format" id="format" class="form-select">
                    <option value="xlsx">Excel (.xlsx)</option>
                    <option value="csv" {{ request('format', 'xlsx') === 'csv' ? 'selected' : '' }}>CSV (.csv)</option>
                </select>
            </div>

            @php($activeAdvanced = array_filter(array_intersect_key((array) request('f', []), $advancedFilters), fn ($v) => trim((string) $v) !== ''))
            <div class="col-md-12">
                <button type="button" class="btn btn-link btn-sm px-0 text-decoration-none {{ $activeAdvanced ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" data-bs-target="#reportMoreFilters" aria-expanded="{{ $activeAdvanced ? 'true' : 'false' }}">
                    More filters @if($activeAdvanced)<span class="badge rounded-pill bg-primary">{{ count($activeAdvanced) }}</span>@endif <i class="fas fa-chevron-down ms-1 small"></i>
                </button>
                <div class="collapse {{ $activeAdvanced ? 'show' : '' }}" id="reportMoreFilters">
                    <div class="row g-2 mt-0">
                        @foreach($advancedFilters as $column => $label)
                            @if(!empty($advancedOptions[$column]))
                                <div class="col-lg-2 col-md-3 col-sm-6">
                                    <label for="f_{{ $column }}" class="form-label small mb-1">{{ $label }}</label>
                                    <select name="f[{{ $column }}]" id="f_{{ $column }}" class="form-select form-select-sm">
                                        <option value="">Any</option>
                                        @foreach($advancedOptions[$column] as $option)
                                            <option value="{{ $option }}" {{ strcasecmp((string) ($activeAdvanced[$column] ?? ''), $option) === 0 ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                       value="{{ request('search') }}"
                       placeholder="Name, PIN, SEA number, parent, phone, email...">
                <small class="text-muted">Optional. Matches the same fields as the student list search.</small>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span><i class="fas fa-columns me-2"></i>Columns to include <span class="badge bg-primary ms-1" id="columnCount"></span></span>
                <span class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-columns="all">Select all</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-columns="none">Clear</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-columns="essentials" title="Name, class, status, date of birth, PIN and the main contact numbers">Essentials only</button>
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($columnGroups as $group => $columns)
                        <div class="col-lg-3 col-md-4 col-sm-6 column-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="small">{{ $group }}</strong>
                                <span class="small">
                                    <a href="#" class="text-decoration-none group-toggle" data-mode="all">all</a>
                                    &middot;
                                    <a href="#" class="text-decoration-none group-toggle" data-mode="none">none</a>
                                </span>
                            </div>
                            @foreach($columns as $column)
                                <div class="form-check form-check-sm">
                                    <input class="form-check-input column-check" type="checkbox" name="columns[]" value="{{ $column }}" id="col_{{ $column }}"
                                           {{ !request()->has('columns') || in_array($column, (array) request('columns'), true) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="col_{{ $column }}">{{ $columnLabels[$column] }}</label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary" id="generateBtn">
                <i class="fas fa-file-excel me-1"></i> Generate &amp; Download
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var checks = Array.prototype.slice.call(document.querySelectorAll('.column-check'));
    var count = document.getElementById('columnCount');
    var button = document.getElementById('generateBtn');
    var essentials = ['id', 'student_name', 'current_class', 'enrolment_status', 'student_gender', 'student_dob',
        'student_birth_certificate_pin', 'mother_name', 'mother_contact', 'father_name', 'father_contact',
        'emergency_contact_name', 'emergency_contact_number'];

    function refresh() {
        var selected = checks.filter(function (c) { return c.checked; }).length;
        count.textContent = selected + ' of ' + checks.length;
        button.disabled = selected === 0;
        button.title = selected === 0 ? 'Choose at least one column' : '';
    }

    checks.forEach(function (c) { c.addEventListener('change', refresh); });

    document.querySelectorAll('[data-columns]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var mode = this.dataset.columns;
            checks.forEach(function (c) {
                c.checked = mode === 'all' || (mode === 'essentials' && essentials.indexOf(c.value) !== -1);
            });
            refresh();
        });
    });

    document.querySelectorAll('.group-toggle').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var on = this.dataset.mode === 'all';
            this.closest('.column-group').querySelectorAll('.column-check').forEach(function (c) { c.checked = on; });
            refresh();
        });
    });

    refresh();
})();
</script>
@endpush
