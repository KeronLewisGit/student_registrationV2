@extends('layouts.app')

@section('title', 'Add New Student - SLSS')

@section('page-title', 'Add New Student')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
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
    .required-field::after {
        content: " *";
        color: var(--danger-red);
    }
</style>
@endpush

@section('content')
<div class="form-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Add New Student</h2>
            <p class="text-muted mb-0">Fill in the student information below</p>
        </div>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <form method="POST" action="{{ route('students.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Basic Information -->
        <fieldset>
            <legend><i class="fas fa-user me-2"></i>Basic Information</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label required-field">Student Name</label>
                    <input type="text" name="student_name" class="form-control" value="{{ old('student_name') }}" required placeholder="Enter full name">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Form 1 Class</label>
                    <select name="form_1_class" class="form-select">
                        <option value="">Select Class</option>
                        @foreach(['1A','1B','1C','1D','1E','1F'] as $class)
                            <option value="{{ $class }}" {{ old('form_1_class') == $class ? 'selected' : '' }}>Form {{ $class }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="student_gender" class="form-select">
                        <option value="">Select Gender</option>
                        @foreach(['Male', 'Female', 'Other'] as $gender)
                            <option value="{{ $gender }}" {{ old('student_gender') == $gender ? 'selected' : '' }}>{{ $gender }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Current Address</label>
                    <textarea name="student_current_address" class="form-control" rows="2" placeholder="Enter current residential address">{{ old('student_current_address') }}</textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="student_dob" class="form-control" value="{{ old('student_dob') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Birth Certificate PIN</label>
                    <input type="text" name="student_birth_certificate_pin" class="form-control" value="{{ old('student_birth_certificate_pin') }}" placeholder="Enter PIN">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Religion</label>
                    <input type="text" name="student_religion" class="form-control" value="{{ old('student_religion') }}" placeholder="e.g., Christian, Hindu">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="student_contact" class="form-control" value="{{ old('student_contact') }}" placeholder="e.g., 868-123-4567">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country of Birth</label>
                    <input type="text" name="student_country_of_birth" class="form-control" value="{{ old('student_country_of_birth') }}" placeholder="e.g., Trinidad and Tobago">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="student_nationality" class="form-control" value="{{ old('student_nationality') }}" placeholder="e.g., Trinidadian">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="student_email" class="form-control" value="{{ old('student_email') }}" placeholder="student@example.com">
                </div>
            </div>
        </fieldset>

        <!-- Passport Photo -->
        <fieldset>
            <legend><i class="fas fa-camera me-2"></i>Passport Photo</legend>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Upload Photo (Optional)</label>
                    <input type="file" name="student_passport_photo" class="form-control" accept="image/*">
                    <small class="text-muted">Accepted formats: JPG, PNG, GIF, WEBP. Maximum size: 2MB.</small>
                </div>
            </div>
        </fieldset>

        <!-- SEA Information -->
        <fieldset>
            <legend><i class="fas fa-graduation-cap me-2"></i>SEA Information</legend>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Primary School</label>
                    <input type="text" name="student_primary_school" class="form-control" value="{{ old('student_primary_school') }}" placeholder="Name of primary school">
                </div>
                <div class="col-md-3">
                    <label class="form-label">SEA Number</label>
                    <input type="text" name="student_sea_number" class="form-control" value="{{ old('student_sea_number') }}" placeholder="SEA #">
                </div>
                <div class="col-md-3">
                    <label class="form-label">SEA Date</label>
                    <input type="date" name="student_sea_date" class="form-control" value="{{ old('student_sea_date') }}">
                </div>
            </div>
        </fieldset>

        <!-- Parent/Guardian Information -->
        <fieldset>
            <legend><i class="fas fa-users me-2"></i>Parent/Guardian Information</legend>

            <h6 class="text-muted mb-3">Mother's Information</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Mother's Name</label>
                    <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name') }}" placeholder="Full name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mother's Contact</label>
                    <input type="text" name="mother_contact" class="form-control" value="{{ old('mother_contact') }}" placeholder="Phone number">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Mother's Email</label>
                    <input type="email" name="mother_email" class="form-control" value="{{ old('mother_email') }}" placeholder="email@example.com">
                </div>
            </div>

            <h6 class="text-muted mb-3">Father's Information</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Father's Name</label>
                    <input type="text" name="father_name" class="form-control" value="{{ old('father_name') }}" placeholder="Full name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Father's Contact</label>
                    <input type="text" name="father_contact" class="form-control" value="{{ old('father_contact') }}" placeholder="Phone number">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Father's Email</label>
                    <input type="email" name="father_email_address" class="form-control" value="{{ old('father_email_address') }}" placeholder="email@example.com">
                </div>
            </div>
        </fieldset>

        <!-- Emergency Contact -->
        <fieldset>
            <legend><i class="fas fa-phone-alt me-2"></i>Emergency Contact</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Contact Name</label>
                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name') }}" placeholder="Full name">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Relationship</label>
                    <input type="text" name="emergency_contact_relation_to_student" class="form-control" value="{{ old('emergency_contact_relation_to_student') }}" placeholder="e.g., Aunt, Uncle">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Contact Number</label>
                    <input type="text" name="emergency_contact_number" class="form-control" value="{{ old('emergency_contact_number') }}" placeholder="Phone number">
                </div>
            </div>
        </fieldset>

        <!-- Registration Information -->
        <fieldset>
            <legend><i class="fas fa-calendar-check me-2"></i>Registration Information</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Registration Date</label>
                    <input type="date" name="registration_date" class="form-control" value="{{ old('registration_date', date('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Registrant Name</label>
                    <input type="text" name="registrant_name" class="form-control" value="{{ old('registrant_name') }}" placeholder="Person registering student">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Relationship to Student</label>
                    <select name="registrant_relationship_to_student" class="form-select">
                        <option value="">Select Relationship</option>
                        <option value="Mother" {{ old('registrant_relationship_to_student') == 'Mother' ? 'selected' : '' }}>Mother</option>
                        <option value="Father" {{ old('registrant_relationship_to_student') == 'Father' ? 'selected' : '' }}>Father</option>
                        <option value="Guardian" {{ old('registrant_relationship_to_student') == 'Guardian' ? 'selected' : '' }}>Guardian</option>
                        <option value="Other" {{ old('registrant_relationship_to_student') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>
        </fieldset>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                <i class="fas fa-times me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-check me-1"></i> Create Student
            </button>
        </div>
    </form>
</div>
@endsection
