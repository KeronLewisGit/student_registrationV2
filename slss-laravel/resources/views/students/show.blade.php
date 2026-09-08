@extends('layouts.app')

@section('title', 'Student Profile - ' . $student->student_name)

@section('page-title', 'Student Profile')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
    <li class="breadcrumb-item active">{{ $student->student_name }}</li>
@endsection

@push('styles')
<style>
    .profile-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #7c3aed 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .profile-photo-large {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 16px;
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .info-card {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .info-card-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--primary-color);
    }

    .info-card-header i {
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-right: 0.75rem;
        width: 32px;
        text-align: center;
    }

    .info-card-header h5 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-normal);
    }

    .info-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1rem;
    }

    .info-item {
        padding: 0.75rem 0;
    }

    .info-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: var(--text-normal);
        word-wrap: break-word;
    }

    .info-value.empty {
        color: var(--text-muted);
        font-style: italic;
    }

    .badge-status {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .badge-male {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-female {
        background: #fce7f3;
        color: #be185d;
    }

    .badge-other {
        background: #ede9fe;
        color: #5b21b6;
    }

    .action-buttons {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.625rem 1.25rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .section-divider {
        border-top: 2px solid #e5e7eb;
        margin: 1.5rem 0;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .profile-header {
            padding: 1.5rem;
        }

        .profile-photo-large {
            width: 100px;
            height: 100px;
        }

        .action-buttons {
            margin-top: 1rem;
        }

        .info-card {
            padding: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .info-card-header h5 {
            font-size: 1rem;
        }

        .info-card-header i {
            font-size: 1.25rem;
        }

        .info-row {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }

        .info-label {
            font-size: 0.7rem;
        }

        .info-value {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .profile-header {
            padding: 1rem;
        }

        .profile-header .row {
            flex-direction: column;
            text-align: center;
        }

        .profile-header .col-auto {
            margin: 0 auto 1rem;
        }

        .profile-header h2 {
            font-size: 1.5rem !important;
        }

        .badge-status {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .info-card {
            padding: 1rem;
        }

        .info-card-header {
            margin-bottom: 1rem;
        }

        .info-card-header h5 {
            font-size: 0.95rem;
        }

        .info-row {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .info-item {
            padding: 0.5rem 0;
        }

        .btn-action {
            font-size: 0.85rem;
            padding: 0.625rem 1rem;
            min-height: 44px;
        }

        /* Ensure action buttons wrap nicely */
        .action-buttons {
            gap: 0.5rem;
        }
    }

    /* Extra Small Mobile - Additional optimizations */
    @media (max-width: 480px) {
        .profile-header {
            padding: 0.875rem;
        }

        .profile-photo-large {
            width: 80px;
            height: 80px;
        }

        .profile-header h2 {
            font-size: 1.25rem !important;
        }

        .badge-status {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }

        .info-card {
            padding: 0.875rem;
            margin-bottom: 1rem;
        }

        .info-card-header i {
            font-size: 1.125rem;
        }

        .info-card-header h5 {
            font-size: 0.9rem;
        }

        .info-label {
            font-size: 0.75rem; /* 12px floor for legibility */
        }

        .info-value {
            font-size: 0.875rem;
        }

        .btn-action {
            font-size: 0.8rem;
            padding: 0.5rem 0.875rem;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .action-buttons {
            flex-direction: column;
            gap: 0.5rem;
        }

        .action-buttons .btn-action {
            width: 100%;
        }
    }

    @media print {
        .info-card {
            break-inside: avoid;
            box-shadow: none;
        }
    }
</style>
@endpush

@section('content')
<!-- Profile Header -->
<div class="profile-header">
    <div class="row align-items-center">
        <div class="col-auto">
            @if($student->student_passport_photo)
                <img src="{{ $student->hasUsablePhoto() ? \App\Models\Student::documentUrl($student->student_passport_photo) : asset('images/noimage.jpg') }}" onerror="this.onerror=null; this.src='{{ asset('images/noimage.jpg') }}';" alt="{{ $student->student_name }}" class="profile-photo-large">
            @else
                <img src="{{ asset('images/noimage.jpg') }}" alt="No Photo" class="profile-photo-large">
            @endif
            @if($student->photoIsDocument())
                @can('view-sensitive')
                    <a href="{{ \App\Models\Student::documentUrl($student->student_passport_photo) }}" target="_blank" rel="noopener" class="d-block small mt-1" style="color: #fff; opacity: 0.9;">
                        <i class="fas fa-file-pdf me-1"></i>Uploaded as a file, not an image &middot; open
                    </a>
                @endcan
            @endif
        </div>
        <div class="col">
            <h2 class="mb-2" style="font-size: 1.75rem; font-weight: 700;">
                {{ ucwords(strtolower($student->student_name)) }}
            </h2>
            <div class="d-flex gap-3 flex-wrap align-items-center">
                @if($student->student_gender)
                    @php
                        $genderBadge = match($student->student_gender) {
                            'Male' => ['badge-male', 'mars'],
                            'Female' => ['badge-female', 'venus'],
                            default => ['badge-other', 'user'],
                        };
                    @endphp
                    <span class="badge-status {{ $genderBadge[0] }}">
                        <i class="fas fa-{{ $genderBadge[1] }} me-1" aria-hidden="true"></i>
                        {{ $student->student_gender }}
                    </span>
                @endif
                @if($student->current_class)
                    <span class="badge-status" style="background: white; color: var(--primary-color);" title="Current class (intake {{ $student->intake_year ?? '?' }}, Form 1 class {{ $student->form_1_class ?? '?' }})">
                        <i class="fas fa-graduation-cap me-1" aria-hidden="true"></i>Class {{ $student->current_class }}
                    </span>
                @elseif($student->form_1_class)
                    <span class="badge-status" style="background: white; color: var(--primary-color);">
                        <i class="fas fa-graduation-cap me-1" aria-hidden="true"></i>{{ $student->form_1_class }}
                    </span>
                @endif
                @if(!$student->isActive())
                    <span class="badge-status" style="background: #fee2e2; color: #991b1b;">
                        <i class="fas fa-user-slash me-1" aria-hidden="true"></i>{{ $student->enrolment_status_label }}@if($student->status_changed_at) since {{ $student->status_changed_at->format('d/m/Y') }}@endif
                    </span>
                @endif
                @if($student->student_sea_number)
                    <span style="opacity: 0.9;">
                        <i class="fas fa-id-card me-1"></i>SEA: {{ $student->student_sea_number }}
                    </span>
                @endif
            </div>
        </div>
        <div class="col-12 col-md-auto">
            <div class="action-buttons">
                @can('view-sensitive')
                <a href="{{ route('students.print', $student) }}" target="_blank" class="btn btn-outline-light btn-action">
                    <i class="fas fa-print"></i><span class="d-none d-sm-inline"> Print Profile</span><span class="d-inline d-sm-none"> Print</span>
                </a>
                <a href="{{ route('students.pdf', $student) }}" class="btn btn-outline-light btn-action">
                    <i class="fas fa-file-pdf"></i><span class="d-none d-sm-inline"> Download PDF</span><span class="d-inline d-sm-none"> PDF</span>
                </a>
                @endcan
                @can('edit-students')
                <a href="{{ route('students.edit', $student) }}" class="btn btn-light btn-action">
                    <i class="fas fa-edit"></i><span class="d-none d-sm-inline"> Edit Student</span><span class="d-inline d-sm-none"> Edit</span>
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-clipboard-check"></i>
                <h5>Record Completeness</h5>
            </div>
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="flex-grow-1" style="height: 10px; background: #e5e7eb; border-radius: 5px; overflow: hidden;">
                    @php($level = \App\Models\Student::completenessLevel($completeness['percent']))
                    <div style="width: {{ $completeness['percent'] }}%; height: 100%; background: {{ ['ok' => '#16a34a', 'mid' => '#f59e0b', 'low' => '#dc2626'][$level] }};" title="Green 85%+, amber 60%+, red below 60%"></div>
                </div>
                <strong>{{ $completeness['percent'] }}%</strong>
            </div>
            <p class="text-muted mb-2" style="font-size: 0.875rem;">
                {{ $completeness['essentials_recorded'] }} of {{ $completeness['essentials_total'] }} essential items recorded
                &middot; {{ $completeness['recorded'] }} of {{ $completeness['total'] }} fields overall ({{ $completeness['fields_percent'] }}%).
            </p>
            @if($completeness['missing'])
                <p class="mb-1"><strong>Essential items still missing:</strong></p>
                <ul class="mb-2" style="padding-left: 1.25rem;">
                    @foreach($completeness['missing'] as $label)
                        <li>{{ $label }}</li>
                    @endforeach
                </ul>
                @can('edit-students')
                    <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i> Add missing information</a>
                @endcan
            @else
                <p class="mb-0 text-success"><i class="fas fa-check-circle me-1"></i> All essential items are recorded.</p>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-history"></i>
                <h5>History</h5>
            </div>
            @if($activities->isEmpty())
                <p class="text-muted mb-0">No changes recorded yet.</p>
            @else
                <ul class="list-unstyled mb-2" style="font-size: 0.875rem;">
                    @foreach($activities as $activity)
                        <li class="mb-2 pb-2 border-bottom">
                            <div class="d-flex justify-content-between gap-2">
                                <span><strong>{{ $activity->action_label }}</strong> &middot; {{ $activity->summary }}</span>
                                <span class="text-muted text-nowrap" title="{{ $activity->created_at->format('d/m/Y H:i') }}">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-muted">by {{ $activity->user_name }}</div>
                            @if($activity->changes && $activity->action === 'updated')
                                <details class="mt-1">
                                    <summary class="text-muted" style="cursor: pointer;">{{ count($activity->changes) }} field(s) changed</summary>
                                    <ul class="mb-0 mt-1" style="padding-left: 1.25rem;">
                                        @foreach($activity->changes as $field => $change)
                                            <li><strong>{{ \App\Models\Student::fieldLabel($field) }}:</strong> <span class="text-muted">{{ $change['from'] ?? '(blank)' }}</span> &rarr; {{ $change['to'] ?? '(blank)' }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @can('admin')
                    <a href="{{ route('activity.index', ['student' => $student->id]) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-list me-1"></i> Full history</a>
                @endcan
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <!-- Student Personal Information -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-user"></i>
                <h5>Personal Information</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Student Name</div>
                    <div class="info-value">{{ $student->student_name ? ucwords(strtolower($student->student_name)) : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gender</div>
                    <div class="info-value">{{ $student->student_gender ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">{{ $student->formatted_dob }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Current Class</div>
                    <div class="info-value">{{ $student->current_class ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Form 1 Class / Intake</div>
                    <div class="info-value">{{ $student->form_1_class ?? 'Not recorded' }}@if($student->intake_year) &middot; {{ $student->intake_year }}@endif</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Enrolment Status</div>
                    <div class="info-value">{{ $student->enrolment_status_label }}@if($student->status_note) &middot; {{ $student->status_note }}@endif</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Citizenship Type</div>
                    <div class="info-value">{{ $student->citizen_type ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Birth Certificate Pin</div>
                    <div class="info-value">{{ $student->student_birth_certificate_pin ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Religion</div>
                    <div class="info-value">{{ $student->student_religion ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Country of Birth</div>
                    <div class="info-value">{{ $student->student_country_of_birth ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nationality</div>
                    <div class="info-value">{{ $student->student_nationality ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ethnicity</div>
                    <div class="info-value">{{ $student->student_ethnicity ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value">{{ $student->student_contact ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $student->student_email ?? 'Not recorded' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Current Address</div>
                <div class="info-value">{{ $student->student_current_address ? ucwords(strtolower($student->student_current_address)) : 'Not recorded' }}</div>
            </div>
        </div>

        <!-- SEA Information -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-graduation-cap"></i>
                <h5>SEA Information</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">SEA Exam Date</div>
                    <div class="info-value">{{ $student->formatted_sea_date }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">SEA Number</div>
                    <div class="info-value">{{ $student->student_sea_number ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Primary School</div>
                    <div class="info-value">{{ $student->student_primary_school ? ucwords(strtolower($student->student_primary_school)) : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">SEA Slip</div>
                    <div class="info-value">@include('students.partials.document-value', ['value' => $student->student_sea_slip])</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Birth Certificate</div>
                    <div class="info-value">@include('students.partials.document-value', ['value' => $student->student_birth_certificate])</div>
                </div>
            </div>
        </div>

        <!-- Medical Information -->
        @can('view-sensitive')
<div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-heartbeat"></i>
                <h5>Medical Information</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Blood Type</div>
                    <div class="info-value">{{ $student->student_bloodtype ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Immunization Status</div>
                    <div class="info-value">{{ $student->student_immunization_status ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Allergies</div>
                    <div class="info-value">{{ $student->student_allergies ?? 'Not recorded' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Medical Conditions</div>
                <div class="info-value">{{ $student->student_medical_condition ?? 'Not recorded' }}</div>
            </div>
        </div>
@endcan

        <!-- Personal Preferences -->
        @can('view-sensitive')
<div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-cog"></i>
                <h5>Personal Preferences</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">School Feeding Option</div>
                    <div class="info-value">{{ $student->student_school_feeding_option ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Social Welfare Status</div>
                    <div class="info-value">{{ $student->student_social_welfare_status ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mode of Transport</div>
                    <div class="info-value">{{ $student->student_mode_of_transport ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Access to Device</div>
                    <div class="info-value">{{ $student->student_access_to_device ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Device Shared</div>
                    <div class="info-value">{{ $student->student_device_shared ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Reliable Internet</div>
                    <div class="info-value">{{ $student->student_reliable_internet ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Internet Provider</div>
                    <div class="info-value">{{ $student->student_internet_provider ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Online Tools</div>
                    <div class="info-value">{{ $student->student_online_tools ?? 'Not recorded' }}</div>
                </div>
            </div>
            @if($student->student_social_welfare_detail)
            <div class="info-item">
                <div class="info-label">Social Welfare Details</div>
                <div class="info-value">{{ $student->student_social_welfare_detail }}</div>
            </div>
            @endif
        </div>
@endcan
    </div>

    <div class="col-md-6">
        <!-- Mother Information -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-female"></i>
                <h5>Parent/Guardian (Mother)</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Mother's Name</div>
                    <div class="info-value">{{ $student->mother_name ? ucwords(strtolower($student->mother_name)) : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">{{ $student->is_mother_active_or_deceased ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value">{{ $student->mother_contact ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $student->mother_email ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Profession</div>
                    <div class="info-value">{{ $student->mother_profession ? ucwords(strtolower($student->mother_profession)) : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Identification Type</div>
                    <div class="info-value">{{ $student->mother_identification_type ?? 'Not recorded' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Identification Number</div>
                <div class="info-value">@can('view-sensitive'){{ $student->mother_identification_number ?? 'Not recorded' }}@else<span class="text-muted">Hidden</span>@endcan</div>
            </div>
            <div class="info-item">
                <div class="info-label">Death Certificate</div>
                <div class="info-value">@include('students.partials.document-value', ['value' => $student->mother_death_certificate])</div>
            </div>
            <div class="info-item">
                <div class="info-label">Home Address</div>
                <div class="info-value">{{ $student->mother_home_address ? ucwords(strtolower($student->mother_home_address)) : 'Not recorded' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Work Address</div>
                <div class="info-value">{{ $student->mother_work_address ? ucwords(strtolower($student->mother_work_address)) : 'Not recorded' }}</div>
            </div>
        </div>

        <!-- Father Information -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-male"></i>
                <h5>Parent/Guardian (Father)</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Father's Name</div>
                    <div class="info-value">{{ $student->father_name ? ucwords(strtolower($student->father_name)) : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">{{ $student->is_father_active_or_deceased ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value">{{ $student->father_contact ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $student->father_email_address ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Profession</div>
                    <div class="info-value">{{ $student->father_profession ? ucwords(strtolower($student->father_profession)) : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Identification Type</div>
                    <div class="info-value">{{ $student->father_identification_type ?? 'Not recorded' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Identification Number</div>
                <div class="info-value">@can('view-sensitive'){{ $student->father_identification_number ?? 'Not recorded' }}@else<span class="text-muted">Hidden</span>@endcan</div>
            </div>
            <div class="info-item">
                <div class="info-label">Death Certificate</div>
                <div class="info-value">@include('students.partials.document-value', ['value' => $student->father_death_certificate])</div>
            </div>
            <div class="info-item">
                <div class="info-label">Home Address</div>
                <div class="info-value">{{ $student->father_home_address ? ucwords(strtolower($student->father_home_address)) : 'Not recorded' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Work Address</div>
                <div class="info-value">{{ $student->father_work_address ? ucwords(strtolower($student->father_work_address)) : 'Not recorded' }}</div>
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-phone-alt"></i>
                <h5>Emergency Contact</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Contact Name</div>
                    <div class="info-value">{{ $student->emergency_contact_name ? ucwords(strtolower($student->emergency_contact_name)) : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Relation to Student</div>
                    <div class="info-value">{{ $student->emergency_contact_relation_to_student ? ucwords(strtolower($student->emergency_contact_relation_to_student)) : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value">{{ $student->emergency_contact_number ?? 'Not recorded' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Address</div>
                <div class="info-value">{{ $student->emergency_contact_address ? ucwords(strtolower($student->emergency_contact_address)) : 'Not recorded' }}</div>
            </div>
        </div>

        <!-- Registrant Information -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-user-edit"></i>
                <h5>Registrant Information</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Registration Date</div>
                    <div class="info-value">{{ $student->formatted_registration_date }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Relationship to Student</div>
                    <div class="info-value">{{ $student->registrant_relationship_to_student ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Registrant Name</div>
                    <div class="info-value">{{ $student->registrant_name ? ucwords(strtolower($student->registrant_name)) : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Identification Type</div>
                    <div class="info-value">{{ $student->registrant_identification_type ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Identification Number</div>
                    <div class="info-value">@can('view-sensitive'){{ $student->registrant_identification_number ?? 'Not recorded' }}@else<span class="text-muted">Hidden</span>@endcan</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nationality</div>
                    <div class="info-value">{{ $student->registrant_nationality ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $student->registrant_email ?? 'Not recorded' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transfer Information (Conditional) -->
@if($student->student_transfer_status || $student->student_transfer_slip || $student->student_transfer_reason || $student->student_transfer_date || $student->student_previous_form_class || $student->student_previous_secondary_school || $student->student_previous_school_location)
<div class="row">
    <div class="col-12">
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-exchange-alt"></i>
                <h5>Transfer Information</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Transfer Status</div>
                    <div class="info-value">{{ $student->student_transfer_status ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Transfer Slip</div>
                    <div class="info-value">@include('students.partials.document-value', ['value' => $student->student_transfer_slip])</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Transfer Date</div>
                    <div class="info-value">{{ $student->student_transfer_date ? $student->student_transfer_date->format('d/m/Y') : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Previous Form Class</div>
                    <div class="info-value">{{ $student->student_previous_form_class ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Previous Secondary School</div>
                    <div class="info-value">{{ $student->student_previous_secondary_school ? ucwords(strtolower($student->student_previous_secondary_school)) : 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Previous School Location</div>
                    <div class="info-value">{{ $student->student_previous_school_location ? ucwords(strtolower($student->student_previous_school_location)) : 'Not recorded' }}</div>
                </div>
            </div>
            @if($student->student_transfer_reason)
            <div class="info-item">
                <div class="info-label">Transfer Reason</div>
                <div class="info-value">{{ $student->student_transfer_reason }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<!-- Special Needs & Intervention (Conditional) -->
@if($student->student_family_crisis || $student->student_receiving_counselling || $student->student_physical_disabilities || $student->student_learning_disabilities || $student->student_educational_aid || $student->student_special_sea_concessions || $student->student_emotional_factors || $student->student_other_intervention_information)
@can('view-sensitive')
<div class="row">
    <div class="col-12">
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-hands-helping"></i>
                <h5>Special Needs & Intervention</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Family Crisis</div>
                    <div class="info-value">{{ $student->student_family_crisis ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Receiving Counselling</div>
                    <div class="info-value">{{ $student->student_receiving_counselling ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Educational Aid</div>
                    <div class="info-value">{{ $student->student_educational_aid ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Physical Disabilities</div>
                    <div class="info-value">{{ $student->student_physical_disabilities ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Learning Disabilities</div>
                    <div class="info-value">{{ $student->student_learning_disabilities ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Special SEA Concessions</div>
                    <div class="info-value">{{ $student->student_special_sea_concessions ?? 'Not recorded' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Emotional Factors</div>
                    <div class="info-value">{{ $student->student_emotional_factors ?? 'Not recorded' }}</div>
                </div>
            </div>
            @if($student->student_other_intervention_information)
            <div class="info-item">
                <div class="info-label">Other Intervention Information</div>
                <div class="info-value">{{ $student->student_other_intervention_information }}</div>
            </div>
            @endif
        </div>
    </div>
</div>
@endcan
@endif

@endsection
