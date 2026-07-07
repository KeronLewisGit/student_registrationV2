<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Profiles - Bulk Export</title>
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
            padding: 0;
            font-size: 10px;
            color: #1f2937;
        }
        .page-break {
            page-break-after: always;
        }
        .student-profile {
            padding: 20px;
            position: relative;
        }
        .watermark {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.06;
            width: 350px;
            z-index: -1;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0b5fff;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 3px 0;
            color: #1f2937;
        }
        .header p {
            margin: 0;
            color: #6b7280;
            font-size: 11px;
        }
        .section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #111;
            margin: 0 0 8px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
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
        }
        .field-label {
            font-weight: bold;
            font-size: 9px;
            color: #374151;
            margin-bottom: 2px;
        }
        .field-value {
            font-size: 9px;
            color: #1f2937;
        }
    </style>
</head>
<body>
    @foreach($students as $index => $student)
        <div class="student-profile {{ $index < count($students) - 1 ? 'page-break' : '' }}">
            @if(file_exists(public_path('images/OfficialDocument1.png')))
                <img src="{{ public_path('images/OfficialDocument1.png') }}" class="watermark">
            @endif

            <div class="header">
                <h1>Success Laventille Secondary School</h1>
                <p>Eastern Main Road, Laventille - Official Student Record</p>
                <p style="font-weight: bold; margin-top: 5px;">{{ $student->student_name }}</p>
            </div>

            <div class="section">
                <div class="section-title">Student Information</div>
                <div class="row">
                    <div class="col">
                        <div class="field-label">Form Class:</div>
                        <div class="field-value">{{ $student->form_1_class ?? 'N/A' }}</div>
                    </div>
                    <div class="col">
                        <div class="field-label">Gender:</div>
                        <div class="field-value">{{ $student->student_gender ?? 'N/A' }}</div>
                    </div>
                    <div class="col">
                        <div class="field-label">Date of Birth:</div>
                        <div class="field-value">{{ $student->formatted_dob }}</div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">Contact Information</div>
                <div class="row">
                    <div class="col">
                        <div class="field-label">Student Contact:</div>
                        <div class="field-value">{{ $student->student_contact ?? 'N/A' }}</div>
                    </div>
                    <div class="col">
                        <div class="field-label">Mother Contact:</div>
                        <div class="field-value">{{ $student->mother_contact ?? 'N/A' }}</div>
                    </div>
                    <div class="col">
                        <div class="field-label">Father Contact:</div>
                        <div class="field-value">{{ $student->father_contact ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">SEA Information</div>
                <div class="row">
                    <div class="col">
                        <div class="field-label">SEA Date:</div>
                        <div class="field-value">{{ $student->formatted_sea_date }}</div>
                    </div>
                    <div class="col">
                        <div class="field-label">Primary School:</div>
                        <div class="field-value">{{ $student->student_primary_school ?? 'N/A' }}</div>
                    </div>
                    <div class="col">
                        <div class="field-label">SEA Number:</div>
                        <div class="field-value">{{ $student->student_sea_number ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
