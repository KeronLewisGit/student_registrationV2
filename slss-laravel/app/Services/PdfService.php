<?php

namespace App\Services;

use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PdfService
{
    /**
     * Generate a PDF for a single student profile.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function generateStudentPdf(Student $student)
    {
        $pdf = PDF::loadView('students.pdf', compact('student'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download($this->profileFilename($student));
    }

    /**
     * Build a unique, filesystem-safe PDF filename for a student.
     *
     * The ID prefix guarantees uniqueness — without it, two students with
     * the same name overwrite each other inside the bulk export ZIP.
     */
    private function profileFilename(Student $student): string
    {
        $name = trim((string) $student->student_name);
        $name = $name !== '' ? preg_replace('/[^A-Za-z0-9]+/', '_', strtolower($name)) : 'student';

        return 'profile_' . $student->id . '_' . trim($name, '_') . '.pdf';
    }

    /**
     * Generate bulk PDF export for multiple students.
     *
     * @param  \Illuminate\Support\Collection  $students
     * @param  string|null  $progressId
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function generateBulkPdf(Collection $students, ?string $progressId = null)
    {
        $returnJson = !empty($progressId);

        // Initialize progress IMMEDIATELY if progressId is provided
        if ($progressId) {
            try {
                Cache::put("pdf_progress_{$progressId}", [
                    'status' => 'initializing',
                    'step' => 'validating',
                    'progress' => 0,
                    'current' => 0,
                    'total' => $students->count(),
                    'message' => 'Validating export request...'
                ], 600);
            } catch (\Exception $e) {
                Log::error("Failed to initialize progress tracking", [
                    'progress_id' => $progressId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Check if there are any students to export
        if ($students->isEmpty()) {
            $errorMsg = 'No students found to export. Please adjust your filters.';

            if ($returnJson) {
                try {
                    Cache::put("pdf_progress_{$progressId}", [
                        'status' => 'failed',
                        'step' => 'validation',
                        'progress' => 0,
                        'message' => $errorMsg,
                        'error_details' => 'No students matched your filter criteria.'
                    ], 600);
                } catch (\Exception $e) {
                    Log::error("Failed to update progress on validation failure", ['error' => $e->getMessage()]);
                }

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

            // Update progress to processing
            if ($progressId) {
                try {
                    Cache::put("pdf_progress_{$progressId}", [
                        'status' => 'processing',
                        'step' => 'initializing',
                        'progress' => 1,
                        'current' => 0,
                        'total' => $total,
                        'message' => "Starting export of {$total} student profiles..."
                    ], 600);
                } catch (\Exception $e) {
                    Log::error("Failed to update progress to processing", ['error' => $e->getMessage()]);
                }
            }

            // Create temporary directory for PDFs (unique per export so
            // concurrent exports never share or delete each other's files)
            $tempDir = storage_path('app/temp_pdfs_' . str_replace('.', '', uniqid('', true)));
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    throw new \Exception('Failed to create temporary directory for PDF generation.');
                }
            }

            Log::info("Starting bulk PDF export for {$total} students", ['temp_dir' => $tempDir]);

            // Generate individual PDF for each student (full profile with all 127 fields)
            $current = 0;
            $failedStudents = [];
            $lastProgress = 0;
            $lastUpdateTime = microtime(true);

            foreach ($students as $index => $student) {
                $current++;

                try {
                    // Update progress (throttled to reduce cache churn)
                    if ($progressId) {
                        $progress = max(1, round(($current / $total) * 80)); // Reserve 20% for zipping, min 1%
                        $currentTime = microtime(true);
                        $timeSinceUpdate = $currentTime - $lastUpdateTime;

                        // Update if: progress changed by 1% OR 0.5 seconds passed OR it's the last student
                        $shouldUpdate = ($progress != $lastProgress) || ($timeSinceUpdate >= 0.5) || ($current == $total);

                        if ($shouldUpdate) {
                            try {
                                Cache::put("pdf_progress_{$progressId}", [
                                    'status' => 'processing',
                                    'step' => 'generating_pdfs',
                                    'progress' => $progress,
                                    'current' => $current,
                                    'total' => $total,
                                    'message' => "Generating PDF {$current} of {$total}: {$student->student_name}"
                                ], 600);
                                $lastProgress = $progress;
                                $lastUpdateTime = $currentTime;
                            } catch (\Exception $e) {
                                // Log but don't fail the export if cache update fails
                                Log::warning("Failed to update progress for student {$current}", ['error' => $e->getMessage()]);
                            }
                        }
                    }

                    $pdf = PDF::loadView('students.pdf', compact('student'));
                    $pdf->setPaper('letter', 'portrait');

                    $pdfPath = $tempDir . '/' . $this->profileFilename($student);

                    $pdf->save($pdfPath);

                    // save() returns the PDF object (always truthy), so verify on disk instead
                    if (!is_file($pdfPath) || filesize($pdfPath) === 0) {
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
                try {
                    Cache::put("pdf_progress_{$progressId}", [
                        'status' => 'processing',
                        'step' => 'creating_zip',
                        'progress' => 85,
                        'current' => $total,
                        'total' => $total,
                        'message' => 'Creating ZIP archive...'
                    ], 600);
                } catch (\Exception $e) {
                    Log::warning("Failed to update progress for ZIP creation", ['error' => $e->getMessage()]);
                }
            }

            // Create ZIP archive in PRIVATE storage (storage/app/exports is not
            // web-served; downloads go through the authenticated
            // students.bulk-pdf-download route). The random suffix also makes
            // filenames unguessable.
            $exportDir = storage_path('app/exports');
            $zipFilename = 'student_profiles_' . now()->format('Y-m-d_His') . '_' . \Illuminate\Support\Str::random(16) . '.zip';
            $zipPath = $exportDir . '/' . $zipFilename;

            if (!file_exists($exportDir)) {
                if (!mkdir($exportDir, 0755, true)) {
                    throw new \Exception('Failed to create exports storage directory.');
                }
            }

            // Remove export archives older than 24 hours so PII doesn't accumulate on disk
            foreach (glob($exportDir . '/student_profiles_*.zip') ?: [] as $oldZip) {
                if (filemtime($oldZip) < now()->subDay()->getTimestamp()) {
                    @unlink($oldZip);
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
                'failed_students' => count($failedStudents),
                'zip_path' => $zipPath,
                'zip_size' => filesize($zipPath)
            ]);

            // Verify file is readable
            if (!file_exists($zipPath)) {
                throw new \Exception("ZIP file was created but cannot be found at: {$zipPath}");
            }

            if (!is_readable($zipPath)) {
                throw new \Exception("ZIP file exists but is not readable. Check file permissions.");
            }

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
                try {
                    Cache::put("pdf_progress_{$progressId}", [
                        'status' => 'completed',
                        'step' => 'completed',
                        'progress' => 100,
                        'current' => $total,
                        'total' => $total,
                        'message' => $successMsg,
                        'download_url' => route('students.bulk-pdf-download', ['file' => $zipFilename]),
                        'filename' => $zipFilename,
                        'failed_count' => count($failedStudents)
                    ], 600);
                } catch (\Exception $e) {
                    Log::error("Failed to mark export as complete in cache", ['error' => $e->getMessage()]);
                }
            }

            // Return JSON response for AJAX or download directly
            if ($returnJson) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'download_url' => route('students.bulk-pdf-download', ['file' => $zipFilename]),
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
                try {
                    Cache::put("pdf_progress_{$progressId}", [
                        'status' => 'failed',
                        'step' => 'error',
                        'progress' => 0,
                        'message' => $errorMessage,
                        'error_details' => $errorDetails
                    ], 600);
                } catch (\Exception $cacheError) {
                    Log::error("Failed to mark export as failed in cache", ['error' => $cacheError->getMessage()]);
                }
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

    /**
     * Get the progress of an ongoing bulk PDF export.
     *
     * @param  string  $progressId
     * @return array
     */
    public function getProgress(string $progressId): array
    {
        try {
            $progress = Cache::get("pdf_progress_{$progressId}");

            if (!$progress) {
                return [
                    'status' => 'not_found',
                    'step' => 'unknown',
                    'progress' => 0,
                    'current' => 0,
                    'total' => 0,
                    'message' => 'Export session not found or expired.'
                ];
            }

            // Ensure progress value is numeric and within bounds
            if (isset($progress['progress'])) {
                $progress['progress'] = max(0, min(100, (int)$progress['progress']));
            }

            return $progress;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve progress", [
                'progress_id' => $progressId,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => 'error',
                'step' => 'cache_error',
                'progress' => 0,
                'current' => 0,
                'total' => 0,
                'message' => 'Unable to retrieve export progress.'
            ];
        }
    }

    /**
     * Stream a PDF for preview instead of download.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function streamPdf(Student $student)
    {
        $pdf = PDF::loadView('students.pdf', compact('student'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream();
    }
}
