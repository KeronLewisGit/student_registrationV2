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
            font-size: 11px;
            color: #1f2937;
        }
        .watermark {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.06;
            width: 400px;
            z-index: -1;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #0b5fff;
        }
        .header h1 {
            font-size: 24px;
            margin: 0 0 5px 0;
            color: #1f2937;
        }
        .header p {
            margin: 0;
            color: #6b7280;
            font-size: 12px;
        }
        .passport-container {
            float: right;
            width: 120px;
            margin-left: 20px;
        }
        .passport {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
        }
        .section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #111;
            margin: 0 0 10px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .col {
            display: table-cell;
            width: 33.33%;
            padding-right: 10px;
            vertical-align: top;
        }
        .field-label {
            font-weight: bold;
            font-size: 10px;
            color: #374151;
            margin-bottom: 3px;
        }
        .field-value {
            font-size: 10px;
            color: #1f2937;
        }
    </style>
</head>
<body>
    @if(file_exists(public_path('images/OfficialDocument1.png')))
        <img src="{{ public_path('images/OfficialDocument1.png') }}" class="watermark">
    @endif

    <div class="header">
        <h1>Success Laventille Secondary School</h1>
        <p>Eastern Main Road, Laventille - Official Student Record</p>
    </div>

    @if($student->student_passport_photo)
        <div class="passport-container">
            <img src="{{ public_path($student->student_passport_photo) }}" class="passport">
        </div>
    @endif

    <div class="section">
        <div class="section-title">Student Information</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Form Class:</div>
                <div class="field-value">{{ $student->form_1_class ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Student Name:</div>
                <div class="field-value">{{ $student->student_name }}</div>
            </div>
            <div class="col">
                <div class="field-label">Gender:</div>
                <div class="field-value">{{ $student->student_gender ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="field-label">Date of Birth:</div>
                <div class="field-value">{{ $student->formatted_dob }}</div>
            </div>
            <div class="col">
                <div class="field-label">Birth Certificate Pin:</div>
                <div class="field-value">{{ $student->student_birth_certificate_pin ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Religion:</div>
                <div class="field-value">{{ $student->student_religion ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">SEA Information</div>
        <div class="row">
            <div class="col">
                <div class="field-label">SEA Exam Date:</div>
                <div class="field-value">{{ $student->formatted_sea_date }}</div>
            </div>
            <div class="col">
                <div class="field-label">Primary School:</div>
                <div class="field-value">{{ $student->student_primary_school ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">S.E.A Number:</div>
                <div class="field-value">{{ $student->student_sea_number ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Medical Information</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Medical Complications:</div>
                <div class="field-value">{{ $student->student_medical_condition ?? 'No record provided' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Blood Group:</div>
                <div class="field-value">{{ $student->student_bloodtype ?? 'N/A' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Allergies:</div>
                <div class="field-value">{{ $student->student_allergies ?? 'No record provided' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Parent/Guardian Information (Mother)</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Mother's Name:</div>
                <div class="field-value">{{ $student->mother_name ?? 'No record provided' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Contact:</div>
                <div class="field-value">{{ $student->mother_contact ?? 'No record provided' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Profession:</div>
                <div class="field-value">{{ $student->mother_profession ?? 'No record provided' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Parent/Guardian Information (Father)</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Father's Name:</div>
                <div class="field-value">{{ $student->father_name ?? 'No record provided' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Contact:</div>
                <div class="field-value">{{ $student->father_contact ?? 'No record provided' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Profession:</div>
                <div class="field-value">{{ $student->father_profession ?? 'No record provided' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Emergency Contact</div>
        <div class="row">
            <div class="col">
                <div class="field-label">Contact Name:</div>
                <div class="field-value">{{ $student->emergency_contact_name ?? 'No record provided' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Relation:</div>
                <div class="field-value">{{ $student->emergency_contact_relation_to_student ?? 'No record provided' }}</div>
            </div>
            <div class="col">
                <div class="field-label">Contact No.:</div>
                <div class="field-value">{{ $student->emergency_contact_number ?? 'No record provided' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
