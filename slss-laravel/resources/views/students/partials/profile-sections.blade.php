<!-- Student Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1rem; color: #111;">Student Information</div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Form Class:</h5>
            <p>{{ $student->form_1_class ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Student Name:</h5>
            <p>{{ $student->student_name ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Gender:</h5>
            <p>{{ $student->student_gender ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Student Address:</h5>
            <p>{{ $student->student_current_address ? ucwords(strtolower($student->student_current_address)) : 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Date of Birth:</h5>
            <p>{{ $student->formatted_dob }}</p>
        </div>
        <div class="col-md-4">
            <h5>Birth Certificate Pin:</h5>
            <p>{{ $student->student_birth_certificate_pin ?? 'N/A' }}</p>
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
            <h5>Student Ethnicity:</h5>
            <p>{{ $student->student_ethnicity ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Student Contact:</h5>
            <p>{{ $student->student_contact ?? 'None Provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Student Email:</h5>
            <p>{{ $student->student_email ?? 'None Provided' }}</p>
        </div>
    </div>
</div>

<!-- SEA Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1rem; color: #111;">SEA Information</div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>SEA Exam Date:</h5>
            <p>{{ $student->formatted_sea_date }}</p>
        </div>
        <div class="col-md-4">
            <h5>Primary School:</h5>
            <p>{{ $student->student_primary_school ? ucwords(strtolower($student->student_primary_school)) : 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>S.E.A Number:</h5>
            <p>{{ $student->student_sea_number ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- Medical Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1rem; color: #111;">Medical Information</div>
    <div class="row g-3">
        <div class="col-md-6">
            <h5>Medical Complications:</h5>
            <p>{{ $student->student_medical_condition ?? 'No record provided' }}</p>
        </div>
        <div class="col-md-3">
            <h5>Blood Group:</h5>
            <p>{{ $student->student_bloodtype ?? 'N/A' }}</p>
        </div>
        <div class="col-md-3">
            <h5>Allergies:</h5>
            <p>{{ $student->student_allergies ?? 'No record provided' }}</p>
        </div>
    </div>
</div>

<!-- Personal Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1rem; color: #111;">Personal Information</div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Boxlunch Preference:</h5>
            <p>{{ $student->student_school_feeding_option ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Social Welfare:</h5>
            <p>{{ $student->student_social_welfare_status ?? 'N/A' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Mode of Transport:</h5>
            <p>{{ $student->student_mode_of_transport ?? 'N/A' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Immunized:</h5>
            <p>{{ $student->student_immunization_status ?? 'N/A' }}</p>
        </div>
        <div class="col-md-8">
            <h5>Continuous Access to Device:</h5>
            <p>{{ $student->student_access_to_device ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<!-- Mother Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1rem; color: #111;">Parent/Guardian Information (Mother)</div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Mother's Name:</h5>
            <p>{{ $student->mother_name ? ucwords(strtolower($student->mother_name)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Status:</h5>
            <p>{{ $student->is_mother_active_or_deceased === 'Deceased' ? 'Deceased' : 'Alive' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Identification:</h5>
            <p>
                @if($student->mother_identification_number)
                    ({{ $student->mother_identification_type }})<br>{{ $student->mother_identification_number }}
                @else
                    No record provided
                @endif
            </p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Home Address:</h5>
            <p>{{ $student->mother_home_address ? ucwords(strtolower($student->mother_home_address)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Contact:</h5>
            <p>{{ $student->mother_contact ?? 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Profession:</h5>
            <p>{{ $student->mother_profession ? ucwords(strtolower($student->mother_profession)) : 'No record provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Work Address:</h5>
            <p>{{ $student->mother_work_address ? ucwords(strtolower($student->mother_work_address)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-8">
            <h5>Email Address:</h5>
            <p>{{ $student->mother_email ?? 'No record provided' }}</p>
        </div>
    </div>
</div>

<!-- Father Information -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1rem; color: #111;">Parent/Guardian Information (Father)</div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Father's Name:</h5>
            <p>{{ $student->father_name ? ucwords(strtolower($student->father_name)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Status:</h5>
            <p>{{ $student->is_father_active_or_deceased === 'Deceased' ? 'Deceased' : 'Alive' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Identification:</h5>
            <p>
                @if($student->father_identification_number)
                    ({{ $student->father_identification_type }})<br>{{ $student->father_identification_number }}
                @else
                    No record provided
                @endif
            </p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Home Address:</h5>
            <p>{{ $student->father_home_address ? ucwords(strtolower($student->father_home_address)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Contact:</h5>
            <p>{{ $student->father_contact ?? 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Profession:</h5>
            <p>{{ $student->father_profession ? ucwords(strtolower($student->father_profession)) : 'No record provided' }}</p>
        </div>
    </div>
    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <h5>Work Address:</h5>
            <p>{{ $student->father_work_address ? ucwords(strtolower($student->father_work_address)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-8">
            <h5>Email Address:</h5>
            <p>{{ $student->father_email_address ?? 'No record provided' }}</p>
        </div>
    </div>
</div>

<!-- Emergency Contact -->
<div class="section-card">
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1rem; color: #111;">Emergency Contact Information</div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Contact Name:</h5>
            <p>{{ $student->emergency_contact_name ? ucwords(strtolower($student->emergency_contact_name)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Relation:</h5>
            <p>{{ $student->emergency_contact_relation_to_student ? ucwords(strtolower($student->emergency_contact_relation_to_student)) : 'No record provided' }}</p>
        </div>
        <div class="col-md-4">
            <h5>Contact No.:</h5>
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
    <div class="fw-bold mb-3 pb-2 border-bottom" style="font-size: 1rem; color: #111;">Registrant Information</div>
    <div class="row g-3">
        <div class="col-md-4">
            <h5>Date of Registration:</h5>
            <p>{{ $student->formatted_registration_date }}</p>
        </div>
        <div class="col-md-4">
            <h5>Registrant {{ $student->registrant_relationship_to_student ? '(' . $student->registrant_relationship_to_student . ')' : '' }}:</h5>
            <p>{{ $student->registrant_display_name }}</p>
        </div>
        <div class="col-md-4">
            <h5>Identification:</h5>
            <p>
                @php $regId = $student->registrant_display_id; @endphp
                @if($regId['number'])
                    ({{ $regId['type'] }})<br>{{ $regId['number'] }}
                @else
                    No record provided
                @endif
            </p>
        </div>
    </div>
</div>
