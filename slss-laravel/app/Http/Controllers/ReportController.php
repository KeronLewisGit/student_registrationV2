<?php

namespace App\Http\Controllers;

use App\Exports\StudentsExport;
use App\Models\ActivityLog;
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
            'description' => 'A spreadsheet of students, filtered the same way as the student list, with your choice of columns.',
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

        $currentClasses = Student::query()->whereNotNull('current_class')->distinct()->orderBy('current_class')->pluck('current_class')->all();

        return view('reports.all-students', [
            'years' => Student::getRegistrationYears(),
            'classes' => Student::FORM_CLASSES,
            'currentClasses' => $currentClasses,
            'statuses' => Student::ENROLMENT_STATUSES,
            'totalStudents' => Student::count(),
            'activeStudents' => Student::where('enrolment_status', 'active')->count(),
            'columnGroups' => StudentsExport::COLUMN_GROUPS,
            'advancedFilters' => Student::ADVANCED_FILTERS,
            'advancedOptions' => Student::advancedFilterOptions(),
            'columnLabels' => StudentsExport::COLUMNS,
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
            'current_class' => ['nullable', 'string', Rule::in(Student::allClasses())],
            'status' => ['nullable', 'string', Rule::in(array_merge(['all'], array_keys(Student::ENROLMENT_STATUSES)))],
            'search' => 'nullable|string|max:255',
            'format' => 'nullable|string|in:xlsx,csv',
            'sort' => ['nullable', Rule::in(\App\Services\StudentService::SORTS)],
            'dir' => 'nullable|in:asc,desc',
            'names' => 'nullable|in:first,last',
            'f' => 'nullable|array',
            'f.*' => 'nullable|string|max:100',
            'columns' => 'nullable|array|min:1',
            'columns.*' => ['string', Rule::in(array_keys(StudentsExport::COLUMNS))],
        ], ['columns.min' => 'Choose at least one column to include.']);

        $format = $validated['format'] ?? 'xlsx';
        $filters = array_filter([
            'year' => $validated['year'] ?? null,
            'student_class' => $validated['student_class'] ?? null,
            'current_class' => $validated['current_class'] ?? null,
            'status' => $validated['status'] ?? null,
            'search' => $validated['search'] ?? null,
            'sort' => $validated['sort'] ?? null,
            'dir' => $validated['dir'] ?? null,
            'names' => $validated['names'] ?? null,
            'f' => array_filter(array_intersect_key($validated['f'] ?? [], Student::ADVANCED_FILTERS), fn ($v) => trim((string) $v) !== '') ?: null,
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

        $count = $this->studentService->buildFilteredQuery($filters)->count();
        ActivityLog::log('export', 'spreadsheet', "Exported {$count} students to " . strtoupper($format)
            . ' (' . (isset($validated['columns']) ? count($validated['columns']) : count(StudentsExport::COLUMNS)) . ' columns)'
            . ($filters ? ' with filters ' . http_build_query($filters, '', ', ') : ''));

        return Excel::download(
            new StudentsExport($filters, $this->studentService, $validated['columns'] ?? null),
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
