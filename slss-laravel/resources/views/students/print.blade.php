<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - {{ $student->student_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    @include('students.partials.print-styles')
</head>
<body>
    @include('students.partials.print-profile', ['student' => $student])

    <div class="no-print" style="position: fixed; bottom: 1.5rem; right: 1.5rem;">
        <button type="button" onclick="window.print()" class="btn btn-primary" style="padding: 0.75rem 1.5rem; font-size: 1rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.25);">
            🖨️ Print
        </button>
    </div>

    <script>
        // Open the browser's print dialog once the page (photos included) has loaded
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
