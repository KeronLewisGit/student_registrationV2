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

    public function generateBulkPdf(Collection $students, bool $returnJson = false)
    {
        // Check if there are any students to export
        if ($students->isEmpty()) {
            if ($returnJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found to export. Please adjust your filters.'
                ], 400);
            }
            return redirect()->back()->with('error', 'No students found to export. Please adjust your filters.');
        }

        // Create temporary directory for PDFs
        $tempDir = storage_path('app/temp_pdfs_' . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        try {
            $total = $students->count();

            // Generate individual PDF for each student (full profile with all 127 fields)
            foreach ($students as $student) {
                $pdf = PDF::loadView('students.pdf', compact('student'));
                $pdf->setPaper('letter', 'portrait');

                $filename = 'profile_' . str_replace(' ', '_', strtolower($student->student_name)) . '.pdf';
                $pdf->save($tempDir . '/' . $filename);
            }

            // Create ZIP archive
            $zipFilename = 'student_profiles_' . now()->format('Y-m-d_His') . '.zip';
            $zipPath = storage_path('app/public/' . $zipFilename);

            // Ensure public storage directory exists
            if (!file_exists(storage_path('app/public'))) {
                mkdir(storage_path('app/public'), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                // Add all PDFs to the zip
                $files = glob($tempDir . '/*.pdf');
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();
            }

            // Clean up temporary PDFs
            array_map('unlink', glob($tempDir . '/*.pdf'));
            rmdir($tempDir);

            // Return JSON response for AJAX or download directly
            if ($returnJson) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully exported {$total} student profiles!",
                    'download_url' => url('storage/' . $zipFilename),
                    'filename' => $zipFilename,
                    'total' => $total
                ]);
            }

            // Direct download
            return response()->download($zipPath, $zipFilename);

        } catch (\Exception $e) {
            // Clean up on error
            if (file_exists($tempDir)) {
                $files = glob($tempDir . '/*.pdf');
                if ($files) {
                    array_map('unlink', $files);
                }
                rmdir($tempDir);
            }

            if ($returnJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate PDF export: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to generate PDF export: ' . $e->getMessage());
        }
    }

    public function streamPdf(Student $student)
    {
        $pdf = PDF::loadView('students.pdf', compact('student'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream();
    }
}
