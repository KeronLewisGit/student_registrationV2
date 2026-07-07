<?php

namespace App\Services;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PdfService
{
    public function generateStudentPdf(Student $student)
    {
        $pdf = PDF::loadView('students.pdf', compact('student'));
        $pdf->setPaper('letter', 'portrait');

        $filename = 'profile_' . str_replace(' ', '_', strtolower($student->student_name)) . '.pdf';

        return $pdf->download($filename);
    }

    public function generateBulkPdf(Collection $students)
    {
        $pdf = PDF::loadView('students.bulk-pdf', compact('students'));
        $pdf->setPaper('letter', 'portrait');

        $filename = 'profiles_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function streamPdf(Student $student)
    {
        $pdf = PDF::loadView('students.pdf', compact('student'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream();
    }
}
