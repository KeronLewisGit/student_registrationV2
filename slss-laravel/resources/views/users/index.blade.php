@extends('layouts.app')

@section('title', 'User Management - SLSS')

@section('page-title', 'User Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Users</li>
@endsection

@push('styles')
<style>
    .role-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .role-admin {
        background: #fee2e2;
        color: #991b1b;
    }
    .role-staff {
        background: #dbeafe;
        color: #1e40af;
    }
    .role-viewer {
        background: #e0e7ff;
        color: #3730a3;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1rem;
    }
</style>
@endpush

@section('content')
<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="stat-value">{{ $users->count() }}</h3>
            <p class="stat-label">Total Users</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-user-shield"></i>
            </div>
            <h3 class="stat-value">{{ $users->where('role', 'admin')->count() }}</h3>
            <p class="stat-label">Administrators</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-user-tie"></i>
            </div>
            <h3 class="stat-value">{{ $users->where('role', 'staff')->count() }}</h3>
            <p class="stat-label">Staff Members</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-user"></i>
            </div>
            <h3 class="stat-value">{{ $users->where('role', 'viewer')->count() }}</h3>
            <p class="stat-label">Viewers</p>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users-cog me-2"></i>System Users</span>
        <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus-circle me-1"></i> Add User
        </a>
    </div>
    <div class="card-body">
        @if($users->isEmpty())
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>No users found.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="usersTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-secondary ms-1">You</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="role-badge role-{{ $user->role }}">
                                    @if($user->role === 'admin')
                                        <i class="fas fa-user-shield me-1"></i>
                                    @elseif($user->role === 'staff')
                                        <i class="fas fa-user-tie me-1"></i>
                                    @else
                                        <i class="fas fa-user me-1"></i>
                                    @endif
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $user->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete {{ $user->name }}? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete User">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary"
                                                disabled
                                                title="You cannot delete yourself">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Role Descriptions -->
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-info-circle me-2"></i>User Role Permissions
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <h6 class="role-badge role-admin mb-2">
                    <i class="fas fa-user-shield me-1"></i>Administrator
                </h6>
                <ul class="small">
                    <li>Full system access</li>
                    <li>Manage users</li>
                    <li>Edit & delete students</li>
                    <li>Import student data</li>
                    <li>Generate reports</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="role-badge role-staff mb-2">
                    <i class="fas fa-user-tie me-1"></i>Staff
                </h6>
                <ul class="small">
                    <li>View all students</li>
                    <li>Edit student records</li>
                    <li>Import student data</li>
                    <li>Generate reports</li>
                    <li>Cannot manage users</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="role-badge role-viewer mb-2">
                    <i class="fas fa-user me-1"></i>Viewer
                </h6>
                <ul class="small">
                    <li>View student records only</li>
                    <li>Generate reports</li>
                    <li>Cannot edit or delete</li>
                    <li>Cannot import data</li>
                    <li>Cannot manage users</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    if ($('#usersTable tbody tr').length > 0) {
        $('#usersTable').DataTable({
            pageLength: 10,
            order: [[3, 'desc']], // Sort by created date
            language: {
                search: "Search users:",
                lengthMenu: "Show _MENU_ users per page",
                info: "Showing _START_ to _END_ of _TOTAL_ users",
                infoEmpty: "No users to display",
                infoFiltered: "(filtered from _MAX_ total users)",
                zeroRecords: "No matching users found"
            },
            columnDefs: [
                { orderable: false, targets: [4] } // Disable sorting on actions
            ]
        });
    }
});
</script>
@endpush
