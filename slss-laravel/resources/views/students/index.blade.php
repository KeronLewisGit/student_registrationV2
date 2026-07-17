@extends('layouts.app')

@section('title', 'Student Management - SLSS')

@section('page-title', 'Student Management')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item active">Students</li>
@endsection

@push('styles')
<style>
    .student-photo-thumbnail {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 6px;
    }

    .table-actions {
        display: flex;
        gap: 0.25rem;
    }

    .badge-gender-male {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-gender-female {
        background: #fce7f3;
        color: #be185d;
    }

    .badge-class {
        background: var(--primary-light);
        color: var(--primary-color);
        font-weight: 600;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .student-photo-thumbnail {
            width: 32px;
            height: 32px;
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

        /* Improve DataTables controls spacing */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1rem;
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        /* Hide less important columns on small screens */
        #studentsTable th:nth-child(5),
        #studentsTable td:nth-child(5) {
            display: none;
        }

        .badge-status,
        .badge-class,
        .badge-gender-male,
        .badge-gender-female {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }

        /* Optimize table for mobile */
        #studentsTable {
            font-size: 0.85rem;
        }

        #studentsTable th,
        #studentsTable td {
            padding: 0.5rem 0.25rem;
        }

        /* Make student name column wrap */
        #studentsTable td:nth-child(2) {
            max-width: 120px;
            white-space: normal;
            word-wrap: break-word;
        }
    }

    /* Extra Small Mobile - Very aggressive optimization */
    @media (max-width: 480px) {
        /* Hide photo column on very small screens */
        #studentsTable th:nth-child(1),
        #studentsTable td:nth-child(1) {
            display: none;
        }

        /* Also hide gender column */
        #studentsTable th:nth-child(4),
        #studentsTable td:nth-child(4) {
            display: none;
        }

        /* Stack action buttons vertically for better touch targets */
        .table-actions {
            flex-direction: column;
            gap: 0.25rem;
            min-width: 40px;
        }

        .table-actions .btn-sm {
            width: 100%;
            min-height: 36px;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .table-actions .btn-sm i {
            font-size: 0.875rem;
        }

        /* Optimize student name column for extra space */
        #studentsTable td:nth-child(2) {
            max-width: 150px;
        }

        /* Reduce table padding further */
        #studentsTable th,
        #studentsTable td {
            padding: 0.4rem 0.2rem;
        }

        /* Smaller font for table */
        #studentsTable {
            font-size: 0.8rem;
        }

        /* Optimize DataTables pagination for small screens */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
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
            <h3 class="stat-value">{{ \App\Models\Student::count() }}</h3>
            <p class="stat-label">Total Students</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h3 class="stat-value">{{ \App\Models\Student::where('student_gender', 'Male')->count() }}</h3>
            <p class="stat-label">Male Students</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h3 class="stat-value">{{ \App\Models\Student::where('student_gender', 'Female')->count() }}</h3>
            <p class="stat-label">Female Students</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-calendar-plus"></i>
            </div>
            <h3 class="stat-value">{{ \App\Models\Student::whereYear('registration_date', date('Y'))->count() }}</h3>
            <p class="stat-label">This Year</p>
        </div>
    </div>
</div>

<!-- Filters & Actions Card -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-filter me-2"></i>Filter Students</span>
        @can('edit-students')
        <a href="{{ route('students.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus-circle me-1"></i><span class="d-none d-sm-inline"> Add Student</span><span class="d-inline d-sm-none">Add</span>
        </a>
        @endcan
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('students.index') }}" class="row g-3">
            <div class="col-md-3 col-sm-6">
                <label for="year" class="form-label">Registration Year</label>
                <select name="year" id="year" class="form-select">
                    <option value="">All Years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 col-sm-6">
                <label for="student_class" class="form-label">Form Class</label>
                <select name="student_class" id="student_class" class="form-select">
                    <option value="0">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class }}" {{ request('student_class') == $class ? 'selected' : '' }}>
                            Form {{ $class }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 col-sm-12">
                <label for="search" class="form-label">Search</label>
                <input type="text" name="search" id="search" class="form-control"
                       placeholder="Search name, SEA #, or cert..."
                       value="{{ request('search') }}">
            </div>

            <div class="col-md-2 col-sm-12 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> Filter
                </button>
            </div>
        </form>

        <div class="mt-3 d-flex gap-2 flex-wrap">
            <a href="{{ route('students.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-redo me-1"></i><span class="d-none d-sm-inline"> Reset Filters</span><span class="d-inline d-sm-none">Reset</span>
            </a>
            <button type="button" id="exportToPdfBtn" class="btn btn-info btn-sm" data-filters="{{ json_encode(request()->all()) }}">
                <i class="fas fa-file-pdf me-1"></i><span class="d-none d-sm-inline"> Export to PDF</span><span class="d-inline d-sm-none">PDF</span>
            </button>
        </div>
    </div>
</div>

<!-- Students Table -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-table me-2"></i>Student Records
        <span class="badge bg-primary ms-2">{{ $students->count() }} {{ $students->count() === 1 ? 'student' : 'students' }}</span>
    </div>
    <div class="card-body">
        @if($students->isEmpty())
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>No students found for the selected filter(s).
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="studentsTable">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Student Name</th>
                            <th>Form Class</th>
                            <th>Gender</th>
                            <th>Birth Date</th>
                            <th>Registration Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td>
                                @if($student->student_passport_photo)
                                    <img src="{{ asset($student->student_passport_photo) }}"
                                         alt="{{ $student->student_name }}"
                                         class="student-photo-thumbnail">
                                @else
                                    <img src="{{ asset('images/noimage.jpg') }}"
                                         alt="No photo"
                                         class="student-photo-thumbnail">
                                @endif
                            </td>
                            <td>
                                <strong>{{ ucwords(strtolower($student->student_name)) }}</strong>
                                @if($student->student_sea_number)
                                    <br><small class="text-muted">SEA: {{ $student->student_sea_number }}</small>
                                @endif
                            </td>
                            <td>
                                @if($student->form_1_class)
                                    <span class="badge badge-class">Form {{ $student->form_1_class }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($student->student_gender === 'Male')
                                    <span class="badge badge-gender-male">
                                        <i class="fas fa-mars me-1"></i>Male
                                    </span>
                                @elseif($student->student_gender === 'Female')
                                    <span class="badge badge-gender-female">
                                        <i class="fas fa-venus me-1"></i>Female
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td data-order="{{ $student->student_dob ? $student->student_dob->format('Y-m-d') : '0000-00-00' }}">
                                <small>{{ $student->formatted_dob }}</small>
                            </td>
                            <td data-order="{{ $student->registration_date ? $student->registration_date->format('Y-m-d') : '0000-00-00' }}">
                                <small>{{ $student->formatted_registration_date }}</small>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('students.show', $student) }}"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="View Profile">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('students.pdf', $student) }}"
                                       class="btn btn-sm btn-outline-success"
                                       title="Download PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    @can('edit-students')
                                    <a href="{{ route('students.edit', $student) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('delete-students')
                                    <form action="{{ route('students.destroy', $student) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this student?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
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

<!-- PDF Export Progress Modal -->
<div class="modal fade" id="pdfExportModal" tabindex="-1" aria-labelledby="pdfExportModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="pdfExportModalLabel">
                    <i class="fas fa-file-pdf me-2"></i>Exporting Student Profiles
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="spinner-border text-info" role="status" id="exportSpinner">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <i class="fas fa-check-circle text-success d-none" id="exportSuccess" style="font-size: 3rem;"></i>
                    <i class="fas fa-times-circle text-danger d-none" id="exportError" style="font-size: 3rem;"></i>
                </div>

                <p class="text-center mb-3" id="exportMessage">Initializing export...</p>

                <div class="progress" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                         role="progressbar"
                         id="exportProgressBar"
                         style="width: 0%"
                         aria-valuenow="0"
                         aria-valuemin="0"
                         aria-valuemax="100">0%</div>
                </div>

                <p class="text-center mt-2 small text-muted" id="exportDetails">
                    Preparing to export <span id="exportTotal">0</span> students...
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="exportCloseBtn" data-bs-dismiss="modal" disabled>
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <a href="#" class="btn btn-success d-none" id="exportDownloadBtn" download>
                    <i class="fas fa-download me-1"></i>Download ZIP
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    if ($('#studentsTable tbody tr').length > 0) {
        // Adjust page length based on screen size
        var pageLength = $(window).width() < 768 ? 10 : 25;

        $('#studentsTable').DataTable({
            pageLength: pageLength,
            order: [[5, 'desc']], // Sort by registration date (newest first)
            language: {
                search: "Search students:",
                lengthMenu: "Show _MENU_ students per page",
                info: "Showing _START_ to _END_ of _TOTAL_ students",
                infoEmpty: "No students to display",
                infoFiltered: "(filtered from _MAX_ total students)",
                zeroRecords: "No matching students found"
            },
            columnDefs: [
                { orderable: false, targets: [0, 6] } // Disable sorting on photo and actions
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
            var table = $('#studentsTable').DataTable();
            var newPageLength = $(window).width() < 768 ? 10 : 25;
            if (table.page.len() !== newPageLength) {
                table.page.len(newPageLength).draw();
            }
        });
    }

    // PDF Export with Progress Bar
    let progressInterval = null;

    $('#exportToPdfBtn').on('click', function() {
        const filters = $(this).data('filters');
        const progressId = 'pdf_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('pdfExportModal'));
        modal.show();

        // Reset UI
        $('#exportSpinner').removeClass('d-none');
        $('#exportSuccess').addClass('d-none');
        $('#exportError').addClass('d-none');
        $('#exportProgressBar').css('width', '0%').text('0%').attr('aria-valuenow', 0);
        $('#exportMessage').text('Initializing export...');
        $('#exportDetails').html('Preparing to export <span id="exportTotal">0</span> students...');
        $('#exportCloseBtn').prop('disabled', true);
        $('#exportDownloadBtn').addClass('d-none');

        // Build URL with filters and progress ID
        const url = '{{ route("students.bulk-pdf") }}?' + $.param({...filters, progress_id: progressId});

        // Start the export in an iframe (so we can download the file)
        const iframe = $('<iframe>', {
            src: url,
            style: 'display:none;'
        }).appendTo('body');

        // Start polling progress
        progressInterval = setInterval(function() {
            $.ajax({
                url: '{{ route("students.bulk-pdf.progress") }}',
                method: 'GET',
                data: { progress_id: progressId },
                success: function(data) {
                    if (data.status === 'processing') {
                        // Update progress bar
                        const progress = Math.round(data.progress);
                        $('#exportProgressBar')
                            .css('width', progress + '%')
                            .text(progress + '%')
                            .attr('aria-valuenow', progress);

                        // Update message
                        $('#exportMessage').text(data.message);
                        $('#exportDetails').html(`Processing ${data.current} of ${data.total} students...`);
                        $('#exportTotal').text(data.total);

                    } else if (data.status === 'completed') {
                        // Export completed
                        clearInterval(progressInterval);

                        $('#exportProgressBar')
                            .css('width', '100%')
                            .text('100%')
                            .attr('aria-valuenow', 100)
                            .removeClass('progress-bar-animated');

                        $('#exportSpinner').addClass('d-none');
                        $('#exportSuccess').removeClass('d-none');
                        $('#exportMessage').html('<strong>Export completed successfully!</strong>');
                        $('#exportDetails').html(`<span class="text-success">All ${data.total} student profiles have been exported.</span>`);
                        $('#exportCloseBtn').prop('disabled', false);

                        // Show success message
                        setTimeout(function() {
                            modal.hide();
                            alert('PDF export completed! Your download should start automatically.');
                        }, 2000);

                    } else if (data.status === 'failed') {
                        // Export failed
                        clearInterval(progressInterval);
                        iframe.remove();

                        $('#exportProgressBar')
                            .removeClass('bg-info progress-bar-animated')
                            .addClass('bg-danger');

                        $('#exportSpinner').addClass('d-none');
                        $('#exportError').removeClass('d-none');
                        $('#exportMessage').html('<strong>Export failed!</strong>');
                        $('#exportDetails').html(`<span class="text-danger">${data.message}</span>`);
                        $('#exportCloseBtn').prop('disabled', false);

                    } else if (data.status === 'not_found') {
                        // Progress not found (might have been deleted after completion)
                        clearInterval(progressInterval);
                    }
                },
                error: function() {
                    clearInterval(progressInterval);
                    iframe.remove();

                    $('#exportProgressBar')
                        .removeClass('bg-info progress-bar-animated')
                        .addClass('bg-danger');

                    $('#exportSpinner').addClass('d-none');
                    $('#exportError').removeClass('d-none');
                    $('#exportMessage').html('<strong>Connection error!</strong>');
                    $('#exportDetails').html('<span class="text-danger">Failed to check export progress.</span>');
                    $('#exportCloseBtn').prop('disabled', false);
                }
            });
        }, 1000); // Poll every second

        // Cleanup on modal close
        $('#pdfExportModal').on('hidden.bs.modal', function() {
            clearInterval(progressInterval);
            iframe.remove();
        });
    });
});
</script>
@endpush
