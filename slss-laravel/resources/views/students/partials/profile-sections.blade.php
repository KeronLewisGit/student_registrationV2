<!-- Student Personal Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1.1rem; color: #111;">
        <i class="fas fa-user me-2"></i>Student Personal Information
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <h5>Form Class:</h5>
            <p>{{ $student->form_1_class ?? 'N/A' }}</p>
        </div>
        <div class="col-md-3">
            <h5>Student Name:</h5>
            <p>{{ $student->student_name ? ucwords(strtolower($student->student_name)) : 'N/A' }}</p>
        </div>
        <div class="col-md-3">
            <h5>Gender:</h5>
            <p>{{ $student->student_gender ?? 'N/A' }}</p>
        </div>
        <div class="col-md-3">
            <h5>Date of Birth:</h5>
            <p>{{ $student->formatted_dob }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Citizenship Type:</h5>
            <p>{{ $student->citizen_type ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Birth Certificate Pin:</h5>
            <p>{{ $student->student_birth_certificate_pin ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Birth Certificate:</h5>
            <p>{{ $student->student_birth_certificate ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Religion:</h5>
            <p>{{ $student->student_religion ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Country of Birth:</h5>
            <p>{{ $student->student_country_of_birth ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Nationality:</h5>
            <p>{{ $student->student_nationality ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Ethnicity:</h5>
            <p>{{ $student->student_ethnicity ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Contact Number:</h5>
            <p>{{ $student->student_contact ?? 'None Provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Email Address:</h5>
            <p>{{ $student->student_email ?? 'None Provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <h5>Current Address:</h5>
            <p>{{ $student->student_current_address ? ucwords(strtolower($student->student_current_address)) : 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- SEA Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1.1rem; color: #111;">
        <i class="fas fa-graduation-cap me-2"></i>SEA Information
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <h5>SEA Exam Date:</h5>
            <p>{{ $student->formatted_sea_date }}</p>
        </div>
        <div class="col-md-3">
            <h5>Primary School:</h5>
            <p>{{ $student->student_primary_school ? ucwords(strtolower($student->student_primary_school)) : 'N/A' }}</p>
        </div>
        <div class="col-md-3">
            <h5>SEA Number:</h5>
            <p>{{ $student->student_sea_number ?? 'N/A' }}</p>
        </div>
        <div class="col-md-3">
            <h5>SEA Slip:</h5>
            <p>{{ $student->student_sea_slip ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- Transfer Information -->
@if($student->student_transfer_status || $student->student_transfer_slip || $student->student_transfer_reason || $student->student_transfer_date || $student->student_previous_form_class || $student->student_previous_secondary_school || $student->student_previous_school_location)
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1.1rem; color: #111;">
        <i class="fas fa-exchange-alt me-2"></i>Transfer Information
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <h5>Transfer Status:</h5>
            <p>{{ $student->student_transfer_status ?? 'N/A' }}</p>
        </div>
        <div class="col-md-3">
            <h5>Transfer Slip:</h5>
            <p>{{ $student->student_transfer_slip ?? 'N/A' }}</p>
        </div>
        <div class="col-md-3">
            <h5>Transfer Date:</h5>
            <p>{{ $student->student_transfer_date ? $student->student_transfer_date->format('d/m/Y') : 'N/A' }}</p>
        </div>
        <div class="col-md-3">
            <h5>Previous Form Class:</h5>
            <p>{{ $student->student_previous_form_class ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-6">
            <h5>Previous Secondary School:</h5>
            <p>{{ $student->student_previous_secondary_school ? ucwords(strtolower($student->student_previous_secondary_school)) : 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <h5>Previous School Location:</h5>
            <p>{{ $student->student_previous_school_location ? ucwords(strtolower($student->student_previous_school_location)) : 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <h5>Transfer Reason:</h5>
            <p>{{ $student->student_transfer_reason ?? 'N/A' }}</p>
        </div>
    </div>
</div>
@endif

<!-- Medical Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1.1rem; color: #111;">
        <i class="fas fa-heartbeat me-2"></i>Medical Information
    </div>
    <div class="row g-3">
        <div class="col-md-3">
            <h5>Blood Type:</h5>
            <p>{{ $student->student_bloodtype ?? 'N/A' }}</p>
        </div>
        <div class="col-md-3">
            <h5>Immunization Status:</h5>
            <p>{{ $student->student_immunization_status ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <h5>Allergies:</h5>
            <p>{{ $student->student_allergies ?? 'No record provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <h5>Medical Conditions:</h5>
            <p>{{ $student->student_medical_condition ?? 'No record provided' }}</p>
        </div>
    </div>
</div>

<!-- Special Needs & Intervention -->
@if($student->student_family_crisis || $student->student_receiving_counselling || $student->student_physical_disabilities || $student->student_learning_disabilities || $student->student_educational_aid || $student->student_special_sea_concessions || $student->student_emotional_factors || $student->student_other_intervention_information)
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1.1rem; color: #111;">
        <i class="fas fa-hands-helping me-2"></i>Special Needs & Intervention
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Family Crisis:</h5>
            <p>{{ $student->student_family_crisis ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Receiving Counselling:</h5>
            <p>{{ $student->student_receiving_counselling ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Educational Aid:</h5>
            <p>{{ $student->student_educational_aid ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Physical Disabilities:</h5>
            <p>{{ $student->student_physical_disabilities ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Learning Disabilities:</h5>
            <p>{{ $student->student_learning_disabilities ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Special SEA Concessions:</h5>
            <p>{{ $student->student_special_sea_concessions ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-6">
            <h5>Emotional Factors:</h5>
            <p>{{ $student->student_emotional_factors ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <h5>Other Intervention Information:</h5>
            <p>{{ $student->student_other_intervention_information ?? 'N/A' }}</p>
        </div>
    </div>
</div>
@endif

<!-- Personal Preferences -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1.1rem; color: #111;">
        <i class="fas fa-cog me-2"></i>Personal Preferences
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>School Feeding Option:</h5>
            <p>{{ $student->student_school_feeding_option ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Social Welfare Status:</h5>
            <p>{{ $student->student_social_welfare_status ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Mode of Transport:</h5>
            <p>{{ $student->student_mode_of_transport ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <h5>Social Welfare Details:</h5>
            <p>{{ $student->student_social_welfare_detail ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Access to Device:</h5>
            <p>{{ $student->student_access_to_device ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Device Shared:</h5>
            <p>{{ $student->student_device_shared ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Reliable Internet:</h5>
            <p>{{ $student->student_reliable_internet ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-6">
            <h5>Internet Provider:</h5>
            <p>{{ $student->student_internet_provider ?? 'N/A' }}</p>
        </div>
        <div class="col-md-6">
            <h5>Online Tools:</h5>
            <p>{{ $student->student_online_tools ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- Mother Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1.1rem; color: #111;">
        <i class="fas fa-female me-2"></i>Parent/Guardian Information (Mother)
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Mother's Name:</h5>
            <p>{{ $student->mother_name ? ucwords(strtolower($student->mother_name)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Status:</h5>
            <p>{{ $student->is_mother_active_or_deceased ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Death Certificate:</h5>
            <p>{{ $student->mother_death_certificate ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Identification Type:</h5>
            <p>{{ $student->mother_identification_type ?? 'No record provided' }}</p>
        </div>
        <div class="col-md-8">
            <h5>Identification Number:</h5>
            <p>{{ $student->mother_identification_number ?? 'No record provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <h5>Home Address:</h5>
            <p>{{ $student->mother_home_address ? ucwords(strtolower($student->mother_home_address)) : 'No record provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Contact Number:</h5>
            <p>{{ $student->mother_contact ?? 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Profession:</h5>
            <p>{{ $student->mother_profession ? ucwords(strtolower($student->mother_profession)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Email Address:</h5>
            <p>{{ $student->mother_email ?? 'No record provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <h5>Work Address:</h5>
            <p>{{ $student->mother_work_address ? ucwords(strtolower($student->mother_work_address)) : 'No record provided' }}</p>
        </div>
    </div>
</div>

<!-- Father Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1.1rem; color: #111;">
        <i class="fas fa-male me-2"></i>Parent/Guardian Information (Father)
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Father's Name:</h5>
            <p>{{ $student->father_name ? ucwords(strtolower($student->father_name)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Status:</h5>
            <p>{{ $student->is_father_active_or_deceased ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Death Certificate:</h5>
            <p>{{ $student->father_death_certificate ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Identification Type:</h5>
            <p>{{ $student->father_identification_type ?? 'No record provided' }}</p>
        </div>
        <div class="col-md-8">
            <h5>Identification Number:</h5>
            <p>{{ $student->father_identification_number ?? 'No record provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <h5>Home Address:</h5>
            <p>{{ $student->father_home_address ? ucwords(strtolower($student->father_home_address)) : 'No record provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Contact Number:</h5>
            <p>{{ $student->father_contact ?? 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Profession:</h5>
            <p>{{ $student->father_profession ? ucwords(strtolower($student->father_profession)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Email Address:</h5>
            <p>{{ $student->father_email_address ?? 'No record provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <h5>Work Address:</h5>
            <p>{{ $student->father_work_address ? ucwords(strtolower($student->father_work_address)) : 'No record provided' }}</p>
        </div>
    </div>
</div>

<!-- Emergency Contact -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1.1rem; color: #111;">
        <i class="fas fa-phone-alt me-2"></i>Emergency Contact Information
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Contact Name:</h5>
            <p>{{ $student->emergency_contact_name ? ucwords(strtolower($student->emergency_contact_name)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Relation to Student:</h5>
            <p>{{ $student->emergency_contact_relation_to_student ? ucwords(strtolower($student->emergency_contact_relation_to_student)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Contact Number:</h5>
            <p>{{ $student->emergency_contact_number ?? 'No record provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <h5>Address:</h5>
            <p>{{ $student->emergency_contact_address ? ucwords(strtolower($student->emergency_contact_address)) : 'No record provided' }}</p>
        </div>
    </div>
</div>

<!-- Registrant Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1.1rem; color: #111;">
        <i class="fas fa-user-edit me-2"></i>Registrant Information
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Registration Date:</h5>
            <p>{{ $student->formatted_registration_date }}</p>
        </div>
        <div class="col-md-4">
            <h5>Relationship to Student:</h5>
            <p>{{ $student->registrant_relationship_to_student ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Registrant Name:</h5>
            <p>{{ $student->registrant_name ? ucwords(strtolower($student->registrant_name)) : 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Identification Type:</h5>
            <p>{{ $student->registrant_identification_type ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Identification Number:</h5>
            <p>{{ $student->registrant_identification_number ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Nationality:</h5>
            <p>{{ $student->registrant_nationality ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-12">
            <h5>Email Address:</h5>
            <p>{{ $student->registrant_email ?? 'No record provided' }}</p>
        </div>
    </div>
</div>
