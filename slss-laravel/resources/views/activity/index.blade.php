@extends('layouts.app')

@section('title', 'Activity Log - SLSS')
@section('page-title', 'Activity Log')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Activity Log</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-filter me-2"></i>Filter activity</div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            @if($student)
                <input type="hidden" name="student" value="{{ $student->id }}">
                <div class="col-md-3">
                    <label class="form-label">Student</label>
                    <div class="form-control-plaintext">
                        <strong>{{ $student->student_name }}</strong>
                        <a href="{{ route('activity.index', array_diff_key($filters, ['student' => 1])) }}" class="ms-2 small">clear</a>
                    </div>
                </div>
            @endif
            <div class="col-md-2">
                <label for="user" class="form-label">User</label>
                <select name="user" id="user" class="form-select">
                    <option value="">Anyone</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ ($filters['user'] ?? null) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="action" class="form-label">Action</label>
                <select name="action" id="action" class="form-select">
                    <option value="">Any</option>
                    @foreach($actions as $code => $label)
                        <option value="{{ $code }}" {{ ($filters['action'] ?? null) === $code ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="from" class="form-label">From</label>
                <input type="date" name="from" id="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label for="to" class="form-label">To</label>
                <input type="date" name="to" id="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-history me-2"></i>Activity
        <span class="badge bg-primary ms-2">{{ $activities->total() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Student</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td class="text-nowrap"><small>{{ $activity->created_at->format('d/m/Y H:i') }}</small></td>
                            <td>
                                @if($activity->student)
                                    <a href="{{ route('students.show', $activity->student) }}">{{ $activity->student->student_name }}</a>
                                    @if($activity->student->trashed())<span class="badge bg-secondary ms-1">deleted</span>@endif
                                @else
                                    <span class="text-muted">#{{ $activity->student_id }}</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $activity->action_label }}</span></td>
                            <td>
                                {{ $activity->summary }}
                                @if($activity->changes)
                                    <details>
                                        <summary class="small text-muted" style="cursor: pointer;">{{ count($activity->changes) }} field(s)</summary>
                                        <ul class="small mb-0 mt-1" style="padding-left: 1.25rem;">
                                            @foreach($activity->changes as $field => $change)
                                                <li><strong>{{ \App\Models\Student::fieldLabel($field) }}:</strong> <span class="text-muted">{{ $change['from'] ?? '(blank)' }}</span> &rarr; {{ $change['to'] ?? '(blank)' }}</li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            </td>
                            <td><small>{{ $activity->user_name }}@if($activity->ip)<br><span class="text-muted">{{ $activity->ip }}</span>@endif</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted text-center py-4">No activity recorded for this selection.</td></tr>
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
