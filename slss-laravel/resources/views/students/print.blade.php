<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - {{ $student->student_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        @page {
            size: Letter;
            margin: 10mm;
        }

        body {
            background: white;
            padding: 2rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            opacity: 0.05;
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
            border: 3px solid #4f46e5;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .school-logo {
            width: 160px;
            height: auto;
        }

        .section-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid #e5e7eb;
            border-left: 4px solid #4f46e5;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            page-break-inside: avoid;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .section-card h5 {
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-card p {
            font-size: 1rem;
            font-weight: 500;
            color: #1e293b;
            margin: 0;
            padding: 0.5rem 0;
        }

        .fw-bold.border-bottom {
            border-color: #4f46e5 !important;
            border-width: 2px !important;
            padding-bottom: 0.75rem !important;
        }

        .fw-bold i {
            color: #4f46e5;
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
            .section-card {
                box-shadow: none;
                border-left-width: 3px;
            }
        }
    </style>
</head>
<body>
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
