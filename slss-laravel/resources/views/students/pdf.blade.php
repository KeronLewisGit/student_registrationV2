<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Profile - {{ $student->student_name }}</title>
    <style>
        @page {
            size: Letter;
            margin: 10mm;
        }
        * {
            font-family: "DejaVu Sans", sans-serif;
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 20px;
            font-size: 10px;
            color: #1f2937;
            position: relative;
        }

        /* OFFICIAL DOCUMENT Watermark */
        .watermark-text {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            font-weight: 900;
            color: rgba(79, 70, 229, 0.06);
            text-transform: uppercase;
            letter-spacing: 8px;
            white-space: nowrap;
            z-index: -1;
            opacity: 1;
        }

        .logo-watermark {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.03;
            width: 400px;
            z-index: -2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #4f46e5;
        }
        .header h1 {
            font-size: 22px;
            margin: 0 0 5px 0;
            color: #1f2937;
            font-weight: bold;
        }
        .header p {
            margin: 0;
            color: #6b7280;
            font-size: 11px;
        }
        .passport-container {
            float: right;
            width: 110px;
            margin-left: 15px;
            margin-bottom: 10px;
        }
        .passport {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border: 3px solid #4f46e5;
            border-radius: 8px;
        }
        .section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-left: 4px solid #4f46e5;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
            clear: both;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #111;
            margin: 0 0 8px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #4f46e5;
        }
        .row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .col {
            display: table-cell;
            width: 33.33%;
            padding-right: 8px;
            vertical-align: top;
            padding-bottom: 4px;
        }
        .col-full {
            display: table-cell;
            width: 100%;
            padding-bottom: 4px;
        }
        .field-label {
            font-weight: bold;
            font-size: 8px;
            color: #64748b;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .field-value {
            font-size: 9px;
            color: #1f2937;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Official Document Watermark -->
    <div class="watermark-text">OFFICIAL DOCUMENT</div>

    @if(file_exists(public_path('images/OfficialDocument1.png')))
        <img src="{{ public_path('images/OfficialDocument1.png') }}" class="logo-watermark">
    @endif

    <div class="header">
        <h1>Success Laventille Secondary School</h1>
        <p>Eastern Main Road, Laventille - Official Student Record</p>
    </div>

    @if($student->student_passport_photo && file_exists(public_path($student->student_passport_photo)))
        <div class="passport-container">
            <img src="{{ public_path($student->student_passport_photo) }}" class="passport">
        </div>
    @endif

    <!-- Student Personal Information -->
    <div class="section">
        <div class="section-title">Student Personal Information</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Form Class</div>
                <div class="field-value">{{ $student->form_1_class ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Student Name</div>
                <div class="field-value">{{ $student->student_name ? ucwords(strtolower($student->student_name)) : 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Gender</div>
                <div class="field-value">{{ $student->student_gender ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Date of Birth</div>
                <div class="field-value">{{ $student->formatted_dob }}</div>
            </div>
            <div class="col">
                <div class="field-label">Citizenship Type</div>
                <div class="field-value">{{ $student->citizen_type ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Birth Certificate Pin</div>
                <div class="field-value">{{ $student->student_birth_certificate_pin ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Birth Certificate</div>
                <div class="field-value">{{ $student->student_birth_certificate ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Religion</div>
                <div class="field-value">{{ $student->student_religion ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Country of Birth</div>
                <div class="field-value">{{ $student->student_country_of_birth ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Nationality</div>
                <div class="field-value">{{ $student->student_nationality ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Ethnicity</div>
                <div class="field-value">{{ $student->student_ethnicity ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Contact Number</div>
                <div class="field-value">{{ $student->student_contact ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Email Address</div>
                <div class="field-value">{{ $student->student_email ?? 'N/A' }}</div>
            </div>
            <div class="col" style="width: 66.66%;">
                <div class="field-label">Current Address</div>
                <div class="field-value">{{ $student->student_current_address ? ucwords(strtolower($student->student_current_address)) : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- SEA Information -->
    <div class="section">
        <div class="section-title">SEA Information</div>
        <div class="row">
            <div class="col">
                <div class="field-label">SEA Exam Date</div>
                <div class="field-value">{{ $student->formatted_sea_date }}</div>
            </div>
            <div class="col">
                <div class="field-label">Primary School</div>
                <div class="field-value">{{ $student->student_primary_school ? ucwords(strtolower($student->student_primary_school)) : 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">SEA Number</div>
                <div class="field-value">{{ $student->student_sea_number ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">SEA Slip</div>
                <div class="field-value">{{ $student->student_sea_slip ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Transfer Information -->
    @if($student->student_transfer_status || $student->student_transfer_slip || $student->student_transfer_reason || $student->student_transfer_date || $student->student_previous_form_class || $student->student_previous_secondary_school || $student->student_previous_school_location)
    <div class="section">
        <div class="section-title">Transfer Information</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Transfer Status</div>
                <div class="field-value">{{ $student->student_transfer_status ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Transfer Slip</div>
                <div class="field-value">{{ $student->student_transfer_slip ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Transfer Date</div>
                <div class="field-value">{{ $student->student_transfer_date ? $student->student_transfer_date->format('d/m/Y') : 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Previous Form Class</div>
                <div class="field-value">{{ $student->student_previous_form_class ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Previous Secondary School</div>
                <div class="field-value">{{ $student->student_previous_secondary_school ? ucwords(strtolower($student->student_previous_secondary_school)) : 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Previous School Location</div>
                <div class="field-value">{{ $student->student_previous_school_location ? ucwords(strtolower($student->student_previous_school_location)) : 'N/A' }}</div>
            </div>
        </div>
        @if($student->student_transfer_reason)
        <div class="row">
            <div class="col-full">
                <div class="field-label">Transfer Reason</div>
                <div class="field-value">{{ $student->student_transfer_reason }}</div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Medical Information -->
    <div class="section">
        <div class="section-title">Medical Information</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Blood Type</div>
                <div class="field-value">{{ $student->student_bloodtype ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Immunization Status</div>
                <div class="field-value">{{ $student->student_immunization_status ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Allergies</div>
                <div class="field-value">{{ $student->student_allergies ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-full">
                <div class="field-label">Medical Conditions</div>
                <div class="field-value">{{ $student->student_medical_condition ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Special Needs & Intervention -->
    @if($student->student_family_crisis || $student->student_receiving_counselling || $student->student_physical_disabilities || $student->student_learning_disabilities || $student->student_educational_aid || $student->student_special_sea_concessions || $student->student_emotional_factors || $student->student_other_intervention_information)
    <div class="section">
        <div class="section-title">Special Needs & Intervention</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Family Crisis</div>
                <div class="field-value">{{ $student->student_family_crisis ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Receiving Counselling</div>
                <div class="field-value">{{ $student->student_receiving_counselling ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Educational Aid</div>
                <div class="field-value">{{ $student->student_educational_aid ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Physical Disabilities</div>
                <div class="field-value">{{ $student->student_physical_disabilities ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Learning Disabilities</div>
                <div class="field-value">{{ $student->student_learning_disabilities ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Special SEA Concessions</div>
                <div class="field-value">{{ $student->student_special_sea_concessions ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Emotional Factors</div>
                <div class="field-value">{{ $student->student_emotional_factors ?? 'N/A' }}</div>
            </div>
            <div class="col" style="width: 66.66%;">
                <div class="field-label">Other Intervention Information</div>
                <div class="field-value">{{ $student->student_other_intervention_information ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Personal Preferences -->
    <div class="section">
        <div class="section-title">Personal Preferences</div>
        <div class="row">
            <div class="col">
                <div class="field-label">School Feeding Option</div>
                <div class="field-value">{{ $student->student_school_feeding_option ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Social Welfare Status</div>
                <div class="field-value">{{ $student->student_social_welfare_status ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Mode of Transport</div>
                <div class="field-value">{{ $student->student_mode_of_transport ?? 'N/A' }}</div>
            </div>
        </div>
        @if($student->student_social_welfare_detail)
        <div class="row">
            <div class="col-full">
                <div class="field-label">Social Welfare Details</div>
                <div class="field-value">{{ $student->student_social_welfare_detail }}</div>
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col">
                <div class="field-label">Access to Device</div>
                <div class="field-value">{{ $student->student_access_to_device ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Device Shared</div>
                <div class="field-value">{{ $student->student_device_shared ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Reliable Internet</div>
                <div class="field-value">{{ $student->student_reliable_internet ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Internet Provider</div>
                <div class="field-value">{{ $student->student_internet_provider ?? 'N/A' }}</div>
            </div>
            <div class="col" style="width: 66.66%;">
                <div class="field-label">Online Tools</div>
                <div class="field-value">{{ $student->student_online_tools ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Mother Information -->
    <div class="section">
        <div class="section-title">Parent/Guardian Information (Mother)</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Mother's Name</div>
                <div class="field-value">{{ $student->mother_name ? ucwords(strtolower($student->mother_name)) : 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Status</div>
                <div class="field-value">{{ $student->is_mother_active_or_deceased ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Death Certificate</div>
                <div class="field-value">{{ $student->mother_death_certificate ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Identification Type</div>
                <div class="field-value">{{ $student->mother_identification_type ?? 'N/A' }}</div>
            </div>
            <div class="col" style="width: 66.66%;">
                <div class="field-label">Identification Number</div>
                <div class="field-value">{{ $student->mother_identification_number ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-full">
                <div class="field-label">Home Address</div>
                <div class="field-value">{{ $student->mother_home_address ? ucwords(strtolower($student->mother_home_address)) : 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Contact Number</div>
                <div class="field-value">{{ $student->mother_contact ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Profession</div>
                <div class="field-value">{{ $student->mother_profession ? ucwords(strtolower($student->mother_profession)) : 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Email Address</div>
                <div class="field-value">{{ $student->mother_email ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-full">
                <div class="field-label">Work Address</div>
                <div class="field-value">{{ $student->mother_work_address ? ucwords(strtolower($student->mother_work_address)) : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Father Information -->
    <div class="section">
        <div class="section-title">Parent/Guardian Information (Father)</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Father's Name</div>
                <div class="field-value">{{ $student->father_name ? ucwords(strtolower($student->father_name)) : 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Status</div>
                <div class="field-value">{{ $student->is_father_active_or_deceased ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Death Certificate</div>
                <div class="field-value">{{ $student->father_death_certificate ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Identification Type</div>
                <div class="field-value">{{ $student->father_identification_type ?? 'N/A' }}</div>
            </div>
            <div class="col" style="width: 66.66%;">
                <div class="field-label">Identification Number</div>
                <div class="field-value">{{ $student->father_identification_number ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-full">
                <div class="field-label">Home Address</div>
                <div class="field-value">{{ $student->father_home_address ? ucwords(strtolower($student->father_home_address)) : 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Contact Number</div>
                <div class="field-value">{{ $student->father_contact ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Profession</div>
                <div class="field-value">{{ $student->father_profession ? ucwords(strtolower($student->father_profession)) : 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Email Address</div>
                <div class="field-value">{{ $student->father_email_address ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-full">
                <div class="field-label">Work Address</div>
                <div class="field-value">{{ $student->father_work_address ? ucwords(strtolower($student->father_work_address)) : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Emergency Contact -->
    <div class="section">
        <div class="section-title">Emergency Contact Information</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Contact Name</div>
                <div class="field-value">{{ $student->emergency_contact_name ? ucwords(strtolower($student->emergency_contact_name)) : 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Relation to Student</div>
                <div class="field-value">{{ $student->emergency_contact_relation_to_student ? ucwords(strtolower($student->emergency_contact_relation_to_student)) : 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Contact Number</div>
                <div class="field-value">{{ $student->emergency_contact_number ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-full">
                <div class="field-label">Address</div>
                <div class="field-value">{{ $student->emergency_contact_address ? ucwords(strtolower($student->emergency_contact_address)) : 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Registrant Information -->
    <div class="section">
        <div class="section-title">Registrant Information</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Registration Date</div>
                <div class="field-value">{{ $student->formatted_registration_date }}</div>
            </div>
            <div class="col">
                <div class="field-label">Relationship to Student</div>
                <div class="field-value">{{ $student->registrant_relationship_to_student ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Registrant Name</div>
                <div class="field-value">{{ $student->registrant_name ? ucwords(strtolower($student->registrant_name)) : 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Identification Type</div>
                <div class="field-value">{{ $student->registrant_identification_type ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Identification Number</div>
                <div class="field-value">{{ $student->registrant_identification_number ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Nationality</div>
                <div class="field-value">{{ $student->registrant_nationality ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col-full">
                <div class="field-label">Email Address</div>
                <div class="field-value">{{ $student->registrant_email ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
