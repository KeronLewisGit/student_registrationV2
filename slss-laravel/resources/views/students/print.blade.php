<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print - {{ $student->student_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            size: Letter;
            margin: 10mm;
        }

        body {
            background: white;
            padding: 2rem;
        }

        .profile-card {
            position: relative;
            padding: 2rem;
            min-height: 1000px;
        }

        .profile-card::after {
            content: "";
            position: absolute;
            inset: 80px;
            background: url('{{ asset('images/OfficialDocument1.png') }}') center/contain no-repeat;
            opacity: 0.08;
            pointer-events: none;
            z-index: 0;
        }

        .profile-inner {
            position: relative;
            z-index: 1;
        }

        .passport-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
        }

        .school-logo {
            width: 160px;
            height: auto;
        }

        .section-card {
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.25rem;
            margin-top: 1.25rem;
            page-break-inside: avoid;
        }

        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .profile-card {
                padding-top: 140px !important;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="profile-card">
        <div class="profile-inner">
            <div class="row align-items-start mb-4">
                <div class="col-md-3">
                    <h6 class="fw-bold mb-2">Passport Size Photo</h6>
                    @if($student->student_passport_photo)
                        <img src="{{ asset($student->student_passport_photo) }}" alt="Passport" class="passport-photo">
                    @else
                        <img src="{{ asset('images/noimage.jpg') }}" alt="No Image" class="passport-photo">
                    @endif
                </div>
                <div class="col-md-6 text-center">
                    <h2 class="fw-bold mb-2" style="font-size: 2rem;">
                        Success Laventille Secondary School<br>Eastern Main Road
                    </h2>
                    <p class="text-muted">Official Student Record</p>
                </div>
                <div class="col-md-3 text-end">
                    <img src="{{ asset('images/successlogo.png') }}" alt="SLSS Logo" class="school-logo">
                </div>
            </div>

            @include('students.partials.profile-sections', ['student' => $student])
        </div>
    </div>
</body>
</html>
