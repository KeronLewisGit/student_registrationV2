<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Services\StudentService;
use App\Services\PdfService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /** Maximum students in one bulk PDF export (each PDF takes ~0.3 s on shared hosting). */
    public const BULK_PDF_LIMIT = 250;

    public function __construct(
        protected StudentService $studentService,
        protected PdfService $pdfService
    ) {}

    public function index(Request $request)
    {
        $students = $this->studentService->getFilteredStudents($request->all());

        // Get filter options
        $years = Student::getRegistrationYears();
        $classes = Student::FORM_CLASSES;
        $currentClasses = Student::query()
            ->whereNotNull('current_class')
            ->distinct()
            ->orderBy('current_class')
            ->pluck('current_class')
            ->all();
        $statuses = Student::ENROLMENT_STATUSES;
        $advancedFilters = Student::ADVANCED_FILTERS;
        $advancedOptions = Student::advancedFilterOptions();

        // Stat-card counts (school-wide, independent of the active filters)
        $stats = [
            'total' => Student::count(),
            'male' => Student::where('student_gender', 'Male')->count(),
            'female' => Student::where('student_gender', 'Female')->count(),
            'registered_this_year' => Student::whereYear('registration_date', now()->year)->count(),
        ];

        return view('students.index', compact('students', 'years', 'classes', 'currentClasses', 'statuses', 'stats', 'advancedFilters', 'advancedOptions'));
    }

    public function create()
    {
        $this->authorize('edit-students');

        return view('students.create');
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->studentService->createStudent(
            $request->validated(),
            $request->file('student_passport_photo')
        );

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        $completeness = $student->completeness();
        $activities = $student->activities()->limit(15)->get();

        // Same placeholder cleaning as the printed record, so "Select Blood Type" / "NA" never show as data
        $student = $student->forPrint();

        return view('students.show', compact('student', 'completeness', 'activities'));
    }

    public function edit(Student $student)
    {
        $this->authorize('edit-students');

        return view('students.edit', compact('student'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $student = $this->studentService->updateStudent(
            $student,
            $request->validated(),
            $request->file('student_passport_photo')
        );

        return redirect()
            ->route('students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete-students');

        $this->studentService->deleteStudent($student);

        return redirect()
            ->route('students.index')
            ->with('success', "\"{$student->student_name}\" was deleted. It can be restored from Recently Deleted.");
    }

    public function trash()
    {
        $this->authorize('delete-students');

        $students = Student::onlyTrashed()->orderBy('deleted_at', 'desc')->get();

        return view('students.trash', compact('students'));
    }

    public function restore(Student $student)
    {
        $this->authorize('delete-students');

        $student->restore();

        return redirect()
            ->route('students.show', $student)
            ->with('success', "\"{$student->student_name}\" has been restored.");
    }

    public function generatePdf(Student $student)
    {
        return $this->pdfService->generateStudentPdf($student);
    }

    public function generateBulkPdf(Request $request)
    {
        // Bulk export exposes the same data as reports; keep it behind the same gate.
        $this->authorize('view-reports');

        $students = $this->studentService->getFilteredStudents($request->all());
        $progressId = $request->input('progress_id');

        // Shared hosting kills long requests; keep one export inside the PHP time limit.
        if ($students->count() > self::BULK_PDF_LIMIT) {
            return response()->json([
                'success' => false,
                'message' => "That selection has {$students->count()} students. Export at most " . self::BULK_PDF_LIMIT . " at a time — filter by class or year first.",
                'error_details' => 'Too many students for one export.',
            ], 422);
        }

        return $this->pdfService->generateBulkPdf($students, $progressId);
    }

    public function downloadBulkPdf(Request $request)
    {
        $this->authorize('view-reports');

        $filename = (string) $request->query('file', '');

        if (!preg_match('/^student_profiles_\d{4}-\d{2}-\d{2}_\d{6}_[A-Za-z0-9]+\.zip$/', $filename)) {
            abort(404);
        }

        $path = storage_path('app/exports/' . $filename);

        if (!is_file($path)) {
            abort(404, 'Export not found or already expired.');
        }

        return response()->download($path, $filename);
    }

    public function getBulkPdfProgress(Request $request)
    {
        $this->authorize('view-reports');

        $progressId = $request->input('progress_id');

        if (!$progressId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Progress ID is required.'
            ], 400);
        }

        $progress = $this->pdfService->getProgress($progressId);

        return response()->json($progress);
    }

    public function print(Student $student)
    {
        return view('students.print', compact('student'));
    }

    /**
     * Printable page containing every student matching the current list filters,
     * one profile per page.
     */
    public function printAll(Request $request)
    {
        // Same data exposure as the bulk PDF export, so the same gate applies.
        $this->authorize('view-reports');

        $filters = $request->all();
        $students = $this->studentService->getFilteredStudents($filters);

        $parts = array_filter([
            !empty($filters['current_class']) && $filters['current_class'] !== '0' ? 'Class ' . $filters['current_class'] : null,
            !empty($filters['status']) && $filters['status'] !== 'active'
                ? ($filters['status'] === 'all' ? 'All statuses' : (Student::ENROLMENT_STATUSES[$filters['status']] ?? $filters['status']))
                : null,
            !empty($filters['year']) ? 'Registered ' . $filters['year'] : null,
            !empty($filters['student_class']) && $filters['student_class'] !== '0' ? 'Form 1 class ' . $filters['student_class'] : null,
            !empty($filters['search']) ? 'Search "' . $filters['search'] . '"' : null,
            !empty($filters['incomplete']) ? 'Incomplete records' : null,
        ]);
        foreach ((array) ($filters['f'] ?? []) as $column => $value) {
            if (trim((string) $value) !== '' && isset(Student::ADVANCED_FILTERS[$column])) {
                $parts[] = Student::ADVANCED_FILTERS[$column] . ': ' . $value;
            }
        }
        $filterSummary = $parts ? implode(' · ', $parts) : 'All students';

        return view('students.print-all', compact('students', 'filterSummary'));
    }
}
