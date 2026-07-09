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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #e5e7eb;
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
        border-bottom: 2px solid #4f46e5;
    }

    .info-card-header i {
        font-size: 1.5rem;
        color: #4f46e5;
        margin-right: 0.75rem;
        width: 32px;
        text-align: center;
    }

    .info-card-header h5 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        color: #1e293b;
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
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 500;
        color: #1e293b;
        word-wrap: break-word;
    }

    .info-value.empty {
        color: #94a3b8;
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
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .info-card {
            padding: 1rem;
        }

        .info-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .btn-action {
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
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
                <img src="{{ asset($student->student_passport_photo) }}" alt="{{ $student->student_name }}" class="profile-photo-large">
            @else
                <img src="{{ asset('images/noimage.jpg') }}" alt="No Photo" class="profile-photo-large">
            @endif
        </div>
        <div class="col">
            <h2 class="mb-2" style="font-size: 2rem; font-weight: 700;">
                {{ ucwords(strtolower($student->student_name)) }}
            </h2>
            <div class="d-flex gap-3 flex-wrap align-items-center">
                @if($student->student_gender)
                    <span class="badge-status {{ $student->student_gender === 'Male' ? 'badge-male' : 'badge-female' }}">
                        <i class="fas fa-{{ $student->student_gender === 'Male' ? 'mars' : 'venus' }} me-1"></i>
                        {{ $student->student_gender }}
                    </span>
                @endif
                @if($student->form_1_class)
                    <span class="badge-status" style="background: white; color: #667eea;">
                        <i class="fas fa-graduation-cap me-1"></i>Form {{ $student->form_1_class }}
                    </span>
                @endif
                @if($student->student_sea_number)
                    <span style="opacity: 0.9;">
                        <i class="fas fa-id-card me-1"></i>SEA: {{ $student->student_sea_number }}
                    </span>
                @endif
            </div>
        </div>
        <div class="col-auto">
            <div class="action-buttons">
                <a href="{{ route('students.print', $student) }}" target="_blank" class="btn btn-light btn-action">
                    <i class="fas fa-print"></i> Print Profile
                </a>
                <a href="{{ route('students.pdf', $student) }}" class="btn btn-light btn-action">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
                @can('edit-students')
                <a href="{{ route('students.edit', $student) }}" class="btn btn-warning btn-action">
                    <i class="fas fa-edit"></i> Edit Student
                </a>
                @endcan
            </div>
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
                    <div class="info-value">{{ $student->student_name ? ucwords(strtolower($student->student_name)) : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gender</div>
                    <div class="info-value">{{ $student->student_gender ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">{{ $student->formatted_dob }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Form Class</div>
                    <div class="info-value">{{ $student->form_1_class ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Citizenship Type</div>
                    <div class="info-value">{{ $student->citizen_type ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Birth Certificate Pin</div>
                    <div class="info-value">{{ $student->student_birth_certificate_pin ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Religion</div>
                    <div class="info-value">{{ $student->student_religion ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Country of Birth</div>
                    <div class="info-value">{{ $student->student_country_of_birth ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nationality</div>
                    <div class="info-value">{{ $student->student_nationality ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ethnicity</div>
                    <div class="info-value">{{ $student->student_ethnicity ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value">{{ $student->student_contact ?? 'None Provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $student->student_email ?? 'None Provided' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Current Address</div>
                <div class="info-value">{{ $student->student_current_address ? ucwords(strtolower($student->student_current_address)) : 'N/A' }}</div>
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
                    <div class="info-value">{{ $student->student_sea_number ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Primary School</div>
                    <div class="info-value">{{ $student->student_primary_school ? ucwords(strtolower($student->student_primary_school)) : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">SEA Slip</div>
                    <div class="info-value">{{ $student->student_sea_slip ?? 'N/A' }}</div>
                </div>
            </div>
        </div>

        <!-- Medical Information -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-heartbeat"></i>
                <h5>Medical Information</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Blood Type</div>
                    <div class="info-value">{{ $student->student_bloodtype ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Immunization Status</div>
                    <div class="info-value">{{ $student->student_immunization_status ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Allergies</div>
                    <div class="info-value">{{ $student->student_allergies ?? 'No record provided' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Medical Conditions</div>
                <div class="info-value">{{ $student->student_medical_condition ?? 'No record provided' }}</div>
            </div>
        </div>

        <!-- Personal Preferences -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fas fa-cog"></i>
                <h5>Personal Preferences</h5>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">School Feeding Option</div>
                    <div class="info-value">{{ $student->student_school_feeding_option ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Social Welfare Status</div>
                    <div class="info-value">{{ $student->student_social_welfare_status ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mode of Transport</div>
                    <div class="info-value">{{ $student->student_mode_of_transport ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Access to Device</div>
                    <div class="info-value">{{ $student->student_access_to_device ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Device Shared</div>
                    <div class="info-value">{{ $student->student_device_shared ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Reliable Internet</div>
                    <div class="info-value">{{ $student->student_reliable_internet ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Internet Provider</div>
                    <div class="info-value">{{ $student->student_internet_provider ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Online Tools</div>
                    <div class="info-value">{{ $student->student_online_tools ?? 'N/A' }}</div>
                </div>
            </div>
            @if($student->student_social_welfare_detail)
            <div class="info-item">
                <div class="info-label">Social Welfare Details</div>
                <div class="info-value">{{ $student->student_social_welfare_detail }}</div>
            </div>
            @endif
        </div>
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
                    <div class="info-value">{{ $student->mother_name ? ucwords(strtolower($student->mother_name)) : 'No record provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">{{ $student->is_mother_active_or_deceased ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value">{{ $student->mother_contact ?? 'No record provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $student->mother_email ?? 'No record provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Profession</div>
                    <div class="info-value">{{ $student->mother_profession ? ucwords(strtolower($student->mother_profession)) : 'No record provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Identification Type</div>
                    <div class="info-value">{{ $student->mother_identification_type ?? 'No record provided' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Identification Number</div>
                <div class="info-value">{{ $student->mother_identification_number ?? 'No record provided' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Home Address</div>
                <div class="info-value">{{ $student->mother_home_address ? ucwords(strtolower($student->mother_home_address)) : 'No record provided' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Work Address</div>
                <div class="info-value">{{ $student->mother_work_address ? ucwords(strtolower($student->mother_work_address)) : 'No record provided' }}</div>
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
                    <div class="info-value">{{ $student->father_name ? ucwords(strtolower($student->father_name)) : 'No record provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value">{{ $student->is_father_active_or_deceased ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value">{{ $student->father_contact ?? 'No record provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $student->father_email_address ?? 'No record provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Profession</div>
                    <div class="info-value">{{ $student->father_profession ? ucwords(strtolower($student->father_profession)) : 'No record provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Identification Type</div>
                    <div class="info-value">{{ $student->father_identification_type ?? 'No record provided' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Identification Number</div>
                <div class="info-value">{{ $student->father_identification_number ?? 'No record provided' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Home Address</div>
                <div class="info-value">{{ $student->father_home_address ? ucwords(strtolower($student->father_home_address)) : 'No record provided' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Work Address</div>
                <div class="info-value">{{ $student->father_work_address ? ucwords(strtolower($student->father_work_address)) : 'No record provided' }}</div>
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
                    <div class="info-value">{{ $student->emergency_contact_name ? ucwords(strtolower($student->emergency_contact_name)) : 'No record provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Relation to Student</div>
                    <div class="info-value">{{ $student->emergency_contact_relation_to_student ? ucwords(strtolower($student->emergency_contact_relation_to_student)) : 'No record provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value">{{ $student->emergency_contact_number ?? 'No record provided' }}</div>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">Address</div>
                <div class="info-value">{{ $student->emergency_contact_address ? ucwords(strtolower($student->emergency_contact_address)) : 'No record provided' }}</div>
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
                    <div class="info-value">{{ $student->registrant_relationship_to_student ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Registrant Name</div>
                    <div class="info-value">{{ $student->registrant_name ? ucwords(strtolower($student->registrant_name)) : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Identification Type</div>
                    <div class="info-value">{{ $student->registrant_identification_type ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Identification Number</div>
                    <div class="info-value">{{ $student->registrant_identification_number ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nationality</div>
                    <div class="info-value">{{ $student->registrant_nationality ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">{{ $student->registrant_email ?? 'No record provided' }}</div>
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
                    <div class="info-value">{{ $student->student_transfer_status ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Transfer Slip</div>
                    <div class="info-value">{{ $student->student_transfer_slip ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Transfer Date</div>
                    <div class="info-value">{{ $student->student_transfer_date ? $student->student_transfer_date->format('d/m/Y') : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Previous Form Class</div>
                    <div class="info-value">{{ $student->student_previous_form_class ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Previous Secondary School</div>
                    <div class="info-value">{{ $student->student_previous_secondary_school ? ucwords(strtolower($student->student_previous_secondary_school)) : 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Previous School Location</div>
                    <div class="info-value">{{ $student->student_previous_school_location ? ucwords(strtolower($student->student_previous_school_location)) : 'N/A' }}</div>
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
                    <div class="info-value">{{ $student->student_family_crisis ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Receiving Counselling</div>
                    <div class="info-value">{{ $student->student_receiving_counselling ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Educational Aid</div>
                    <div class="info-value">{{ $student->student_educational_aid ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Physical Disabilities</div>
                    <div class="info-value">{{ $student->student_physical_disabilities ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Learning Disabilities</div>
                    <div class="info-value">{{ $student->student_learning_disabilities ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Special SEA Concessions</div>
                    <div class="info-value">{{ $student->student_special_sea_concessions ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Emotional Factors</div>
                    <div class="info-value">{{ $student->student_emotional_factors ?? 'N/A' }}</div>
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
@endif

@endsection
