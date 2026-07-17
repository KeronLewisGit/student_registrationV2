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
    .table-actions {
        display: flex;
        gap: 0.25rem;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .user-avatar {
            width: 32px;
            height: 32px;
            font-size: 0.875rem;
        }

        .table-actions {
            gap: 0.125rem;
        }

        .table-actions .btn-sm {
            padding: 0.375rem 0.5rem;
        }

        .table-actions .btn-sm i {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        /* Hide Created column on small screens */
        #usersTable th:nth-child(4),
        #usersTable td:nth-child(4) {
            display: none;
        }

        .role-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }

        /* Optimize table for mobile */
        #usersTable {
            font-size: 0.85rem;
        }

        #usersTable th,
        #usersTable td {
            padding: 0.5rem 0.25rem;
        }
    }

    @media (max-width: 480px) {
        /* Stack action buttons vertically for better touch targets */
        .table-actions {
            flex-direction: column;
            gap: 0.25rem;
            min-width: 44px;
        }

        .table-actions .btn-sm {
            width: 100%;
            min-height: 40px;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table-actions .btn-sm i {
            font-size: 0.875rem;
        }

        /* Reduce table padding further */
        #usersTable th,
        #usersTable td {
            padding: 0.4rem 0.2rem;
        }

        /* Smaller font for table */
        #usersTable {
            font-size: 0.8rem;
        }
    }

    /* Modal responsive */
    @media (max-width: 576px) {
        .modal-dialog {
            margin: 0.5rem;
        }

        .modal-body .form-control {
            min-height: 44px;
            font-size: 16px; /* Prevents iOS zoom */
        }

        .modal-footer .btn {
            min-height: 44px;
        }
    }
</style>
@endpush

@section('content')
<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="stat-value">{{ $users->count() }}</h3>
            <p class="stat-label">Total Users</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-user-shield"></i>
            </div>
            <h3 class="stat-value">{{ $users->where('role', 'admin')->count() }}</h3>
            <p class="stat-label">Administrators</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-user-tie"></i>
            </div>
            <h3 class="stat-value">{{ $users->where('role', 'staff')->count() }}</h3>
            <p class="stat-label">Staff Members</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
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
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-users-cog me-2"></i>System Users</span>
        <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus-circle me-1"></i><span class="d-none d-sm-inline"> Add User</span><span class="d-inline d-sm-none">Add</span>
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
                                <div class="table-actions">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#resetPasswordModal{{ $user->id }}"
                                            title="Reset Password">
                                        <i class="fas fa-key"></i>
                                    </button>
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

            <!-- Password Reset Modals -->
            @foreach($users as $user)
            <div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1" aria-labelledby="resetPasswordModalLabel{{ $user->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-white">
                            <h5 class="modal-title" id="resetPasswordModalLabel{{ $user->id }}">
                                <i class="fas fa-key me-2"></i>Reset Password for {{ $user->name }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('users.reset-password', $user) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Administrator Password Reset</strong><br>
                                    You are about to reset the password for <strong>{{ $user->name }}</strong> ({{ $user->email }}).
                                </div>

                                <div class="mb-3">
                                    <label for="new_password{{ $user->id }}" class="form-label">New Password *</label>
                                    <input type="password"
                                           class="form-control"
                                           id="new_password{{ $user->id }}"
                                           name="new_password"
                                           required
                                           minlength="8"
                                           placeholder="Enter new password (minimum 8 characters)">
                                </div>

                                <div class="mb-3">
                                    <label for="new_password_confirmation{{ $user->id }}" class="form-label">Confirm New Password *</label>
                                    <input type="password"
                                           class="form-control"
                                           id="new_password_confirmation{{ $user->id }}"
                                           name="new_password_confirmation"
                                           required
                                           minlength="8"
                                           placeholder="Confirm new password">
                                </div>

                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> Make sure to securely communicate this new password to the user.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key me-1"></i>Reset Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
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
        // Adjust page length based on screen size
        var pageLength = $(window).width() < 768 ? 10 : 10;

        $('#usersTable').DataTable({
            pageLength: pageLength,
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
            ],
            // Optimize for mobile
            dom: $(window).width() < 768 ?
                '<"row"<"col-12"f>><"row"<"col-12"tr>><"row"<"col-12"i><"col-12"p>>' :
                'lfrtip',
            // Adjust display on window resize
            drawCallback: function() {
                // Ensure action buttons remain properly styled after DataTables redraw
                $('.table-actions').css('display', 'flex');
            }
        });

        // Handle responsive page length on window resize
        $(window).on('resize', function() {
            var table = $('#usersTable').DataTable();
            var newPageLength = $(window).width() < 768 ? 10 : 10;
            if (table.page.len() !== newPageLength) {
                table.page.len(newPageLength).draw();
            }
        });
    }
});
</script>
@endpush
