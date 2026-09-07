{{-- All sections of a printed student profile. Every value is rendered through
     students.partials.field so that nothing is ever left blank. --}}

<!-- Student Personal Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom">Student Personal Information</div>
    <div class="row g-3">
        @include('students.partials.field', ['label' => 'Form Class', 'value' => $student->form_1_class])
        @include('students.partials.field', ['label' => 'Student Name', 'value' => $student->student_name, 'format' => 'name'])
        @include('students.partials.field', ['label' => 'Gender', 'value' => $student->student_gender])
        @include('students.partials.field', ['label' => 'Date of Birth', 'value' => $student->student_dob, 'format' => 'date'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Citizenship Type', 'value' => $student->citizen_type])
        @include('students.partials.field', ['label' => 'Birth Certificate Pin', 'value' => $student->student_birth_certificate_pin])
        @include('students.partials.field', ['label' => 'Birth Certificate', 'value' => $student->student_birth_certificate, 'format' => 'document'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Religion', 'value' => $student->student_religion])
        @include('students.partials.field', ['label' => 'Country of Birth', 'value' => $student->student_country_of_birth])
        @include('students.partials.field', ['label' => 'Nationality', 'value' => $student->student_nationality])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Ethnicity', 'value' => $student->student_ethnicity])
        @include('students.partials.field', ['label' => 'Contact Number', 'value' => $student->student_contact])
        @include('students.partials.field', ['label' => 'Email Address', 'value' => $student->student_email])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Current Address', 'value' => $student->student_current_address, 'format' => 'name', 'col' => 'col-md-12'])
    </div>
</div>

<!-- SEA Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom">SEA Information</div>
    <div class="row g-3">
        @include('students.partials.field', ['label' => 'SEA Exam Date', 'value' => $student->student_sea_date, 'format' => 'date'])
        @include('students.partials.field', ['label' => 'Primary School', 'value' => $student->student_primary_school, 'format' => 'name'])
        @include('students.partials.field', ['label' => 'SEA Number', 'value' => $student->student_sea_number])
        @include('students.partials.field', ['label' => 'SEA Slip', 'value' => $student->student_sea_slip, 'format' => 'document'])
    </div>
</div>

<!-- Transfer Information -->
@php
    $hasTransferDetails = $student->student_transfer_slip || $student->student_transfer_reason || $student->student_transfer_date
        || $student->student_previous_form_class || $student->student_previous_secondary_school || $student->student_previous_school_location;
    $isTransfer = $student->student_transfer_status && !in_array(strtolower(trim($student->student_transfer_status)), ['no', 'n/a', 'none'], true);
@endphp
@if($isTransfer || $hasTransferDetails)
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom">Transfer Information</div>
    <div class="row g-3">
        @include('students.partials.field', ['label' => 'Transfer Status', 'value' => $student->student_transfer_status])
        @include('students.partials.field', ['label' => 'Transfer Slip', 'value' => $student->student_transfer_slip, 'format' => 'document'])
        @include('students.partials.field', ['label' => 'Transfer Date', 'value' => $student->student_transfer_date, 'format' => 'date'])
        @include('students.partials.field', ['label' => 'Previous Form Class', 'value' => $student->student_previous_form_class])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Previous Secondary School', 'value' => $student->student_previous_secondary_school, 'format' => 'name', 'col' => 'col-md-6'])
        @include('students.partials.field', ['label' => 'Previous School Location', 'value' => $student->student_previous_school_location, 'format' => 'name', 'col' => 'col-md-6'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Transfer Reason', 'value' => $student->student_transfer_reason, 'col' => 'col-md-12'])
    </div>
</div>
@else
{{-- Not a transfer student (or nothing recorded): one line instead of a block of empty fields --}}
<div class="section-card section-card-compact">
    <div class="fw-bold">
        Transfer Information
        <span class="compact-value">
            @if($student->student_transfer_status)
                Not a transfer student
            @else
                <span class="not-recorded">Not recorded</span>
            @endif
        </span>
    </div>
</div>
@endif

<!-- Medical Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom">Medical Information</div>
    <div class="row g-3">
        @include('students.partials.field', ['label' => 'Blood Type', 'value' => $student->student_bloodtype])
        @include('students.partials.field', ['label' => 'Immunization Status', 'value' => $student->student_immunization_status])
        @include('students.partials.field', ['label' => 'Allergies', 'value' => $student->student_allergies, 'col' => 'col-md-6'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Medical Conditions', 'value' => $student->student_medical_condition, 'col' => 'col-md-12'])
    </div>
</div>

<!-- Special Needs & Intervention -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom">Special Needs &amp; Intervention</div>
    <div class="row g-3">
        @include('students.partials.field', ['label' => 'Family Crisis', 'value' => $student->student_family_crisis, 'format' => 'yesno'])
        @include('students.partials.field', ['label' => 'Receiving Counselling', 'value' => $student->student_receiving_counselling, 'format' => 'yesno'])
        @include('students.partials.field', ['label' => 'Educational Aid', 'value' => $student->student_educational_aid, 'format' => 'yesno'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Physical Disabilities', 'value' => $student->student_physical_disabilities, 'format' => 'yesno'])
        @include('students.partials.field', ['label' => 'Learning Disabilities', 'value' => $student->student_learning_disabilities, 'format' => 'yesno'])
        @include('students.partials.field', ['label' => 'Special SEA Concessions', 'value' => $student->student_special_sea_concessions, 'format' => 'yesno'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Emotional Factors', 'value' => $student->student_emotional_factors, 'format' => 'yesno', 'col' => 'col-md-6'])
        @include('students.partials.field', ['label' => 'Other Intervention Information', 'value' => $student->student_other_intervention_information, 'format' => 'yesno', 'col' => 'col-md-6'])
    </div>
</div>

<!-- Personal Preferences -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom">Personal Preferences</div>
    <div class="row g-3">
        @include('students.partials.field', ['label' => 'School Feeding Option', 'value' => $student->student_school_feeding_option])
        @include('students.partials.field', ['label' => 'Social Welfare Status', 'value' => $student->student_social_welfare_status])
        @include('students.partials.field', ['label' => 'Mode of Transport', 'value' => $student->student_mode_of_transport])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Social Welfare Details', 'value' => $student->student_social_welfare_detail, 'col' => 'col-md-12'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Access to Device', 'value' => $student->student_access_to_device])
        @include('students.partials.field', ['label' => 'Device Shared', 'value' => $student->student_device_shared])
        @include('students.partials.field', ['label' => 'Reliable Internet', 'value' => $student->student_reliable_internet])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Internet Provider', 'value' => $student->student_internet_provider, 'col' => 'col-md-6'])
        @include('students.partials.field', ['label' => 'Online Tools', 'value' => $student->student_online_tools, 'col' => 'col-md-6'])
    </div>
</div>

<!-- Mother Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom">Parent/Guardian Information (Mother)</div>
    <div class="row g-3">
        @include('students.partials.field', ['label' => "Mother's Name", 'value' => $student->mother_name, 'format' => 'name'])
        @include('students.partials.field', ['label' => 'Status', 'value' => $student->is_mother_active_or_deceased])
        @include('students.partials.field', ['label' => 'Death Certificate', 'value' => $student->mother_death_certificate, 'format' => 'document'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Identification Type', 'value' => $student->mother_identification_type])
        @include('students.partials.field', ['label' => 'Identification Number', 'value' => $student->mother_identification_number])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Home Address', 'value' => $student->mother_home_address, 'format' => 'name', 'col' => 'col-md-12'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Contact Number', 'value' => $student->mother_contact])
        @include('students.partials.field', ['label' => 'Profession', 'value' => $student->mother_profession, 'format' => 'name'])
        @include('students.partials.field', ['label' => 'Email Address', 'value' => $student->mother_email])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Work Address', 'value' => $student->mother_work_address, 'format' => 'name', 'col' => 'col-md-12'])
    </div>
</div>

<!-- Father Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom">Parent/Guardian Information (Father)</div>
    <div class="row g-3">
        @include('students.partials.field', ['label' => "Father's Name", 'value' => $student->father_name, 'format' => 'name'])
        @include('students.partials.field', ['label' => 'Status', 'value' => $student->is_father_active_or_deceased])
        @include('students.partials.field', ['label' => 'Death Certificate', 'value' => $student->father_death_certificate, 'format' => 'document'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Identification Type', 'value' => $student->father_identification_type])
        @include('students.partials.field', ['label' => 'Identification Number', 'value' => $student->father_identification_number])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Home Address', 'value' => $student->father_home_address, 'format' => 'name', 'col' => 'col-md-12'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Contact Number', 'value' => $student->father_contact])
        @include('students.partials.field', ['label' => 'Profession', 'value' => $student->father_profession, 'format' => 'name'])
        @include('students.partials.field', ['label' => 'Email Address', 'value' => $student->father_email_address])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Work Address', 'value' => $student->father_work_address, 'format' => 'name', 'col' => 'col-md-12'])
    </div>
</div>

<!-- Emergency Contact -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom">Emergency Contact Information</div>
    <div class="row g-3">
        @include('students.partials.field', ['label' => 'Contact Name', 'value' => $student->emergency_contact_name, 'format' => 'name'])
        @include('students.partials.field', ['label' => 'Relation to Student', 'value' => $student->emergency_contact_relation_to_student, 'format' => 'name'])
        @include('students.partials.field', ['label' => 'Contact Number', 'value' => $student->emergency_contact_number])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Address', 'value' => $student->emergency_contact_address, 'format' => 'name', 'col' => 'col-md-12'])
    </div>
</div>

<!-- Registrant Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom">Registrant Information</div>
    <div class="row g-3">
        @include('students.partials.field', ['label' => 'Registration Date', 'value' => $student->registration_date, 'format' => 'date'])
        @include('students.partials.field', ['label' => 'Relationship to Student', 'value' => $student->registrant_relationship_to_student])
        @include('students.partials.field', ['label' => 'Registrant Name', 'value' => $student->registrant_name, 'format' => 'name'])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Identification Type', 'value' => $student->registrant_identification_type])
        @include('students.partials.field', ['label' => 'Identification Number', 'value' => $student->registrant_identification_number])
        @include('students.partials.field', ['label' => 'Nationality', 'value' => $student->registrant_nationality])
    </div>
    <div class="row g-3 mt-2">
        @include('students.partials.field', ['label' => 'Email Address', 'value' => $student->registrant_email, 'col' => 'col-md-12'])
    </div>
</div>
