@extends('layouts.app')

@section('title', 'Activity Log - SLSS')
@section('page-title', 'Activity Log')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Activity Log</li>
@endsection

@push('styles')
<style>
    .preset-nav .nav-link { padding: 0.3rem 0.7rem; font-size: 0.875rem; }
    .preset-nav .badge { font-weight: 500; }
    .activity-table td { font-size: 0.875rem; vertical-align: top; }
    .activity-table .summary { max-width: 34rem; }
    .cat-auth { background: #dbeafe; color: #1e40af; }
    .cat-student { background: #ede9fe; color: #5b21b6; }
    .cat-export, .cat-print { background: #dcfce7; color: #166534; }
    .cat-document { background: #fef3c7; color: #92400e; }
    .cat-import { background: #cffafe; color: #155e75; }
    .cat-user { background: #fce7f3; color: #9d174d; }
    .cat-promotion, .cat-system { background: #e5e7eb; color: #374151; }
    .cat-badge { font-size: 0.72rem; padding: 0.2rem 0.5rem; border-radius: 999px; white-space: nowrap; }
    .action-failed { color: #b91c1c; font-weight: 600; }
</style>
@endpush

@section('content')
@php($activePreset = $filters['preset'] ?? (empty(array_filter($filters)) ? 'all' : null))

<ul class="nav nav-pills preset-nav mb-3 flex-wrap gap-1">
    @foreach($presets as $key => $preset)
        @php($presetCategories = array_filter(explode(',', $preset['filters']['category'] ?? '')))
        @php($presetCount = $presetCategories ? array_sum(array_intersect_key($counts, array_flip($presetCategories))) : array_sum($counts))
        <li class="nav-item">
            <a class="nav-link {{ $activePreset === $key ? 'active' : '' }}" href="{{ route('activity.index', ['preset' => $key]) }}">
                {{ $preset['label'] }}
                @if($key !== 'failed')<span class="badge bg-light text-dark ms-1">{{ number_format($presetCount) }}</span>@endif
            </a>
        </li>
    @endforeach
</ul>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            @if($student)
                <input type="hidden" name="student" value="{{ $student->id }}">
                <div class="col-md-2">
                    <span class="form-label small d-block mb-1">Student</span>
                    <div class="form-control-plaintext py-1">
                        <strong>{{ $student->student_name }}</strong>
                        <a href="{{ route('activity.index', array_diff_key($filters, ['student' => 1, 'preset' => 1])) }}" class="ms-1 small">clear</a>
                    </div>
                </div>
            @endif
            <div class="col-md-2 col-sm-6">
                <label for="category" class="form-label small mb-1">Category</label>
                <select name="category" id="category" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach($categories as $code => $label)
                        <option value="{{ $code }}" {{ ($filters['category'] ?? '') === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="action" class="form-label small mb-1">Action</label>
                <select name="action" id="action" class="form-select form-select-sm">
                    <option value="">Any</option>
                    @foreach($actions as $code => $label)
                        <option value="{{ $code }}" {{ ($filters['action'] ?? null) === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 col-sm-6">
                <label for="user" class="form-label small mb-1">User</label>
                <select name="user" id="user" class="form-select form-select-sm">
                    <option value="">Anyone</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ ($filters['user'] ?? null) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label for="q" class="form-label small mb-1">Contains</label>
                <input type="search" name="q" id="q" class="form-control form-control-sm" value="{{ $filters['q'] ?? '' }}" placeholder="name, email, text...">
            </div>
            <div class="col-md-1 col-sm-6">
                <label for="ip" class="form-label small mb-1">IP</label>
                <input type="text" name="ip" id="ip" class="form-control form-control-sm" value="{{ $filters['ip'] ?? '' }}" placeholder="e.g. 190.">
            </div>
            <div class="col-md-1 col-sm-6">
                <label for="from" class="form-label small mb-1">From</label>
                <input type="date" name="from" id="from" class="form-control form-control-sm" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-md-1 col-sm-6">
                <label for="to" class="form-label small mb-1">To</label>
                <input type="date" name="to" id="to" class="form-control form-control-sm" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-md-1 col-sm-6 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1" title="Apply"><i class="fas fa-search"></i></button>
                <a href="{{ route('activity.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear filters"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-history me-2"></i>{{ number_format($activities->total()) }} {{ Str::plural('entry', $activities->total()) }}</span>
        <small class="text-muted">Entries are kept for {{ \App\Models\ActivityLog::RETENTION_MONTHS }} months</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 activity-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Who</th>
                        <th>Category</th>
                        <th>Action</th>
                        <th>Record</th>
                        <th>Details</th>
                        <th>From</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td class="text-nowrap"><small>{{ $activity->created_at->format('d/m/Y') }}<br>{{ $activity->created_at->format('H:i:s') }}</small></td>
                            <td>
                                @if($activity->user_id)
                                    <a href="{{ route('activity.index', ['user' => $activity->user_id]) }}" class="text-decoration-none">{{ $activity->user_name }}</a>
                                @else
                                    {{ $activity->user_name }}
                                @endif
                            </td>
                            <td><span class="cat-badge cat-{{ $activity->category }}">{{ $activity->category_label }}</span></td>
                            <td class="{{ in_array($activity->action, ['login-failed', 'failed'], true) ? 'action-failed' : '' }}">{{ $activity->action_label }}</td>
                            <td>
                                @if($activity->subject_type === 'student')
                                    @if($activity->student)
                                        <a href="{{ route('students.show', $activity->student) }}">{{ $activity->student->student_name }}</a>
                                        @if($activity->student->trashed())<span class="badge bg-secondary ms-1">deleted</span>@endif
                                    @else
                                        <span class="text-muted">{{ $activity->subject_label ?? ('#' . $activity->subject_id) }} <span class="badge bg-secondary">removed</span></span>
                                    @endif
                                @elseif($activity->subject_label)
                                    @if($url = $activity->subjectUrl())<a href="{{ $url }}">{{ $activity->subject_label }}</a>@else{{ $activity->subject_label }}@endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="summary">
                                {{ $activity->summary }}
                                @if($activity->changes)
                                    <details>
                                        <summary class="small text-muted" style="cursor: pointer;">{{ count($activity->changes) }} detail(s)</summary>
                                        <ul class="small mb-0 mt-1" style="padding-left: 1.25rem;">
                                            @foreach($activity->changes as $field => $change)
                                                @if(is_array($change) && array_key_exists('from', $change))
                                                    <li><strong>{{ \App\Models\Student::fieldLabel($field) }}:</strong> <span class="text-muted">{{ $change['from'] ?? '(blank)' }}</span> &rarr; {{ $change['to'] ?? '(blank)' }}</li>
                                                @else
                                                    <li><strong>{{ \App\Models\Student::fieldLabel($field) }}:</strong> {{ is_array($change) ? implode(', ', $change) : $change }}</li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            </td>
                            <td><small class="text-muted text-nowrap">{{ $activity->ip ?? '—' }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted text-center py-4">No activity matches this selection.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($activities->hasPages())
        <div class="card-footer">{{ $activities->links() }}</div>
    @endif
</div>
@endsection
