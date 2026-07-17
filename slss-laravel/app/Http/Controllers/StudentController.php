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
        $studentNames = Student::getStudentNames();
        $classes = ['1A', '1B', '1C', '1D', '1E', '1F'];

        return view('students.index', compact('students', 'years', 'studentNames', 'classes'));
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
            ->route('students.index', [
                'year' => $student->registration_date?->year,
                'student_class' => $student->form_1_class,
                'student_name' => $student->student_name
            ])
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
        $students = $this->studentService->getFilteredStudents($request->all());
        $progressId = $request->input('progress_id');

        return $this->pdfService->generateBulkPdf($students, $progressId);
    }

    public function getBulkPdfProgress(Request $request)
    {
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
