<?php

namespace App\Services;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PdfService
{
    public function generateStudentPdf(Student $student)
    {
        $pdf = PDF::loadView('students.pdf', compact('student'));
        $pdf->setPaper('letter', 'portrait');

        $filename = 'profile_' . str_replace(' ', '_', strtolower($student->student_name)) . '.pdf';

        return $pdf->download($filename);
    }

    public function generateBulkPdf(Collection $students, ?string $progressId = null)
    {
        // Check if there are any students to export
        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'No students found to export. Please adjust your filters.');
        }

        // Initialize progress tracking
        if ($progressId) {
            Cache::put("pdf_progress_{$progressId}", [
                'status' => 'processing',
                'progress' => 0,
                'current' => 0,
                'total' => $students->count(),
                'message' => 'Starting export...'
            ], 600); // 10 minutes expiry
        }

        // Create temporary directory for PDFs
        $tempDir = storage_path('app/temp_pdfs_' . time());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        try {
            $total = $students->count();
            $current = 0;

            // Generate individual PDF for each student (full profile with all 127 fields)
            foreach ($students as $student) {
                $current++;

                // Update progress
                if ($progressId) {
                    $progress = round(($current / $total) * 90); // Reserve 10% for zipping
                    Cache::put("pdf_progress_{$progressId}", [
                        'status' => 'processing',
                        'progress' => $progress,
                        'current' => $current,
                        'total' => $total,
                        'message' => "Generating PDF {$current} of {$total}..."
                    ], 600);
                }

                $pdf = PDF::loadView('students.pdf', compact('student'));
                $pdf->setPaper('letter', 'portrait');

                $filename = 'profile_' . str_replace(' ', '_', strtolower($student->student_name)) . '.pdf';
                $pdf->save($tempDir . '/' . $filename);
            }

            // Update progress - Creating ZIP
            if ($progressId) {
                Cache::put("pdf_progress_{$progressId}", [
                    'status' => 'processing',
                    'progress' => 90,
                    'current' => $total,
                    'total' => $total,
                    'message' => 'Creating ZIP archive...'
                ], 600);
            }

            // Create ZIP archive
            $zipFilename = 'student_profiles_' . now()->format('Y-m-d_His') . '.zip';
            $zipPath = storage_path('app/' . $zipFilename);

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

            // Mark as complete
            if ($progressId) {
                Cache::put("pdf_progress_{$progressId}", [
                    'status' => 'completed',
                    'progress' => 100,
                    'current' => $total,
                    'total' => $total,
                    'message' => 'Export completed successfully!',
                    'download_path' => $zipFilename
                ], 600);
            }

            // Download the ZIP file and delete after sending
            return response()->download($zipPath, $zipFilename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // Mark as failed
            if ($progressId) {
                Cache::put("pdf_progress_{$progressId}", [
                    'status' => 'failed',
                    'progress' => 0,
                    'current' => 0,
                    'total' => $students->count(),
                    'message' => 'Export failed: ' . $e->getMessage()
                ], 600);
            }

            // Clean up on error
            if (file_exists($tempDir)) {
                $files = glob($tempDir . '/*.pdf');
                if ($files) {
                    array_map('unlink', $files);
                }
                rmdir($tempDir);
            }

            return redirect()->back()->with('error', 'Failed to generate PDF export: ' . $e->getMessage());
        }
    }

    public function getProgress(string $progressId): array
    {
        return Cache::get("pdf_progress_{$progressId}", [
            'status' => 'not_found',
            'progress' => 0,
            'current' => 0,
            'total' => 0,
            'message' => 'Export session not found.'
        ]);
    }

    public function streamPdf(Student $student)
    {
        $pdf = PDF::loadView('students.pdf', compact('student'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream();
    }
}
