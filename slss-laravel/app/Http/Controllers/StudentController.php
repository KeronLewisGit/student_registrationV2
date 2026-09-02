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

        return view('students.index', compact('students', 'years', 'classes'));
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
        return view('students.show', compact('student'));
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
            ->with('success', 'Student deleted successfully.');
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
}
