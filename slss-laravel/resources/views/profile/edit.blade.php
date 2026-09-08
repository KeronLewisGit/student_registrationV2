@extends('layouts.app')

@section('title', 'My Profile - SLSS')
@section('page-title', 'My Profile')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item active">My Profile</li>
@endsection

@push('styles')
<style>
    .profile-avatar {
        width: 72px; height: 72px; border-radius: 50%;
        background: var(--primary-color); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.6rem; font-weight: 700;
    }
    .signin-list li { display: flex; justify-content: space-between; gap: 1rem; padding: 0.4rem 0; border-bottom: 1px solid var(--border-color); font-size: 0.875rem; }
    .signin-list li:last-child { border-bottom: 0; }
</style>
@endpush

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="form-card mb-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="profile-avatar">{{ $user->initials }}</div>
                <div>
                    <h2 class="h4 mb-1">{{ $user->name }}</h2>
                    <p class="text-muted mb-0">{{ ucfirst($user->role) }}@if($user->job_title) &middot; {{ $user->job_title }}@endif &middot; member since {{ $user->created_at?->format('M Y') }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" data-warn-unsaved>
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First name <span class="text-danger">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $user->first_name) }}" required maxlength="80" autocomplete="given-name">
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last name <span class="text-danger">*</span></label>
                        <input type="text" id="last_name" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $user->last_name) }}" required maxlength="80" autocomplete="family-name">
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="job_title" class="form-label">Job title</label>
                        <input type="text" id="job_title" name="job_title" class="form-control @error('job_title') is-invalid @enderror" value="{{ old('job_title', $user->job_title) }}" maxlength="100" placeholder="e.g. Form 3 Teacher, Registrar">
                        @error('job_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" maxlength="40" autocomplete="tel">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email address <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="email">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">You sign in with this address.</small>
                    </div>
                    <div class="col-md-6">
                        <label for="current_password" class="form-label">Current password <small class="text-muted fw-normal">(only to change your email)</small></label>
                        <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" disabled>
                        <small class="text-muted">Roles are set by an administrator.</small>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save changes</button>
                </div>
            </form>
        </div>

        <div class="form-card">
            <h2 class="h5 mb-1">Change password</h2>
            <p class="text-muted small mb-3">
                At least 12 characters with upper and lower case letters and a number.
                @if($lastChange) Last changed {{ $lastChange->created_at->diffForHumans() }}. @endif
            </p>
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="pw_current" class="form-label">Current password</label>
                        <input type="password" id="pw_current" name="current_password" class="form-control @error('current_password', 'password') is-invalid @enderror" required autocomplete="current-password">
                        @error('current_password', 'password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="pw_new" class="form-label">New password</label>
                        <input type="password" id="pw_new" name="password" class="form-control @error('password', 'password') is-invalid @enderror" required autocomplete="new-password">
                        @error('password', 'password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="pw_confirm" class="form-label">Confirm new password</label>
                        <input type="password" id="pw_confirm" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-key me-1"></i> Change password</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="form-card mb-4">
            <h2 class="h5 mb-3">Recent sign-in activity</h2>
            @if($signIns->isEmpty())
                <p class="text-muted mb-0">No sign-ins recorded yet.</p>
            @else
                <ul class="list-unstyled signin-list mb-0">
                    @foreach($signIns as $entry)
                        <li>
                            <span class="{{ $entry->action === 'login-failed' ? 'text-danger' : '' }}">{{ $entry->action_label }}</span>
                            <span class="text-muted text-nowrap">{{ $entry->created_at->format('d/m/Y H:i') }} &middot; {{ $entry->ip ?? '—' }}</span>
                        </li>
                    @endforeach
                </ul>
                <p class="text-muted small mt-2 mb-0">If you see a sign-in you don't recognise, change your password and sign out of other devices.</p>
            @endif
        </div>

        <div class="form-card">
            <h2 class="h5 mb-1">Sign out of other devices</h2>
            <p class="text-muted small mb-3">Ends every other session signed in to your account. This browser stays signed in.</p>
            <form method="POST" action="{{ route('profile.sign-out-others') }}">
                @csrf
                <div class="input-group">
                    <input type="password" name="password_confirm" class="form-control @error('password_confirm', 'sessions') is-invalid @enderror" placeholder="Your current password" required autocomplete="current-password" aria-label="Current password">
                    <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-sign-out-alt me-1"></i> Sign out others</button>
                    @error('password_confirm', 'sessions')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
