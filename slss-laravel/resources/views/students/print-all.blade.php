<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profiles ({{ $students->count() }}) - {{ $filterSummary }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    @include('students.partials.print-styles')
    <style>
        /* One profile per printed page */
        .profile-card {
            page-break-after: always;
            break-after: page;
        }
        .profile-card:last-of-type {
            page-break-after: auto;
            break-after: auto;
        }

        .print-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #fff;
            border-bottom: 1px solid #d1d5db;
            padding: 0.75rem 1rem;
            margin: -2rem -1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="print-toolbar no-print">
        <div>
            <strong><i class="fas fa-print me-2"></i>{{ $students->count() }} {{ $students->count() === 1 ? 'student profile' : 'student profiles' }}</strong>
            <span class="text-muted ms-2">{{ $filterSummary }}</span>
            <span class="text-muted ms-2 small">&middot; Formatted for Legal (8.5 &times; 14 in) paper, one student per sheet</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('students.index', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to list
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
                🖨️ Print all
            </button>
        </div>
    </div>

    @forelse($students as $student)
        @include('students.partials.print-profile', ['student' => $student])
    @empty
        <div class="alert alert-warning">No students match the current filters.</div>
    @endforelse

    <script>
        // Open the browser's print dialog once the page (photos included) has loaded
        window.addEventListener('load', function () {
            if ({{ $students->count() }} > 0) {
                window.print();
            }
        });
    </script>
</body>
</html>
