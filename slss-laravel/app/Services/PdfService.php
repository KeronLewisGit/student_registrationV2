<?php

namespace App\Services;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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
        $returnJson = !empty($progressId);

        // Check if there are any students to export
        if ($students->isEmpty()) {
            $errorMsg = 'No students found to export. Please adjust your filters.';

            if ($returnJson) {
                Cache::put("pdf_progress_{$progressId}", [
                    'status' => 'failed',
                    'step' => 'validation',
                    'progress' => 0,
                    'message' => $errorMsg
                ], 600);

                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'error_details' => 'No students matched your filter criteria.'
                ], 400);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $tempDir = null;

        try {
            $total = $students->count();

            // Initialize progress
            if ($progressId) {
                Cache::put("pdf_progress_{$progressId}", [
                    'status' => 'processing',
                    'step' => 'initializing',
                    'progress' => 0,
                    'current' => 0,
                    'total' => $total,
                    'message' => "Starting export of {$total} student profiles..."
                ], 600);
            }

            // Create temporary directory for PDFs
            $tempDir = storage_path('app/temp_pdfs_' . time());
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    throw new \Exception('Failed to create temporary directory for PDF generation.');
                }
            }

            Log::info("Starting bulk PDF export for {$total} students", ['temp_dir' => $tempDir]);

            // Generate individual PDF for each student (full profile with all 127 fields)
            $current = 0;
            $failedStudents = [];

            foreach ($students as $index => $student) {
                $current++;

                try {
                    // Update progress
                    if ($progressId) {
                        $progress = round(($current / $total) * 80); // Reserve 20% for zipping
                        Cache::put("pdf_progress_{$progressId}", [
                            'status' => 'processing',
                            'step' => 'generating_pdfs',
                            'progress' => $progress,
                            'current' => $current,
                            'total' => $total,
                            'message' => "Generating PDF {$current} of {$total}: {$student->student_name}"
                        ], 600);
                    }

                    $pdf = PDF::loadView('students.pdf', compact('student'));
                    $pdf->setPaper('letter', 'portrait');

                    $filename = 'profile_' . str_replace(' ', '_', strtolower($student->student_name)) . '.pdf';
                    $pdfPath = $tempDir . '/' . $filename;

                    if (!$pdf->save($pdfPath)) {
                        throw new \Exception("Failed to save PDF for student: {$student->student_name}");
                    }

                } catch (\Exception $e) {
                    Log::error("Failed to generate PDF for student ID {$student->id}: " . $e->getMessage());
                    $failedStudents[] = $student->student_name;
                    // Continue with other students
                }
            }

            // Check if any PDFs were generated
            $generatedFiles = glob($tempDir . '/*.pdf');
            if (empty($generatedFiles)) {
                throw new \Exception('No PDFs were successfully generated. Please check the error logs for details.');
            }

            // Update progress - Creating ZIP
            if ($progressId) {
                Cache::put("pdf_progress_{$progressId}", [
                    'status' => 'processing',
                    'step' => 'creating_zip',
                    'progress' => 85,
                    'current' => $total,
                    'total' => $total,
                    'message' => 'Creating ZIP archive...'
                ], 600);
            }

            // Create ZIP archive
            $zipFilename = 'student_profiles_' . now()->format('Y-m-d_His') . '.zip';
            $zipPath = storage_path('app/public/' . $zipFilename);

            // Ensure public storage directory exists
            if (!file_exists(storage_path('app/public'))) {
                if (!mkdir(storage_path('app/public'), 0755, true)) {
                    throw new \Exception('Failed to create public storage directory.');
                }
            }

            $zip = new \ZipArchive();
            $zipResult = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            if ($zipResult !== true) {
                throw new \Exception('Failed to create ZIP archive. Error code: ' . $zipResult);
            }

            // Add all PDFs to the zip
            foreach ($generatedFiles as $file) {
                if (!$zip->addFile($file, basename($file))) {
                    Log::warning("Failed to add file to ZIP: " . basename($file));
                }
            }

            if (!$zip->close()) {
                throw new \Exception('Failed to finalize ZIP archive.');
            }

            Log::info("ZIP archive created successfully", [
                'filename' => $zipFilename,
                'total_files' => count($generatedFiles),
                'failed_students' => count($failedStudents)
            ]);

            // Clean up temporary PDFs
            array_map('unlink', $generatedFiles);
            if (is_dir($tempDir)) {
                rmdir($tempDir);
            }

            // Prepare success message
            $successMsg = "Successfully exported " . count($generatedFiles) . " student profiles!";
            if (!empty($failedStudents)) {
                $successMsg .= " (Note: " . count($failedStudents) . " students failed - check logs for details)";
            }

            // Mark as complete
            if ($progressId) {
                Cache::put("pdf_progress_{$progressId}", [
                    'status' => 'completed',
                    'step' => 'completed',
                    'progress' => 100,
                    'current' => $total,
                    'total' => $total,
                    'message' => $successMsg,
                    'download_url' => url('storage/' . $zipFilename),
                    'filename' => $zipFilename,
                    'failed_count' => count($failedStudents)
                ], 600);
            }

            // Return JSON response for AJAX or download directly
            if ($returnJson) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'download_url' => url('storage/' . $zipFilename),
                    'filename' => $zipFilename,
                    'total' => count($generatedFiles),
                    'failed' => count($failedStudents)
                ]);
            }

            // Direct download
            return response()->download($zipPath, $zipFilename);

        } catch (\Exception $e) {
            // Detailed error logging
            Log::error('Bulk PDF export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'students_count' => $students->count()
            ]);

            // Clean up on error
            if ($tempDir && file_exists($tempDir)) {
                $files = glob($tempDir . '/*.pdf');
                if ($files) {
                    array_map('unlink', $files);
                }
                if (is_dir($tempDir)) {
                    rmdir($tempDir);
                }
            }

            $errorMessage = 'Failed to generate PDF export: ' . $e->getMessage();
            $errorDetails = 'Error occurred during export process. Please check server logs for details.';

            // Determine specific failure point
            if (strpos($e->getMessage(), 'temporary directory') !== false) {
                $errorDetails = 'Failed to create temporary directory. Check server permissions for storage/app folder.';
            } elseif (strpos($e->getMessage(), 'No PDFs were successfully generated') !== false) {
                $errorDetails = 'All student PDFs failed to generate. Check if the PDF template exists and student data is valid.';
            } elseif (strpos($e->getMessage(), 'ZIP') !== false) {
                $errorDetails = 'PDFs generated successfully but failed to create ZIP archive. Check ZIP extension is installed.';
            } elseif (strpos($e->getMessage(), 'storage directory') !== false) {
                $errorDetails = 'Failed to create public storage directory. Check server permissions.';
            }

            if ($progressId) {
                Cache::put("pdf_progress_{$progressId}", [
                    'status' => 'failed',
                    'step' => 'error',
                    'progress' => 0,
                    'message' => $errorMessage,
                    'error_details' => $errorDetails
                ], 600);
            }

            if ($returnJson) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'error_details' => $errorDetails
                ], 500);
            }

            return redirect()->back()->with('error', $errorMessage . ' Details: ' . $errorDetails);
        }
    }

    public function getProgress(string $progressId): array
    {
        return Cache::get("pdf_progress_{$progressId}", [
            'status' => 'not_found',
            'step' => 'unknown',
            'progress' => 0,
            'current' => 0,
            'total' => 0,
            'message' => 'Export session not found or expired.'
        ]);
    }

    public function streamPdf(Student $student)
    {
        $pdf = PDF::loadView('students.pdf', compact('student'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream();
    }
}
