@extends('layouts.app')

@section('title', 'Edit User - SLSS')

@section('page-title', 'Edit User')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Edit - {{ $user->name }}</li>
@endsection

@push('styles')
<style>
    /* Page-specific: password note (shared form styles live in css/slss.css) */
    .password-note {
        background: var(--bg-light);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
        font-size: 0.875rem;
        color: var(--text-muted);
    }

    @media (max-width: 768px) {
        .password-note {
            padding: 0.875rem;
            font-size: 0.9rem;
        }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Edit User Account</h2>
            <p class="text-muted mb-0">User ID: {{ $user->id }} | {{ $user->email }}</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>

    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="field_name">Full Name *</label>
                <input type="text"
                       id="field_name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}"
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="field_email">Email Address *</label>
                <input type="email"
                       id="field_email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}"
                       required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label" for="field_role">User Role *</label>
                <select id="field_role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">Select Role</option>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                        Administrator - Full Access
                    </option>
                    <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }}>
                        Staff - Edit Students & Import
                    </option>
                    <option value="viewer" {{ old('role', $user->role) == 'viewer' ? 'selected' : '' }}>
                        Viewer - View Only
                    </option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if($user->role === 'admin' && \App\Models\User::where('role', 'admin')->count() === 1)
                    <small class="text-warning-emphasis d-block mt-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        This is the only administrator account. Changing the role will leave the system without an admin.
                    </small>
                @endif
            </div>

            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Change Password (Optional)</strong><br>
                    Leave password fields empty to keep the current password. Only fill them in if you want to change the password.
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="field_password">New Password (Optional)</label>
                <input type="password"
                       id="field_password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       id="password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label" for="field_password_confirmation">Confirm New Password</label>
                <input type="password"
                       id="field_password_confirmation" name="password_confirmation"
                       class="form-control">
            </div>

            <div class="col-md-12">
                <div class="password-note">
                    <strong class="text-dark">
                        <i class="fas fa-shield-alt me-1"></i>Password Requirements:
                    </strong>
                    <ul class="mt-2 mb-0">
                        <li>Minimum 8 characters long</li>
                        <li>Mix of uppercase and lowercase letters recommended</li>
                        <li>Include numbers and special characters for stronger security</li>
                    </ul>
                </div>
            </div>

            @if($user->id === auth()->id())
            <div class="col-md-12">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> You are editing your own account. Be careful when changing your email or password.
                </div>
            </div>
            @endif
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
