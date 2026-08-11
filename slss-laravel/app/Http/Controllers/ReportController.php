<?php

namespace App\Http\Controllers;

use App\Exports\StudentsExport;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Reports available in the reports section.
     *
     * @var array<int, array<string, string>>
     */
    protected const REPORTS = [
        [
            'key' => 'all-students',
            'name' => 'All Students',
            'description' => 'A complete spreadsheet of every student in the system, with optional filtering by registration year, form class, or search term.',
            'icon' => 'fa-users',
        ],
    ];

    public function __construct(protected StudentService $studentService)
    {
    }

    /**
     * Show the reports landing page.
     */
    public function index()
    {
        $this->authorize('view-reports');

        return view('reports.index', [
            'reports' => self::REPORTS,
        ]);
    }

    /**
     * Show the configuration page for a single report.
     */
    public function show(string $report)
    {
        $this->authorize('view-reports');

        abort_unless($report === 'all-students', 404);

        return view('reports.all-students', [
            'years' => Student::getRegistrationYears(),
            'classes' => Student::FORM_CLASSES,
            'totalStudents' => Student::count(),
        ]);
    }

    /**
     * Generate and download the "All Students" report as a spreadsheet.
     */
    public function allStudents(Request $request)
    {
        $this->authorize('view-reports');

        $validated = $request->validate([
            'year' => 'nullable|integer|digits:4',
            'student_class' => ['nullable', 'string', Rule::in(Student::FORM_CLASSES)],
            'search' => 'nullable|string|max:255',
            'format' => 'nullable|string|in:xlsx,csv',
        ]);

        $format = $validated['format'] ?? 'xlsx';
        $filters = array_filter([
            'year' => $validated['year'] ?? null,
            'student_class' => $validated['student_class'] ?? null,
            'search' => $validated['search'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        if ($this->studentService->buildFilteredQuery($filters)->doesntExist()) {
            return redirect()
                ->route('reports.show', 'all-students')
                ->withInput()
                ->with('error', 'No students match the selected filters. Adjust the filters and try again.');
        }

        $writerType = $format === 'csv'
            ? \Maatwebsite\Excel\Excel::CSV
            : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(
            new StudentsExport($filters, $this->studentService),
            $this->buildFilename($filters, $format),
            $writerType
        );
    }

    /**
     * Build a descriptive filename reflecting the applied filters.
     *
     * @param  array  $filters
     * @param  string  $format
     * @return string
     */
    protected function buildFilename(array $filters, string $format): string
    {
        $parts = ['all-students'];

        if (!empty($filters['year'])) {
            $parts[] = $filters['year'];
        }

        if (!empty($filters['student_class'])) {
            $parts[] = 'form-' . strtolower($filters['student_class']);
        }

        if (!empty($filters['search'])) {
            $parts[] = 'filtered';
        }

        $parts[] = now()->format('Y-m-d');

        return implode('_', $parts) . '.' . $format;
    }
}
