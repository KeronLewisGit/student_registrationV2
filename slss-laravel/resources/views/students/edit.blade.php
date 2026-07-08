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

        <!-- Basic Information -->
        <fieldset>
            <legend><i class="fas fa-user me-2"></i>Basic Information</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Student Name *</label>
                    <input type="text" name="student_name" class="form-control" value="{{ old('student_name', $student->student_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Form 1 Class</label>
                    <select name="form_1_class" class="form-select">
                        <option value="">Select Class</option>
                        @foreach(['1A','1B','1C','1D','1E','1F'] as $class)
                            <option value="{{ $class }}" {{ old('form_1_class', $student->form_1_class) == $class ? 'selected' : '' }}>Form {{ $class }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="student_gender" class="form-select">
                        <option value="">Select Gender</option>
                        @foreach(['Male', 'Female', 'Other'] as $gender)
                            <option value="{{ $gender }}" {{ old('student_gender', $student->student_gender) == $gender ? 'selected' : '' }}>{{ $gender }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Citizen Type</label>
                    <input type="text" name="citizen_type" class="form-control" value="{{ old('citizen_type', $student->citizen_type) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="student_dob" class="form-control" value="{{ old('student_dob', $student->student_dob?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Birth Certificate PIN</label>
                    <input type="text" name="student_birth_certificate_pin" class="form-control" value="{{ old('student_birth_certificate_pin', $student->student_birth_certificate_pin) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Birth Certificate</label>
                    <input type="text" name="student_birth_certificate" class="form-control" value="{{ old('student_birth_certificate', $student->student_birth_certificate) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Current Address</label>
                    <textarea name="student_current_address" class="form-control" rows="2">{{ old('student_current_address', $student->student_current_address) }}</textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Religion</label>
                    <input type="text" name="student_religion" class="form-control" value="{{ old('student_religion', $student->student_religion) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ethnicity</label>
                    <input type="text" name="student_ethnicity" class="form-control" value="{{ old('student_ethnicity', $student->student_ethnicity) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country of Birth</label>
                    <input type="text" name="student_country_of_birth" class="form-control" value="{{ old('student_country_of_birth', $student->student_country_of_birth) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="student_nationality" class="form-control" value="{{ old('student_nationality', $student->student_nationality) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="student_contact" class="form-control" value="{{ old('student_contact', $student->student_contact) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="student_email" class="form-control" value="{{ old('student_email', $student->student_email) }}">
                </div>
            </div>
        </fieldset>

        <!-- Passport Photo -->
        <fieldset>
            <legend><i class="fas fa-camera me-2"></i>Passport Photo</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    @if($student->student_passport_photo)
                        <img src="{{ asset($student->student_passport_photo) }}" alt="Current Photo" class="img-thumbnail" style="max-width: 200px;">
                    @else
                        <div class="alert alert-info">No photo uploaded</div>
                    @endif
                </div>
                <div class="col-md-8">
                    <label class="form-label">Upload New Photo (Optional)</label>
                    <input type="file" name="student_passport_photo" class="form-control" accept="image/*">
                    <small class="text-muted">JPG, PNG, GIF, WEBP. Maximum 2MB.</small>
                </div>
            </div>
        </fieldset>

        <!-- SEA Information -->
        <fieldset>
            <legend><i class="fas fa-graduation-cap me-2"></i>SEA Information</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Primary School</label>
                    <input type="text" name="student_primary_school" class="form-control" value="{{ old('student_primary_school', $student->student_primary_school) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">SEA Number</label>
                    <input type="text" name="student_sea_number" class="form-control" value="{{ old('student_sea_number', $student->student_sea_number) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">SEA Date</label>
                    <input type="date" name="student_sea_date" class="form-control" value="{{ old('student_sea_date', $student->student_sea_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">SEA Slip</label>
                    <input type="text" name="student_sea_slip" class="form-control" value="{{ old('student_sea_slip', $student->student_sea_slip) }}">
                </div>
            </div>
        </fieldset>

        <!-- Transfer Information -->
        <fieldset>
            <legend><i class="fas fa-exchange-alt me-2"></i>Transfer Information</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Transfer Status</label>
                    <input type="text" name="student_transfer_status" class="form-control" value="{{ old('student_transfer_status', $student->student_transfer_status) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transfer Date</label>
                    <input type="date" name="student_transfer_date" class="form-control" value="{{ old('student_transfer_date', $student->student_transfer_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transfer Slip</label>
                    <input type="text" name="student_transfer_slip" class="form-control" value="{{ old('student_transfer_slip', $student->student_transfer_slip) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Previous Secondary School</label>
                    <input type="text" name="student_previous_secondary_school" class="form-control" value="{{ old('student_previous_secondary_school', $student->student_previous_secondary_school) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Previous School Location</label>
                    <input type="text" name="student_previous_school_location" class="form-control" value="{{ old('student_previous_school_location', $student->student_previous_school_location) }}">
                </div>
            </div>
        </fieldset>

        <!-- Medical Information -->
        <fieldset>
            <legend><i class="fas fa-heartbeat me-2"></i>Medical Information</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Medical Condition(s)</label>
                    <textarea name="student_medical_condition" class="form-control" rows="2">{{ old('student_medical_condition', $student->student_medical_condition) }}</textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Blood Type</label>
                    <select name="student_bloodtype" class="form-select">
                        <option value="">Select</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type)
                            <option value="{{ $type }}" {{ old('student_bloodtype', $student->student_bloodtype) == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Immunization Status</label>
                    <input type="text" name="student_immunization_status" class="form-control" value="{{ old('student_immunization_status', $student->student_immunization_status) }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Allergies</label>
                    <textarea name="student_allergies" class="form-control" rows="2">{{ old('student_allergies', $student->student_allergies) }}</textarea>
                </div>
            </div>
        </fieldset>

        <!-- Special Needs & Intervention -->
        <fieldset>
            <legend><i class="fas fa-hands-helping me-2"></i>Special Needs & Intervention</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Physical Disabilities</label>
                    <textarea name="student_physical_disabilities" class="form-control" rows="2">{{ old('student_physical_disabilities', $student->student_physical_disabilities) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Learning Disabilities</label>
                    <textarea name="student_learning_disabilities" class="form-control" rows="2">{{ old('student_learning_disabilities', $student->student_learning_disabilities) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Receiving Counselling</label>
                    <textarea name="student_receiving_counselling" class="form-control" rows="2">{{ old('student_receiving_counselling', $student->student_receiving_counselling) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Educational Aid</label>
                    <input type="text" name="student_educational_aid" class="form-control" value="{{ old('student_educational_aid', $student->student_educational_aid) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Special SEA Concessions</label>
                    <input type="text" name="student_special_sea_concessions" class="form-control" value="{{ old('student_special_sea_concessions', $student->student_special_sea_concessions) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Family Crisis</label>
                    <input type="text" name="student_family_crisis" class="form-control" value="{{ old('student_family_crisis', $student->student_family_crisis) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Emotional Factors</label>
                    <textarea name="student_emotional_factors" class="form-control" rows="2">{{ old('student_emotional_factors', $student->student_emotional_factors) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Other Intervention Information</label>
                    <textarea name="student_other_intervention_information" class="form-control" rows="2">{{ old('student_other_intervention_information', $student->student_other_intervention_information) }}</textarea>
                </div>
            </div>
        </fieldset>

        <!-- Personal Preferences -->
        <fieldset>
            <legend><i class="fas fa-cog me-2"></i>Personal Preferences</legend>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">School Feeding Option</label>
                    <input type="text" name="student_school_feeding_option" class="form-control" value="{{ old('student_school_feeding_option', $student->student_school_feeding_option) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Social Welfare Status</label>
                    <input type="text" name="student_social_welfare_status" class="form-control" value="{{ old('student_social_welfare_status', $student->student_social_welfare_status) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mode of Transport</label>
                    <input type="text" name="student_mode_of_transport" class="form-control" value="{{ old('student_mode_of_transport', $student->student_mode_of_transport) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Access to Device</label>
                    <input type="text" name="student_access_to_device" class="form-control" value="{{ old('student_access_to_device', $student->student_access_to_device) }}">
                </div>
            </div>
        </fieldset>

        <!-- Mother Information -->
        <fieldset>
            <legend><i class="fas fa-female me-2"></i>Mother's Information</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mother's Name</label>
                    <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $student->mother_name) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="is_mother_active_or_deceased" class="form-select">
                        <option value="">Select</option>
                        <option value="Alive" {{ old('is_mother_active_or_deceased', $student->is_mother_active_or_deceased) == 'Alive' ? 'selected' : '' }}>Alive</option>
                        <option value="Deceased" {{ old('is_mother_active_or_deceased', $student->is_mother_active_or_deceased) == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contact</label>
                    <input type="text" name="mother_contact" class="form-control" value="{{ old('mother_contact', $student->mother_contact) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Type</label>
                    <input type="text" name="mother_identification_type" class="form-control" value="{{ old('mother_identification_type', $student->mother_identification_type) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Number</label>
                    <input type="text" name="mother_identification_number" class="form-control" value="{{ old('mother_identification_number', $student->mother_identification_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Profession</label>
                    <input type="text" name="mother_profession" class="form-control" value="{{ old('mother_profession', $student->mother_profession) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Home Address</label>
                    <textarea name="mother_home_address" class="form-control" rows="2">{{ old('mother_home_address', $student->mother_home_address) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Work Address</label>
                    <textarea name="mother_work_address" class="form-control" rows="2">{{ old('mother_work_address', $student->mother_work_address) }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Email</label>
                    <input type="email" name="mother_email" class="form-control" value="{{ old('mother_email', $student->mother_email) }}">
                </div>
            </div>
        </fieldset>

        <!-- Father Information -->
        <fieldset>
            <legend><i class="fas fa-male me-2"></i>Father's Information</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Father's Name</label>
                    <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="is_father_active_or_deceased" class="form-select">
                        <option value="">Select</option>
                        <option value="Alive" {{ old('is_father_active_or_deceased', $student->is_father_active_or_deceased) == 'Alive' ? 'selected' : '' }}>Alive</option>
                        <option value="Deceased" {{ old('is_father_active_or_deceased', $student->is_father_active_or_deceased) == 'Deceased' ? 'selected' : '' }}>Deceased</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contact</label>
                    <input type="text" name="father_contact" class="form-control" value="{{ old('father_contact', $student->father_contact) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Type</label>
                    <input type="text" name="father_identification_type" class="form-control" value="{{ old('father_identification_type', $student->father_identification_type) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Number</label>
                    <input type="text" name="father_identification_number" class="form-control" value="{{ old('father_identification_number', $student->father_identification_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Profession</label>
                    <input type="text" name="father_profession" class="form-control" value="{{ old('father_profession', $student->father_profession) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Home Address</label>
                    <textarea name="father_home_address" class="form-control" rows="2">{{ old('father_home_address', $student->father_home_address) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Work Address</label>
                    <textarea name="father_work_address" class="form-control" rows="2">{{ old('father_work_address', $student->father_work_address) }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Email</label>
                    <input type="email" name="father_email_address" class="form-control" value="{{ old('father_email_address', $student->father_email_address) }}">
                </div>
            </div>
        </fieldset>

        <!-- Emergency Contact -->
        <fieldset>
            <legend><i class="fas fa-phone-alt me-2"></i>Emergency Contact</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Contact Name</label>
                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Relationship</label>
                    <input type="text" name="emergency_contact_relation_to_student" class="form-control" value="{{ old('emergency_contact_relation_to_student', $student->emergency_contact_relation_to_student) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="emergency_contact_number" class="form-control" value="{{ old('emergency_contact_number', $student->emergency_contact_number) }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <textarea name="emergency_contact_address" class="form-control" rows="2">{{ old('emergency_contact_address', $student->emergency_contact_address) }}</textarea>
                </div>
            </div>
        </fieldset>

        <!-- Registration Information -->
        <fieldset>
            <legend><i class="fas fa-calendar-check me-2"></i>Registration Information</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Registration Date</label>
                    <input type="date" name="registration_date" class="form-control" value="{{ old('registration_date', $student->registration_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Registrant Name</label>
                    <input type="text" name="registrant_name" class="form-control" value="{{ old('registrant_name', $student->registrant_name) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Relationship to Student</label>
                    <select name="registrant_relationship_to_student" class="form-select">
                        <option value="">Select</option>
                        <option value="Mother" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Mother' ? 'selected' : '' }}>Mother</option>
                        <option value="Father" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Father' ? 'selected' : '' }}>Father</option>
                        <option value="Guardian" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                        <option value="Other" {{ old('registrant_relationship_to_student', $student->registrant_relationship_to_student) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Type</label>
                    <input type="text" name="registrant_identification_type" class="form-control" value="{{ old('registrant_identification_type', $student->registrant_identification_type) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ID Number</label>
                    <input type="text" name="registrant_identification_number" class="form-control" value="{{ old('registrant_identification_number', $student->registrant_identification_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="registrant_nationality" class="form-control" value="{{ old('registrant_nationality', $student->registrant_nationality) }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Email</label>
                    <input type="email" name="registrant_email" class="form-control" value="{{ old('registrant_email', $student->registrant_email) }}">
                </div>
            </div>
        </fieldset>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
