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
    /* Page-specific: current-photo thumbnail sizing (shared form styles live in css/slss.css) */
    @media (max-width: 768px) {
        .img-thumbnail {
            max-width: 150px !important;
        }
    }

    @media (max-width: 576px) {
        .img-thumbnail {
            max-width: 120px !important;
        }
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

    @include('students.partials.form-section-nav')

    <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data" data-warn-unsaved>
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
                    <label class="form-label" for="field_student_passport_photo">Student Photo (Passport Size)</label>
                    @if($student->student_passport_photo)
                        <div class="mb-2">
                            <img src="{{ asset($student->student_passport_photo) }}" alt="Current photo of {{ $student->student_name }}" class="img-thumbnail" style="max-width: 200px;">
                            <small class="d-block text-muted mt-1">Current photo</small>
                        </div>
                    @endif
                    <input type="file" id="field_student_passport_photo" name="student_passport_photo" class="form-control" accept="image/*">
                    <small class="text-muted">Upload new photo to replace current one. File must not exceed 5MB. Allowed: JPG, PNG, GIF, WEBP</small>
                </div>

                <div class="col-md-2">
                    <label class="form-label" for="field_form_1_class">Form 1 Class</label>
                    @php
                        // Legacy records store variants like "A" or "Form 1a"; match them
                        // to the canonical option so saving doesn't silently null the class.
                        $currentClass = old('form_1_class', $student->form_1_class);
                        $canonicalClass = \App\Models\Student::canonicalClass($currentClass);
                    @endphp
                    <select id="field_form_1_class" name="form_1_class" class="form-select">
                        <option value="">Select</option>
                        @foreach(\App\Models\Student::FORM_CLASSES as $class)
                            <option value="{{ $class }}" {{ $canonicalClass === $class ? 'selected' : '' }}>{{ $class }}</option>
                        @endforeach
                        @if(!empty($currentClass) && $canonicalClass === null)
                            <option value="{{ $currentClass }}" selected>{{ $currentClass }} (unrecognized legacy value)</option>
                        @endif
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label" for="field_student_first_name">First Name <span class="text-danger">*</span></label>
                    @php
                        $nameParts = explode(' ', $student->student_name ?? '', 2);
                        $firstName = $nameParts[0] ?? '';
                        $lastName = $nameParts[1] ?? '';
                    @endphp
                    <input type="text" id="field_student_first_name" name="student_first_name" class="form-control @error('student_first_name') is-invalid @enderror" value="{{ old('student_first_name', $firstName) }}" placeholder="First Name" required>
                    @error('student_first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-5">
                    <label class="form-label" for="field_student_last_name">Last Name <span class="text-danger">*</span></label>
                    <input type="text" id="field_student_last_name" name="student_last_name" class="form-control @error('student_last_name') is-invalid @enderror" value="{{ old('student_last_name', $lastName) }}" placeholder="Last Name" required>
                    @error('student_last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_student_gender">Gender</label>
                    <select id="field_student_gender" name="student_gender" class="form-select">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('student_gender', $student->student_gender) == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('student_gender', $student->student_gender) == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('student_gender', $student->student_gender) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_student_dob">Date of Birth</label>
                    <input type="date" id="field_student_dob" name="student_dob" class="form-control" value="{{ old('student_dob', $student->student_dob?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_citizen_type">Citizenship Type</label>
                    <select id="field_citizen_type" name="citizen_type" class="form-select">
                        <option value="">Select</option>
                        <option value="Birth" {{ old('citizen_type', $student->citizen_type) == 'Birth' ? 'selected' : '' }}>Birth</option>
                        <option value="Descent" {{ old('citizen_type', $student->citizen_type) == 'Descent' ? 'selected' : '' }}>Descent</option>
                        <option value="Naturalisation" {{ old('citizen_type', $student->citizen_type) == 'Naturalisation' ? 'selected' : '' }}>Naturalisation</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_student_birth_certificate_pin">Birth Certificate PIN</label>
                    <input type="text" id="field_student_birth_certificate_pin" name="student_birth_certificate_pin" class="form-control" value="{{ old('student_birth_certificate_pin', $student->student_birth_certificate_pin) }}" placeholder="Birth Cert PIN">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_religion">Religion</label>
                    <input type="text" id="field_student_religion" name="student_religion" class="form-control" value="{{ old('student_religion', $student->student_religion) }}" placeholder="Religion">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_country_of_birth">Country of Birth</label>
                    <input type="text" id="field_student_country_of_birth" name="student_country_of_birth" class="form-control" value="{{ old('student_country_of_birth', $student->student_country_of_birth) }}" placeholder="Country of Birth">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_nationality">Nationality</label>
                    <input type="text" id="field_student_nationality" name="student_nationality" class="form-control" value="{{ old('student_nationality', $student->student_nationality) }}" placeholder="Nationality">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_ethnicity">Ethnicity</label>
                    <input type="text" id="field_student_ethnicity" name="student_ethnicity" class="form-control" value="{{ old('student_ethnicity', $student->student_ethnicity) }}" placeholder="Ethnicity">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_contact">Contact Number</label>
                    <input type="tel" inputmode="tel" id="field_student_contact" name="student_contact" class="form-control" value="{{ old('student_contact', $student->student_contact) }}" placeholder="Contact No.">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_email">Email Address</label>
                    <input type="email" id="field_student_email" name="student_email" class="form-control" value="{{ old('student_email', $student->student_email) }}" placeholder="Email">
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Current Address</legend>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="field_student_current_address">Full Address</label>
                    <textarea id="field_student_current_address" name="student_current_address" class="form-control" rows="3" placeholder="Enter full current address">{{ old('student_current_address', $student->student_current_address) }}</textarea>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Birth Certificate</legend>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label" for="field_student_birth_certificate">Birth Certificate (Upload)</label>
                    <input type="file" id="field_student_birth_certificate" name="student_birth_certificate" class="form-control" accept=".pdf,.jpg,.png">
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
                    <label class="form-label" for="field_student_sea_date">SEA Date</label>
                    <input type="date" id="field_student_sea_date" name="student_sea_date" class="form-control" value="{{ old('student_sea_date', $student->student_sea_date?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_primary_school">Primary School</label>
                    <input type="text" id="field_student_primary_school" name="student_primary_school" class="form-control" value="{{ old('student_primary_school', $student->student_primary_school) }}" placeholder="Primary School Name">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_sea_number">SEA Number</label>
                    <input type="text" id="field_student_sea_number" name="student_sea_number" class="form-control" value="{{ old('student_sea_number', $student->student_sea_number) }}" placeholder="SEA #">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_student_sea_slip">SEA Slip (Upload)</label>
                    <input type="file" id="field_student_sea_slip" name="student_sea_slip" class="form-control" accept=".pdf,.jpg,.png">
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
                    <label class="form-label" for="field_student_transfer_status">Transfer Status</label>
                    <select id="field_student_transfer_status" name="student_transfer_status" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('student_transfer_status', $student->student_transfer_status) == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('student_transfer_status', $student->student_transfer_status) == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_student_transfer_date">Transfer Date</label>
                    <input type="date" id="field_student_transfer_date" name="student_transfer_date" class="form-control" value="{{ old('student_transfer_date', $student->student_transfer_date?->format('Y-m-d')) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_student_previous_form_class">Previous Form Class</label>
                    <input type="text" id="field_student_previous_form_class" name="student_previous_form_class" class="form-control" value="{{ old('student_previous_form_class', $student->student_previous_form_class) }}" placeholder="Previous Class">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_student_previous_secondary_school">Previous School</label>
                    <input type="text" id="field_student_previous_secondary_school" name="student_previous_secondary_school" class="form-control" value="{{ old('student_previous_secondary_school', $student->student_previous_secondary_school) }}" placeholder="Previous School">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_student_previous_school_location">Previous School Location</label>
                    <textarea id="field_student_previous_school_location" name="student_previous_school_location" class="form-control" rows="2" placeholder="Previous school address">{{ old('student_previous_school_location', $student->student_previous_school_location) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_student_transfer_reason">Transfer Reason</label>
                    <textarea id="field_student_transfer_reason" name="student_transfer_reason" class="form-control" rows="2" placeholder="Reason for transfer">{{ old('student_transfer_reason', $student->student_transfer_reason) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_student_transfer_slip">Transfer Slip (Upload)</label>
                    <input type="file" id="field_student_transfer_slip" name="student_transfer_slip" class="form-control" accept=".pdf,.jpg,.png">
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
                    <label class="form-label" for="field_student_medical_condition">Medical Condition</label>
                    <textarea id="field_student_medical_condition" name="student_medical_condition" class="form-control" rows="2" placeholder="Any medical conditions">{{ old('student_medical_condition', $student->student_medical_condition) }}</textarea>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_student_bloodtype">Blood Type</label>
                    <select id="field_student_bloodtype" name="student_bloodtype" class="form-select">
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
                    <label class="form-label" for="field_student_immunization_status">Immunization Status</label>
                    <input type="text" id="field_student_immunization_status" name="student_immunization_status" class="form-control" value="{{ old('student_immunization_status', $student->student_immunization_status) }}" placeholder="Immunization Status">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_student_allergies">Allergies</label>
                    <textarea id="field_student_allergies" name="student_allergies" class="form-control" rows="2" placeholder="Any allergies">{{ old('student_allergies', $student->student_allergies) }}</textarea>
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
                    <label class="form-label" for="field_student_family_crisis">Family Crisis</label>
                    <input type="text" id="field_student_family_crisis" name="student_family_crisis" class="form-control" value="{{ old('student_family_crisis', $student->student_family_crisis) }}" placeholder="Family crisis if any">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="field_student_receiving_counselling">Receiving Counselling</label>
                    <textarea id="field_student_receiving_counselling" name="student_receiving_counselling" class="form-control" rows="2" placeholder="Counselling details">{{ old('student_receiving_counselling', $student->student_receiving_counselling) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="field_student_physical_disabilities">Physical Disabilities</label>
                    <textarea id="field_student_physical_disabilities" name="student_physical_disabilities" class="form-control" rows="2" placeholder="Physical disabilities if any">{{ old('student_physical_disabilities', $student->student_physical_disabilities) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="field_student_learning_disabilities">Learning Disabilities</label>
                    <textarea id="field_student_learning_disabilities" name="student_learning_disabilities" class="form-control" rows="2" placeholder="Learning disabilities if any">{{ old('student_learning_disabilities', $student->student_learning_disabilities) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="field_student_educational_aid">Educational Aid</label>
                    <input type="text" id="field_student_educational_aid" name="student_educational_aid" class="form-control" value="{{ old('student_educational_aid', $student->student_educational_aid) }}" placeholder="Educational aid received">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="field_student_special_sea_concessions">Special SEA Concessions</label>
                    <input type="text" id="field_student_special_sea_concessions" name="student_special_sea_concessions" class="form-control" value="{{ old('student_special_sea_concessions', $student->student_special_sea_concessions) }}" placeholder="SEA concessions">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_student_emotional_factors">Emotional/Developmental Factors</label>
                    <textarea id="field_student_emotional_factors" name="student_emotional_factors" class="form-control" rows="2" placeholder="Emotional or developmental factors">{{ old('student_emotional_factors', $student->student_emotional_factors) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_student_other_intervention_information">Other Intervention Information</label>
                    <textarea id="field_student_other_intervention_information" name="student_other_intervention_information" class="form-control" rows="2" placeholder="Other intervention information">{{ old('student_other_intervention_information', $student->student_other_intervention_information) }}</textarea>
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
                    <label class="form-label" for="field_student_school_feeding_option">School Feeding Programme</label>
                    <select id="field_student_school_feeding_option" name="student_school_feeding_option" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('student_school_feeding_option', $student->student_school_feeding_option) == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('student_school_feeding_option', $student->student_school_feeding_option) == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_social_welfare_status">Social Welfare Status</label>
                    <select id="field_student_social_welfare_status" name="student_social_welfare_status" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('student_social_welfare_status', $student->student_social_welfare_status) == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('student_social_welfare_status', $student->student_social_welfare_status) == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_social_welfare_detail">Social Welfare Detail</label>
                    <input type="text" id="field_student_social_welfare_detail" name="student_social_welfare_detail" class="form-control" value="{{ old('student_social_welfare_detail', $student->student_social_welfare_detail) }}" placeholder="Details">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_mode_of_transport">Mode of Transport</label>
                    <input type="text" id="field_student_mode_of_transport" name="student_mode_of_transport" class="form-control" value="{{ old('student_mode_of_transport', $student->student_mode_of_transport) }}" placeholder="Transport method">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_access_to_device">Access to Device</label>
                    <input type="text" id="field_student_access_to_device" name="student_access_to_device" class="form-control" value="{{ old('student_access_to_device', $student->student_access_to_device) }}" placeholder="Device access">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_device_shared">Device Shared with Others</label>
                    <select id="field_student_device_shared" name="student_device_shared" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('student_device_shared', $student->student_device_shared) == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('student_device_shared', $student->student_device_shared) == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_reliable_internet">Reliable Internet</label>
                    <select id="field_student_reliable_internet" name="student_reliable_internet" class="form-select">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('student_reliable_internet', $student->student_reliable_internet) == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('student_reliable_internet', $student->student_reliable_internet) == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_internet_provider">Internet Provider</label>
                    <input type="text" id="field_student_internet_provider" name="student_internet_provider" class="form-control" value="{{ old('student_internet_provider', $student->student_internet_provider) }}" placeholder="Provider name">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_student_online_tools">Online Tools</label>
                    <input type="text" id="field_student_online_tools" name="student_online_tools" class="form-control" value="{{ old('student_online_tools', $student->student_online_tools) }}" placeholder="Online tools used">
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
                    <label class="form-label" for="field_mother_name">Mother's Name</label>
                    <input type="text" id="field_mother_name" name="mother_name" class="form-control" value="{{ old('mother_name', $student->mother_name) }}" placeholder="Full name">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_is_mother_active_or_deceased">Living Status</label>
                    <select id="field_is_mother_active_or_deceased" name="is_mother_active_or_deceased" class="form-select">
                        <option value="">Select</option>
                        <option value="Alive" {{ old('is_mother_active_or_deceased', $student->is_mother_active_or_deceased) == 'Alive' ? 'selected' : '' }}>Alive</option>
                        <option value="Deceased" {{ old('is_mother_active_or_deceased', $student->is_mother_active_or_deceased) == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_mother_identification_type">Identification Type</label>
                    <input type="text" id="field_mother_identification_type" name="mother_identification_type" class="form-control" value="{{ old('mother_identification_type', $student->mother_identification_type) }}" placeholder="ID Type">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_mother_identification_number">Identification Number</label>
                    <input type="text" id="field_mother_identification_number" name="mother_identification_number" class="form-control" value="{{ old('mother_identification_number', $student->mother_identification_number) }}" placeholder="ID Number">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_mother_contact">Contact Number</label>
                    <input type="tel" inputmode="tel" id="field_mother_contact" name="mother_contact" class="form-control" value="{{ old('mother_contact', $student->mother_contact) }}" placeholder="Contact">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_mother_email">Email</label>
                    <input type="email" id="field_mother_email" name="mother_email" class="form-control" value="{{ old('mother_email', $student->mother_email) }}" placeholder="Email">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_mother_home_address">Home Address</label>
                    <textarea id="field_mother_home_address" name="mother_home_address" class="form-control" rows="2" placeholder="Home address">{{ old('mother_home_address', $student->mother_home_address) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="field_mother_profession">Profession</label>
                    <input type="text" id="field_mother_profession" name="mother_profession" class="form-control" value="{{ old('mother_profession', $student->mother_profession) }}" placeholder="Profession">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="field_mother_work_address">Work Address</label>
                    <textarea id="field_mother_work_address" name="mother_work_address" class="form-control" rows="2" placeholder="Work address">{{ old('mother_work_address', $student->mother_work_address) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_mother_death_certificate">Death Certificate (if deceased)</label>
                    <input type="file" id="field_mother_death_certificate" name="mother_death_certificate" class="form-control" accept=".pdf,.jpg,.png">
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
                    <label class="form-label" for="field_father_name">Father's Name</label>
                    <input type="text" id="field_father_name" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name) }}" placeholder="Full name">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_is_father_active_or_deceased">Living Status</label>
                    <select id="field_is_father_active_or_deceased" name="is_father_active_or_deceased" class="form-select">
                        <option value="">Select</option>
                        <option value="Alive" {{ old('is_father_active_or_deceased', $student->is_father_active_or_deceased) == 'Alive' ? 'selected' : '' }}>Alive</option>
                        <option value="Deceased" {{ old('is_father_active_or_deceased', $student->is_father_active_or_deceased) == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_father_identification_type">Identification Type</label>
                    <input type="text" id="field_father_identification_type" name="father_identification_type" class="form-control" value="{{ old('father_identification_type', $student->father_identification_type) }}" placeholder="ID Type">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_father_identification_number">Identification Number</label>
                    <input type="text" id="field_father_identification_number" name="father_identification_number" class="form-control" value="{{ old('father_identification_number', $student->father_identification_number) }}" placeholder="ID Number">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_father_contact">Contact Number</label>
                    <input type="tel" inputmode="tel" id="field_father_contact" name="father_contact" class="form-control" value="{{ old('father_contact', $student->father_contact) }}" placeholder="Contact">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="field_father_email_address">Email</label>
                    <input type="email" id="field_father_email_address" name="father_email_address" class="form-control" value="{{ old('father_email_address', $student->father_email_address) }}" placeholder="Email">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_father_home_address">Home Address</label>
                    <textarea id="field_father_home_address" name="father_home_address" class="form-control" rows="2" placeholder="Home address">{{ old('father_home_address', $student->father_home_address) }}</textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="field_father_profession">Profession</label>
                    <input type="text" id="field_father_profession" name="father_profession" class="form-control" value="{{ old('father_profession', $student->father_profession) }}" placeholder="Profession">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="field_father_work_address">Work Address</label>
                    <textarea id="field_father_work_address" name="father_work_address" class="form-control" rows="2" placeholder="Work address">{{ old('father_work_address', $student->father_work_address) }}</textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_father_death_certificate">Death Certificate (if deceased)</label>
                    <input type="file" id="field_father_death_certificate" name="father_death_certificate" class="form-control" accept=".pdf,.jpg,.png">
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
                    <label class="form-label" for="field_emergency_contact_name">Contact Name</label>
                    <input type="text" id="field_emergency_contact_name" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}" placeholder="Full name">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_emergency_contact_relation_to_student">Relationship to Student</label>
                    <input type="text" id="field_emergency_contact_relation_to_student" name="emergency_contact_relation_to_student" class="form-control" value="{{ old('emergency_contact_relation_to_student', $student->emergency_contact_relation_to_student) }}" placeholder="Relation">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_emergency_contact_number">Contact Number</label>
                    <input type="tel" inputmode="tel" id="field_emergency_contact_number" name="emergency_contact_number" class="form-control" value="{{ old('emergency_contact_number', $student->emergency_contact_number) }}" placeholder="Phone">
                </div>

                <div class="col-md-12">
                    <label class="form-label" for="field_emergency_contact_address">Address</label>
                    <textarea id="field_emergency_contact_address" name="emergency_contact_address" class="form-control" rows="2" placeholder="Emergency contact address">{{ old('emergency_contact_address', $student->emergency_contact_address) }}</textarea>
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
                    <label class="form-label" for="field_registration_date">Registration Date</label>
                    <input type="date" id="field_registration_date" name="registration_date" class="form-control" value="{{ old('registration_date', $student->registration_date?->format('Y-m-d') ?? date('Y-m-d')) }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_registrant_relationship_to_student">Relationship to Student</label>
                    <select id="field_registrant_relationship_to_student" name="registrant_relationship_to_student" class="form-select">
                        <option value="">Select</option>
                        <option value="Mother" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Mother' ? 'selected' : '' }}>Mother</option>
                        <option value="Father" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Father' ? 'selected' : '' }}>Father</option>
                        <option value="Guardian" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                        <option value="Other" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="field_registrant_name">Registrant Name</label>
                    <input type="text" id="field_registrant_name" name="registrant_name" class="form-control" value="{{ old('registrant_name', $student->registrant_name) }}" placeholder="Person registering">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_registrant_identification_type">Identification Type</label>
                    <input type="text" id="field_registrant_identification_type" name="registrant_identification_type" class="form-control" value="{{ old('registrant_identification_type', $student->registrant_identification_type) }}" placeholder="ID Type">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_registrant_identification_number">Identification Number</label>
                    <input type="text" id="field_registrant_identification_number" name="registrant_identification_number" class="form-control" value="{{ old('registrant_identification_number', $student->registrant_identification_number) }}" placeholder="ID Number">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_registrant_nationality">Nationality</label>
                    <input type="text" id="field_registrant_nationality" name="registrant_nationality" class="form-control" value="{{ old('registrant_nationality', $student->registrant_nationality) }}" placeholder="Nationality">
                </div>

                <div class="col-md-3">
                    <label class="form-label" for="field_registrant_email">Email</label>
                    <input type="email" id="field_registrant_email" name="registrant_email" class="form-control" value="{{ old('registrant_email', $student->registrant_email) }}" placeholder="Email">
                </div>
            </div>
        </fieldset>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4 sticky-bottom-actions">
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

