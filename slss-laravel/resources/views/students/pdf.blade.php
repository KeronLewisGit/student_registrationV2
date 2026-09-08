<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Profile - {{ $student->student_name }}</title>
    {{-- Same stylesheet and same profile partial as the browser print view --}}
    @include('students.partials.print-styles')
</head>
<body class="pdf">
    @include('students.partials.print-profile', ['student' => $student, 'forPdf' => true])
</body>
</html>
