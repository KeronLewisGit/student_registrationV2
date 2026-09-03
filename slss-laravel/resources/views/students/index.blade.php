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
            gap: 0.25rem;
        }

        /* Keep icon buttons at a comfortable touch size */
        .table-actions .btn-sm {
            padding: 0.375rem 0.5rem;
            min-width: 44px;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .table-actions .btn-sm i {
            font-size: 0.875rem;
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
        .badge-status,
        .badge-class,
        .badge-gender-male,
        .badge-gender-female {
            font-size: 0.75rem; /* 12px legibility floor */
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
        /* Stack action buttons vertically for better touch targets */
        .table-actions {
            flex-direction: column;
            gap: 0.25rem;
            min-width: 40px;
        }

        .table-actions .btn-sm {
            width: 100%;
            min-height: 44px;
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
            <div class="stat-value" style="font-size: 1.75rem; font-weight: 700;">{{ $stats['total'] }}</div>
            <p class="stat-label">Total Students</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-value" style="font-size: 1.75rem; font-weight: 700;">{{ $stats['male'] }}</div>
            <p class="stat-label">Male Students</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="stat-value" style="font-size: 1.75rem; font-weight: 700;">{{ $stats['female'] }}</div>
            <p class="stat-label">Female Students</p>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-calendar-plus"></i>
            </div>
            <div class="stat-value" style="font-size: 1.75rem; font-weight: 700;">{{ $stats['registered_this_year'] }}</div>
            <p class="stat-label">Registered in {{ date('Y') }}</p>
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
                    @php
                        // The stored value may be "A" or "1A"; normalize both sides so the
                        // dropdown highlights the right option whichever format came back.
                        $selectedClass = \App\Models\Student::classVariants(request('student_class'));
                    @endphp
                    @foreach($classes as $class)
                        <option value="{{ $class }}" {{ in_array($class, $selectedClass, true) ? 'selected' : '' }}>
                            {{ $class }}
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
                            <th scope="col">Photo</th>
                            <th scope="col">Student Name</th>
                            <th scope="col">Form Class</th>
                            <th scope="col">Gender</th>
                            <th scope="col">Birth Date</th>
                            <th scope="col">Registration Date</th>
                            <th scope="col">Actions</th>
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
                                    <span class="badge badge-class">{{ $student->form_1_class }}</span>
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
                                       title="View Profile"
                                       aria-label="View profile of {{ $student->student_name }}">
                                        <i class="fas fa-eye" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('students.pdf', $student) }}"
                                       class="btn btn-sm btn-outline-success"
                                       title="Download PDF"
                                       aria-label="Download PDF for {{ $student->student_name }}">
                                        <i class="fas fa-file-pdf" aria-hidden="true"></i>
                                    </a>
                                    @can('edit-students')
                                    <a href="{{ route('students.edit', $student) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Edit"
                                       aria-label="Edit {{ $student->student_name }}">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>
                                    @endcan
                                    @can('delete-students')
                                    <form action="{{ route('students.destroy', $student) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm({{ Illuminate\Support\Js::from('Delete ' . $student->student_name . '? The record can be restored by an administrator if needed.') }});">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete"
                                                aria-label="Delete {{ $student->student_name }}">
                                            <i class="fas fa-trash" aria-hidden="true"></i>
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

<!-- PDF Export Modal -->
<div class="modal fade" id="pdfExportModal" tabindex="-1" aria-labelledby="pdfExportModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: var(--primary-color);">
                <h5 class="modal-title" id="pdfExportModalLabel">
                    <i class="fas fa-file-pdf me-2" aria-hidden="true"></i>Exporting Student Profiles
                </h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="spinner-border text-info" role="status" id="exportSpinner" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <i class="fas fa-check-circle text-success d-none" id="exportSuccess" style="font-size: 3rem;"></i>
                    <i class="fas fa-times-circle text-danger d-none" id="exportError" style="font-size: 3rem;"></i>
                </div>

                <p class="text-center mb-3 fw-bold" id="exportMessage" aria-live="polite">Initializing export...</p>

                <div class="progress" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-info"
                         role="progressbar"
                         id="exportProgressBar"
                         style="width: 0%"
                         aria-valuenow="0"
                         aria-valuemin="0"
                         aria-valuemax="100"></div>
                </div>

                <p class="text-center mt-3 small text-muted" id="exportDetails" aria-live="polite">
                    This may take a few moments...
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="exportCloseBtn" data-bs-dismiss="modal" disabled>
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <a href="#" class="btn btn-success d-none" id="exportDownloadBtn" target="_blank">
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
            responsive: true, // collapse overflow columns into a tap-to-expand row
            language: {
                search: "Search students:",
                lengthMenu: "Show _MENU_ students per page",
                info: "Showing _START_ to _END_ of _TOTAL_ students",
                infoEmpty: "No students to display",
                infoFiltered: "(filtered from _MAX_ total students)",
                zeroRecords: "No matching students found"
            },
            columnDefs: [
                { orderable: false, targets: [0, 6] }, // Disable sorting on photo and actions
                { responsivePriority: 1, targets: 1 }, // Always keep name...
                { responsivePriority: 2, targets: 6 }, // ...and actions visible
                { responsivePriority: 10001, targets: 0 } // Photo collapses first
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

    // PDF Export with Real-Time Progress Tracking
    $('#exportToPdfBtn').on('click', function() {
        const filters = $(this).data('filters');

        // Generate unique progress ID
        const progressId = 'export_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('pdfExportModal'));
        modal.show();

        // Reset UI
        $('#exportSpinner').removeClass('d-none');
        $('#exportSuccess').addClass('d-none');
        $('#exportError').addClass('d-none');
        $('#exportProgressBar').css('width', '0%').removeClass('bg-danger bg-success').addClass('bg-info progress-bar-animated');
        $('#exportMessage').text('Initializing export...');
        $('#exportDetails').html('Starting PDF generation...');
        $('#exportCloseBtn').prop('disabled', true);
        $('#exportDownloadBtn').addClass('d-none').attr('href', '#');

        // Track last known progress to enforce monotonic increase
        let lastProgress = 0;
        let progressInterval = null;
        let missingPolls = 0; // consecutive polls with no progress record

        // Show a terminal failure state and always give the user a way out.
        // Messages are inserted with .text() — server/error strings may contain
        // user-submitted data and must never be interpreted as HTML.
        function showExportFailure(message, details) {
            if (progressInterval) clearInterval(progressInterval);
            $('#exportProgressBar').removeClass('bg-info progress-bar-animated').addClass('bg-danger').css('width', '100%');
            $('#exportSpinner').addClass('d-none');
            $('#exportError').removeClass('d-none');
            $('#exportMessage').text(message);
            $('#exportDetails').empty().append($('<span class="text-danger"></span>').text(details || ''));
            $('#exportCloseBtn').prop('disabled', false);
        }

        // Start the export process (non-blocking request)
        $.ajax({
            url: '{{ route("students.bulk-pdf") }}',
            method: 'GET',
            data: {...filters, progress_id: progressId},
            dataType: 'json',
            timeout: 300000, // 5 minutes timeout
            error: function(xhr, status, error) {
                if (status === 'timeout') {
                    showExportFailure('Export timed out!',
                        'The export process took too long. Please try with fewer students or contact support.');
                } else if (xhr.status === 401 || xhr.status === 419 || (status === 'parsererror' && xhr.status === 200)) {
                    // Session expired: the request was redirected to the login page
                    showExportFailure('Session expired',
                        'Your session has expired. Please reload the page and log in again.');
                } else if (xhr.status === 403) {
                    showExportFailure('Not authorized',
                        'Your account does not have permission to bulk-export student profiles.');
                } else {
                    let details = 'The export request failed.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        details = xhr.responseJSON.message;
                    }
                    showExportFailure('Export failed!', details);
                }
            }
        });

        // Wait 1 second before starting to poll (give server time to initialize)
        setTimeout(function() {
            // Poll for progress updates every 1.5 seconds
            progressInterval = setInterval(function() {
                $.ajax({
                    url: '{{ route("students.bulk-pdf-progress") }}',
                    method: 'GET',
                    data: { progress_id: progressId },
                    dataType: 'json',
                    success: function(progress) {
                        // No progress record: either the export hasn't initialized yet
                        // or the export request died before writing one. Allow a grace
                        // period, then stop so the modal can't spin forever.
                        if (progress.status === 'not_found' || progress.status === 'error') {
                            missingPolls++;
                            if (missingPolls >= 10) { // ~15 seconds with no record
                                showExportFailure('Export failed to start',
                                    'No progress information was received from the server. Please close this dialog and try again.');
                            }
                            return;
                        }
                        missingPolls = 0;

                        // Enforce monotonic increase - progress should never decrease
                        let currentProgress = Math.max(lastProgress, progress.progress || 0);
                        lastProgress = currentProgress;

                        // Update progress bar (only if increased)
                        $('#exportProgressBar').css('width', currentProgress + '%').attr('aria-valuenow', currentProgress);

                        // Update message
                        $('#exportMessage').text(progress.message || 'Processing...');

                        // Update details with current/total if available
                        if (progress.current && progress.total) {
                            $('#exportDetails').text(`Processing ${progress.current} of ${progress.total} students...`);
                        } else {
                            $('#exportDetails').text(progress.step || 'Working...');
                        }

                        // Handle completion
                        if (progress.status === 'completed') {
                            clearInterval(progressInterval);

                            $('#exportProgressBar')
                                .removeClass('progress-bar-animated')
                                .addClass('bg-success')
                                .css('width', '100%');

                            $('#exportSpinner').addClass('d-none');
                            $('#exportSuccess').removeClass('d-none');
                            $('#exportMessage').text('Export completed successfully!');
                            $('#exportDetails').empty().append($('<span class="text-success"></span>').text(progress.message || ''));
                            $('#exportCloseBtn').prop('disabled', false);

                            // Show download button
                            if (progress.download_url) {
                                $('#exportDownloadBtn')
                                    .removeClass('d-none')
                                    .attr('href', progress.download_url)
                                    .attr('download', progress.filename);

                                // Trigger automatic download
                                window.location.href = progress.download_url;
                            }
                        }

                        // Handle failure
                        if (progress.status === 'failed') {
                            let details = progress.message || 'Export failed.';
                            if (progress.error_details) {
                                details += ' — ' + progress.error_details;
                            }
                            showExportFailure('Export failed!', details);
                        }
                    },
                    error: function(xhr, status) {
                        // A single failed poll may be transient, but repeated
                        // failures (expired session, server down) must not leave
                        // the modal spinning forever.
                        missingPolls++;
                        if (missingPolls >= 10) {
                            showExportFailure('Lost contact with the server',
                                'Progress updates stopped. The export may still finish in the background — please reload the page and check again.');
                        }
                    }
                });
            }, 1500); // Poll every 1.5 seconds
        }, 1000); // Wait 1 second before starting to poll
    });
});
</script>
@endpush
