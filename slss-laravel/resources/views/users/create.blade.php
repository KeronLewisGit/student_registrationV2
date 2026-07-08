@extends('layouts.app')

@section('title', 'Add New User - SLSS')

@section('page-title', 'Add New User')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@push('styles')
<style>
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
    }
    .password-strength {
        margin-top: 0.5rem;
        font-size: 0.875rem;
    }
    .password-requirements {
        background: var(--bg-light);
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }
    .password-requirements ul {
        margin: 0;
        padding-left: 1.5rem;
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Create New User Account</h2>
            <p class="text-muted mb-0">Add a new user to the system</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name *</label>
                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       required
                       autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Email Address *</label>
                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label">User Role *</label>
                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                    <option value="">Select Role</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                        <i class="fas fa-user-shield"></i> Administrator - Full Access
                    </option>
                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>
                        <i class="fas fa-user-tie"></i> Staff - Edit Students & Import
                    </option>
                    <option value="viewer" {{ old('role') == 'viewer' ? 'selected' : '' }}>
                        <i class="fas fa-user"></i> Viewer - View Only
                    </option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Password *</label>
                <input type="password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       required
                       id="password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Confirm Password *</label>
                <input type="password"
                       name="password_confirmation"
                       class="form-control"
                       required>
            </div>

            <div class="col-md-12">
                <div class="password-requirements">
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
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-user-plus me-1"></i> Create User
            </button>
        </div>
    </form>
</div>
@endsection
