@extends('layouts.app')

@section('title', 'Edit Student - SLSS')

@section('page-title', 'Edit Student')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
    <li class="breadcrumb-item active">Edit - {{ $student->student_name }}</li>
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
    fieldset {
        border: 2px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    legend {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text-dark);
        padding: 0 0.75rem;
        width: auto;
    }
    .section-header {
        background: var(--primary-color);
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    .subsection-header {
        color: var(--primary-color);
        font-weight: 600;
        font-size: 1rem;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary-light);
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Edit Student Record</h2>
            <p class="text-muted mb-0">Student ID: {{ $student->id }} | {{ $student->student_name }}</p>
        </div>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Student Personal Information -->
        <div class="section-header">
            <i class="fas fa-user me-2"></i>Student's Personal Information
        </div>

        <fieldset>
            <legend>Basic Details</legend>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Student Photo (Passport Size)</label>
                    @if($student->student_passport_photo)
                        <div class="mb-2">
                            <img src="{{ asset($student->student_passport_photo) }}" alt="Current Photo" class="img-thumbnail" style="max-width: 200px;">
                            <small class="d-block text-muted mt-1">Current photo</small>
                        </div>
                    @endif
                    <input type="file" name="student_passport_photo" class="form-control" accept="image/*">
                    <small class="text-muted">Upload new photo to replace current one. File must not exceed 5MB. Allowed: PDF, JPG, PNG</small>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Form 1 Class</label>
                    <select name="form_1_class" class="form-select">
                        <option value="">Select</option>
                        <option value="A" {{ old('form_1_class', $student->form_1_class) == 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ old('form_1_class', $student->form_1_class) == 'B' ? 'selected' : '' }}>B</option>
                        <option value="C" {{ old('form_1_class', $student->form_1_class) == 'C' ? 'selected' : '' }}>C</option>
                        <option value="D" {{ old('form_1_class', $student->form_1_class) == 'D' ? 'selected' : '' }}>D</option>
                        <option value="E" {{ old('form_1_class', $student->form_1_class) == 'E' ? 'selected' : '' }}>E</option>
                        <option value="F" {{ old('form_1_class', $student->form_1_class) == 'F' ? 'selected' : '' }}>F</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">First Name</label>
                    @php
                        $nameParts = explode(' ', $student->student_name ?? '', 2);
                        $firstName = $nameParts[0] ?? '';
                        $lastName = $nameParts[1] ?? '';
                    @endphp
                    <input type="text" name="student_first_name" class="form-control" value="{{ old('student_first_name', $firstName) }}" placeholder="First Name">
                </div>

                <div class="col-md-5">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="student_last_name" class="form-control" value="{{ old('student_last_name', $lastName) }}" placeholder="Last Name">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Gender</label>
                    <select name="student_gender" class="form-select">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('student_gender', $student->student_gender) == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('student_gender', $student->student_gender) == 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="student_dob" class="form-control" value="{{ old('student_dob', $student->student_dob?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Citizenship Type</label>
                    <select name="citizen_type" class="form-select">
                        <option value="">Select</option>
                        <option value="Birth" {{ old('citizen_type', $student->citizen_type) == 'Birth' ? 'selected' : '' }}>Birth</option>
                        <option value="Descent" {{ old('citizen_type', $student->citizen_type) == 'Descent' ? 'selected' : '' }}>Descent</option>
                        <option value="Naturalisation" {{ old('citizen_type', $student->citizen_type) == 'Naturalisation' ? 'selected' : '' }}>Naturalisation</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Birth Certificate PIN</label>
                    <input type="text" name="student_birth_certificate_pin" class="form-control" value="{{ old('student_birth_certificate_pin', $student->student_birth_certificate_pin) }}" placeholder="Birth Cert PIN">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Religion</label>
                    <input type="text" name="student_religion" class="form-control" value="{{ old('student_religion', $student->student_religion) }}" placeholder="Religion">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Country of Birth</label>
                    <input type="text" name="student_country_of_birth" class="form-control" value="{{ old('student_country_of_birth', $student->student_country_of_birth) }}" placeholder="Country of Birth">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="student_nationality" class="form-control" value="{{ old('student_nationality', $student->student_nationality) }}" placeholder="Nationality">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Ethnicity</label>
                    <input type="text" name="student_ethnicity" class="form-control" value="{{ old('student_ethnicity', $student->student_ethnicity) }}" placeholder="Ethnicity">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="student_contact" class="form-control" value="{{ old('student_contact', $student->student_contact) }}" placeholder="Contact No.">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="student_email" class="form-control" value="{{ old('student_email', $student->student_email) }}" placeholder="Email">
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Current Address</legend>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Full Address</label>
                    <textarea name="student_current_address" class="form-control" rows="3" placeholder="Enter full current address">{{ old('student_current_address', $student->student_current_address) }}</textarea>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Birth Certificate</legend>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Birth Certificate (Upload)</label>
                    <input type="file" name="student_birth_certificate" class="form-control" accept=".pdf,.jpg,.png">
                    <small class="text-muted">Allowed: PDF, JPG, PNG</small>
                </div>
            </div>
        </fieldset>

        <!-- SEA Information -->
        <div class="section-header">
            <i class="fas fa-graduation-cap me-2"></i>SEA Information
        </div>

        <fieldset>
            <legend>SEA Details</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">SEA Date</label>
                    <input type="date" name="student_sea_date" class="form-control" value="{{ old('student_sea_date', $student->student_sea_date?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Primary School</label>
                    <input type="text" name="student_primary_school" class="form-control" value="{{ old('student_primary_school', $student->student_primary_school) }}" placeholder="Primary School Name">
                </div>

                <div class="col-md-4">
                    <label class="form-label">SEA Number</label>
                    <input type="text" name="student_sea_number" class="form-control" value="{{ old('student_sea_number', $student->student_sea_number) }}" placeholder="SEA #">
                </div>

                <div class="col-md-12">
                    <label class="form-label">SEA Slip (Upload)</label>
                    <input type="file" name="student_sea_slip" class="form-control" accept=".pdf,.jpg,.png">
                    <small class="text-muted">Allowed: PDF, JPG, PNG</small>
                </div>
            </div>
        </fieldset>

        <!-- Transfer Information -->
        <div class="section-header">
            <i class="fas fa-exchange-alt me-2"></i>Transfer Information
        </div>

        <fieldset>
            <legend>Transfer Status</legend>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Transfer Status</label>
                    <select name="student_transfer_status" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('student_transfer_status', $student->student_transfer_status) == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('student_transfer_status', $student->student_transfer_status) == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Transfer Date</label>
                    <input type="date" name="student_transfer_date" class="form-control" value="{{ old('student_transfer_date', $student->student_transfer_date?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Previous Form Class</label>
                    <input type="text" name="student_previous_form_class" class="form-control" value="{{ old('student_previous_form_class', $student->student_previous_form_class) }}" placeholder="Previous Class">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Previous School</label>
                    <input type="text" name="student_previous_secondary_school" class="form-control" value="{{ old('student_previous_secondary_school', $student->student_previous_secondary_school) }}" placeholder="Previous School">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Previous School Location</label>
                    <textarea name="student_previous_school_location" class="form-control" rows="2" placeholder="Previous school address">{{ old('student_previous_school_location', $student->student_previous_school_location) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Transfer Reason</label>
                    <textarea name="student_transfer_reason" class="form-control" rows="2" placeholder="Reason for transfer">{{ old('student_transfer_reason', $student->student_transfer_reason) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Transfer Slip (Upload)</label>
                    <input type="file" name="student_transfer_slip" class="form-control" accept=".pdf,.jpg,.png">
                    <small class="text-muted">Allowed: PDF, JPG, PNG</small>
                </div>
            </div>
        </fieldset>

        <!-- Medical Information -->
        <div class="section-header">
            <i class="fas fa-heartbeat me-2"></i>Medical Information
        </div>

        <fieldset>
            <legend>Medical Details</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Medical Condition</label>
                    <textarea name="student_medical_condition" class="form-control" rows="2" placeholder="Any medical conditions">{{ old('student_medical_condition', $student->student_medical_condition) }}</textarea>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Blood Type</label>
                    <select name="student_bloodtype" class="form-select">
                        <option value="">Select</option>
                        <option value="A+" {{ old('student_bloodtype', $student->student_bloodtype) == 'A+' ? 'selected' : '' }}>A+</option>
                        <option value="A-" {{ old('student_bloodtype', $student->student_bloodtype) == 'A-' ? 'selected' : '' }}>A-</option>
                        <option value="B+" {{ old('student_bloodtype', $student->student_bloodtype) == 'B+' ? 'selected' : '' }}>B+</option>
                        <option value="B-" {{ old('student_bloodtype', $student->student_bloodtype) == 'B-' ? 'selected' : '' }}>B-</option>
                        <option value="AB+" {{ old('student_bloodtype', $student->student_bloodtype) == 'AB+' ? 'selected' : '' }}>AB+</option>
                        <option value="AB-" {{ old('student_bloodtype', $student->student_bloodtype) == 'AB-' ? 'selected' : '' }}>AB-</option>
                        <option value="O+" {{ old('student_bloodtype', $student->student_bloodtype) == 'O+' ? 'selected' : '' }}>O+</option>
                        <option value="O-" {{ old('student_bloodtype', $student->student_bloodtype) == 'O-' ? 'selected' : '' }}>O-</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Immunization Status</label>
                    <input type="text" name="student_immunization_status" class="form-control" value="{{ old('student_immunization_status', $student->student_immunization_status) }}" placeholder="Immunization Status">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Allergies</label>
                    <textarea name="student_allergies" class="form-control" rows="2" placeholder="Any allergies">{{ old('student_allergies', $student->student_allergies) }}</textarea>
                </div>
            </div>
        </fieldset>

        <!-- Special Needs & Intervention -->
        <div class="section-header">
            <i class="fas fa-hands-helping me-2"></i>Special Needs & Intervention
        </div>

        <fieldset>
            <legend>Student Support Information</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Family Crisis</label>
                    <input type="text" name="student_family_crisis" class="form-control" value="{{ old('student_family_crisis', $student->student_family_crisis) }}" placeholder="Family crisis if any">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Receiving Counselling</label>
                    <textarea name="student_receiving_counselling" class="form-control" rows="2" placeholder="Counselling details">{{ old('student_receiving_counselling', $student->student_receiving_counselling) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Physical Disabilities</label>
                    <textarea name="student_physical_disabilities" class="form-control" rows="2" placeholder="Physical disabilities if any">{{ old('student_physical_disabilities', $student->student_physical_disabilities) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Learning Disabilities</label>
                    <textarea name="student_learning_disabilities" class="form-control" rows="2" placeholder="Learning disabilities if any">{{ old('student_learning_disabilities', $student->student_learning_disabilities) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Educational Aid</label>
                    <input type="text" name="student_educational_aid" class="form-control" value="{{ old('student_educational_aid', $student->student_educational_aid) }}" placeholder="Educational aid received">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Special SEA Concessions</label>
                    <input type="text" name="student_special_sea_concessions" class="form-control" value="{{ old('student_special_sea_concessions', $student->student_special_sea_concessions) }}" placeholder="SEA concessions">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Emotional/Developmental Factors</label>
                    <textarea name="student_emotional_factors" class="form-control" rows="2" placeholder="Emotional or developmental factors">{{ old('student_emotional_factors', $student->student_emotional_factors) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Other Intervention Information</label>
                    <textarea name="student_other_intervention_information" class="form-control" rows="2" placeholder="Other intervention information">{{ old('student_other_intervention_information', $student->student_other_intervention_information) }}</textarea>
                </div>
            </div>
        </fieldset>

        <!-- Personal Preferences -->
        <div class="section-header">
            <i class="fas fa-sliders-h me-2"></i>Personal Preferences
        </div>

        <fieldset>
            <legend>School & Personal Information</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">School Feeding Programme</label>
                    <select name="student_school_feeding_option" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('student_school_feeding_option', $student->student_school_feeding_option) == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('student_school_feeding_option', $student->student_school_feeding_option) == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Social Welfare Status</label>
                    <select name="student_social_welfare_status" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('student_social_welfare_status', $student->student_social_welfare_status) == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('student_social_welfare_status', $student->student_social_welfare_status) == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Social Welfare Detail</label>
                    <input type="text" name="student_social_welfare_detail" class="form-control" value="{{ old('student_social_welfare_detail', $student->student_social_welfare_detail) }}" placeholder="Details">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Mode of Transport</label>
                    <input type="text" name="student_mode_of_transport" class="form-control" value="{{ old('student_mode_of_transport', $student->student_mode_of_transport) }}" placeholder="Transport method">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Access to Device</label>
                    <input type="text" name="student_access_to_device" class="form-control" value="{{ old('student_access_to_device', $student->student_access_to_device) }}" placeholder="Device access">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Device Shared with Others</label>
                    <select name="student_device_shared" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('student_device_shared', $student->student_device_shared) == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('student_device_shared', $student->student_device_shared) == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Reliable Internet</label>
                    <select name="student_reliable_internet" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('student_reliable_internet', $student->student_reliable_internet) == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('student_reliable_internet', $student->student_reliable_internet) == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Internet Provider</label>
                    <input type="text" name="student_internet_provider" class="form-control" value="{{ old('student_internet_provider', $student->student_internet_provider) }}" placeholder="Provider name">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Online Tools</label>
                    <input type="text" name="student_online_tools" class="form-control" value="{{ old('student_online_tools', $student->student_online_tools) }}" placeholder="Online tools used">
                </div>
            </div>
        </fieldset>

        <!-- Mother Information -->
        <div class="section-header">
            <i class="fas fa-female me-2"></i>Mother's Information
        </div>

        <fieldset>
            <legend>Mother's Details</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mother's Name</label>
                    <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $student->mother_name) }}" placeholder="Full name">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Living Status</label>
                    <select name="is_mother_active_or_deceased" class="form-select">
                        <option value="Alive" {{ old('is_mother_active_or_deceased', $student->is_mother_active_or_deceased) == 'Alive' ? 'selected' : '' }}>Alive</option>
                        <option value="Deceased" {{ old('is_mother_active_or_deceased', $student->is_mother_active_or_deceased) == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Identification Type</label>
                    <input type="text" name="mother_identification_type" class="form-control" value="{{ old('mother_identification_type', $student->mother_identification_type) }}" placeholder="ID Type">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Identification Number</label>
                    <input type="text" name="mother_identification_number" class="form-control" value="{{ old('mother_identification_number', $student->mother_identification_number) }}" placeholder="ID Number">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="mother_contact" class="form-control" value="{{ old('mother_contact', $student->mother_contact) }}" placeholder="Contact">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="mother_email" class="form-control" value="{{ old('mother_email', $student->mother_email) }}" placeholder="Email">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Home Address</label>
                    <textarea name="mother_home_address" class="form-control" rows="2" placeholder="Home address">{{ old('mother_home_address', $student->mother_home_address) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Profession</label>
                    <input type="text" name="mother_profession" class="form-control" value="{{ old('mother_profession', $student->mother_profession) }}" placeholder="Profession">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Work Address</label>
                    <textarea name="mother_work_address" class="form-control" rows="2" placeholder="Work address">{{ old('mother_work_address', $student->mother_work_address) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Death Certificate (if deceased)</label>
                    <input type="file" name="mother_death_certificate" class="form-control" accept=".pdf,.jpg,.png">
                    <small class="text-muted">Allowed: PDF, JPG, PNG</small>
                </div>
            </div>
        </fieldset>

        <!-- Father Information -->
        <div class="section-header">
            <i class="fas fa-male me-2"></i>Father's Information
        </div>

        <fieldset>
            <legend>Father's Details</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Father's Name</label>
                    <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name) }}" placeholder="Full name">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Living Status</label>
                    <select name="is_father_active_or_deceased" class="form-select">
                        <option value="Alive" {{ old('is_father_active_or_deceased', $student->is_father_active_or_deceased) == 'Alive' ? 'selected' : '' }}>Alive</option>
                        <option value="Deceased" {{ old('is_father_active_or_deceased', $student->is_father_active_or_deceased) == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Identification Type</label>
                    <input type="text" name="father_identification_type" class="form-control" value="{{ old('father_identification_type', $student->father_identification_type) }}" placeholder="ID Type">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Identification Number</label>
                    <input type="text" name="father_identification_number" class="form-control" value="{{ old('father_identification_number', $student->father_identification_number) }}" placeholder="ID Number">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="father_contact" class="form-control" value="{{ old('father_contact', $student->father_contact) }}" placeholder="Contact">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="father_email_address" class="form-control" value="{{ old('father_email_address', $student->father_email_address) }}" placeholder="Email">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Home Address</label>
                    <textarea name="father_home_address" class="form-control" rows="2" placeholder="Home address">{{ old('father_home_address', $student->father_home_address) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Profession</label>
                    <input type="text" name="father_profession" class="form-control" value="{{ old('father_profession', $student->father_profession) }}" placeholder="Profession">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Work Address</label>
                    <textarea name="father_work_address" class="form-control" rows="2" placeholder="Work address">{{ old('father_work_address', $student->father_work_address) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Death Certificate (if deceased)</label>
                    <input type="file" name="father_death_certificate" class="form-control" accept=".pdf,.jpg,.png">
                    <small class="text-muted">Allowed: PDF, JPG, PNG</small>
                </div>
            </div>
        </fieldset>

        <!-- Emergency Contact -->
        <div class="section-header">
            <i class="fas fa-phone-alt me-2"></i>Emergency Contact
        </div>

        <fieldset>
            <legend>Emergency Contact Information</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Contact Name</label>
                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}" placeholder="Full name">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Relationship to Student</label>
                    <input type="text" name="emergency_contact_relation_to_student" class="form-control" value="{{ old('emergency_contact_relation_to_student', $student->emergency_contact_relation_to_student) }}" placeholder="Relation">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="emergency_contact_number" class="form-control" value="{{ old('emergency_contact_number', $student->emergency_contact_number) }}" placeholder="Phone">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <textarea name="emergency_contact_address" class="form-control" rows="2" placeholder="Emergency contact address">{{ old('emergency_contact_address', $student->emergency_contact_address) }}</textarea>
                </div>
            </div>
        </fieldset>

        <!-- Registration Information -->
        <div class="section-header">
            <i class="fas fa-calendar-check me-2"></i>Registrant Information
        </div>

        <fieldset>
            <legend>Registration Details</legend>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Registration Date</label>
                    <input type="date" name="registration_date" class="form-control" value="{{ old('registration_date', $student->registration_date?->format('Y-m-d') ?? date('Y-m-d')) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Relationship to Student</label>
                    <select name="registrant_relationship_to_student" class="form-select">
                        <option value="">Select</option>
                        <option value="Mother" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Mother' ? 'selected' : '' }}>Mother</option>
                        <option value="Father" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Father' ? 'selected' : '' }}>Father</option>
                        <option value="Guardian" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                        <option value="Other" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Registrant Name</label>
                    <input type="text" name="registrant_name" class="form-control" value="{{ old('registrant_name', $student->registrant_name) }}" placeholder="Person registering">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Identification Type</label>
                    <input type="text" name="registrant_identification_type" class="form-control" value="{{ old('registrant_identification_type', $student->registrant_identification_type) }}" placeholder="ID Type">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Identification Number</label>
                    <input type="text" name="registrant_identification_number" class="form-control" value="{{ old('registrant_identification_number', $student->registrant_identification_number) }}" placeholder="ID Number">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="registrant_nationality" class="form-control" value="{{ old('registrant_nationality', $student->registrant_nationality) }}" placeholder="Nationality">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="registrant_email" class="form-control" value="{{ old('registrant_email', $student->registrant_email) }}" placeholder="Email">
                </div>
            </div>
        </fieldset>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Update Student
            </button>
        </div>
    </form>
</div>
@endsection

