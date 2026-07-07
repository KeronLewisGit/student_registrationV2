@extends('layouts.app')

@section('title', 'Edit Student - SLSS')

@push('styles')
<style>
    .edit-card {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    }
    fieldset {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    legend {
        font-weight: 700;
        font-size: 1.1rem;
        color: #1f2937;
        padding: 0 0.75rem;
        width: auto;
    }
    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="edit-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Edit Student</h2>
            <p class="text-muted">ID: {{ $student->id }}</p>
        </div>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <fieldset>
            <legend>Student Information</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Form 1 Class</label>
                    <select name="form_1_class" class="form-select">
                        <option value="">—</option>
                        @foreach(['A','B','C','D','E','F'] as $class)
                            <option value="{{ $class }}" {{ $student->form_1_class == $class ? 'selected' : '' }}>{{ $class }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Student Name *</label>
                    <input type="text" name="student_name" class="form-control" value="{{ old('student_name', $student->student_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="student_gender" class="form-select">
                        <option value="">—</option>
                        @foreach(['Male', 'Female', 'Other'] as $gender)
                            <option value="{{ $gender }}" {{ $student->student_gender == $gender ? 'selected' : '' }}>{{ $gender }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Current Address</label>
                    <textarea name="student_current_address" class="form-control" rows="2">{{ old('student_current_address', $student->student_current_address) }}</textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="student_dob" class="form-control" value="{{ old('student_dob', $student->student_dob?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Birth Certificate Pin</label>
                    <input type="text" name="student_birth_certificate_pin" class="form-control" value="{{ old('student_birth_certificate_pin', $student->student_birth_certificate_pin) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Religion</label>
                    <input type="text" name="student_religion" class="form-control" value="{{ old('student_religion', $student->student_religion) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contact</label>
                    <input type="text" name="student_contact" class="form-control" value="{{ old('student_contact', $student->student_contact) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Country of Birth</label>
                    <input type="text" name="student_country_of_birth" class="form-control" value="{{ old('student_country_of_birth', $student->student_country_of_birth) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="student_nationality" class="form-control" value="{{ old('student_nationality', $student->student_nationality) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Email</label>
                    <input type="email" name="student_email" class="form-control" value="{{ old('student_email', $student->student_email) }}">
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Passport Photo</legend>
            <div class="row g-3">
                <div class="col-md-4">
                    @if($student->student_passport_photo)
                        <img src="{{ asset($student->student_passport_photo) }}" alt="Current Photo" class="img-thumbnail" style="max-width: 200px;">
                    @else
                        <p class="text-muted">No photo uploaded</p>
                    @endif
                </div>
                <div class="col-md-8">
                    <label class="form-label">Upload New Photo (optional)</label>
                    <input type="file" name="student_passport_photo" class="form-control" accept="image/*">
                    <small class="text-muted">JPG, PNG, GIF, WEBP. Max 2MB.</small>
                </div>
            </div>
        </fieldset>

        <!-- Note: For brevity, I'm showing key fields. You can add all other fields following the same pattern -->

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
