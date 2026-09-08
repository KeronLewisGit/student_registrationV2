@extends('layouts.app')

@section('title', 'Year-End Promotion - SLSS')
@section('page-title', 'Year-End Promotion')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Year-End Promotion</li>
@endsection

@section('content')
<div class="form-card">
    <h2 class="h4 mb-1">Promote students for {{ $academicYear }}</h2>
    <p class="text-muted">
        Moves every active student up one form, keeping their stream letter (3C becomes 4C).
        Students in Form {{ \App\Models\Student::MAX_FORM }} are marked as graduated.
        Students who have left, or who have no current class, are not touched.
        Every change is written to each student's history.
    </p>

    @if($newIntake)
        <div class="alert alert-info">
            <i class="fas fa-user-plus me-1"></i>
            <strong>{{ $newIntake }}</strong> students with intake year {{ $targetYear }} are the incoming Form 1 group. They stay in Form 1 and are not included below.
        </div>
    @endif

    @if($alreadyRan && !$lastRun)
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-1"></i>
            <strong>Classes already match {{ $academicYear }}.</strong>
            {{ $alignment['aligned'] }} students are in the form their intake year implies for this year, so there is nothing to promote yet.
            Come back at the end of the academic year.
        </div>
    @endif

    @if($lastRun)
        <div class="alert {{ $alreadyRan ? 'alert-danger' : 'alert-secondary' }}">
            <i class="fas fa-info-circle me-1"></i>
            Last promotion ran on <strong>{{ $lastRun->created_at->format('d/m/Y H:i') }}</strong> by {{ $lastRun->user_name }}: {{ $lastRun->summary }}.
            @if($alreadyRan)
                <strong>The promotion into {{ $academicYear }} has already been run</strong>, so the button below is disabled.
            @endif
        </div>
    @endif

    <div class="table-responsive mb-4">
        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Current class</th>
                    <th>Students</th>
                    <th>Becomes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($preview as $row)
                    <tr>
                        <td><span class="badge bg-primary">{{ $row['from'] }}</span></td>
                        <td>{{ $row['count'] }}</td>
                        <td>
                            @if($row['to'] === 'Graduated')
                                <span class="badge bg-success">Graduated</span>
                            @elseif($row['to'] === 'Unchanged')
                                <span class="text-muted">Unchanged</span>
                            @else
                                <span class="badge bg-primary">{{ $row['to'] }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted">No active students with a current class.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('students.promotion.run') }}"
          onsubmit="return confirm('This moves every active student up one form and marks Form {{ \App\Models\Student::MAX_FORM }} as graduated. It cannot be undone automatically. Continue?');">
        @csrf
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="confirm" id="confirm" value="1" required>
            <label class="form-check-label" for="confirm">
                I have checked the table above and want to promote all active students now.
            </label>
            @error('confirm')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary" {{ empty($preview) || $alreadyRan ? 'disabled' : '' }}>
            <i class="fas fa-level-up-alt me-1"></i> Run promotion
        </button>
        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
    </form>
</div>
@endsection
