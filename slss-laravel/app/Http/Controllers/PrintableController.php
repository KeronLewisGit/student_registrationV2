<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Everyday printable lists for teachers and the office: class registers,
 * emergency contact sheets, medical alert lists, birthday lists and the
 * outstanding-items report.
 */
class PrintableController extends Controller
{
    public const PRINTABLES = [
        'register' => [
            'name' => 'Class Register',
            'description' => 'Name, gender, date of birth, PIN and a parent phone number for each student, with blank columns for attendance or signatures.',
            'icon' => 'fa-clipboard-list',
        ],
        'contacts' => [
            'name' => 'Emergency Contact Sheet',
            'description' => 'Mother, father and emergency contact names and phone numbers for every student in the class.',
            'icon' => 'fa-phone-alt',
        ],
        'medical' => [
            'name' => 'Medical Alert List',
            'description' => 'Blood type, allergies, medical conditions and immunization status. Students with recorded conditions are highlighted.',
            'icon' => 'fa-heartbeat',
        ],
        'birthdays' => [
            'name' => 'Birthday List',
            'description' => 'Students grouped by birthday month with the age they turn this academic year.',
            'icon' => 'fa-birthday-cake',
        ],
        'outstanding' => [
            'name' => 'Outstanding Items',
            'description' => 'Students whose records are missing essential information (photo, PIN, birth certificate, contacts), as a checklist for the office.',
            'icon' => 'fa-clipboard-check',
        ],
    ];

    public function __construct()
    {
        $this->middleware('can:view-reports');
    }

    public function index()
    {
        $classes = Student::query()
            ->where('enrolment_status', 'active')
            ->whereNotNull('current_class')
            ->select('current_class')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('current_class')
            ->orderBy('current_class')
            ->pluck('total', 'current_class')
            ->all();

        return view('printables.index', [
            'printables' => self::PRINTABLES,
            'classes' => $classes,
        ]);
    }

    public function show(Request $request, string $printable)
    {
        abort_unless(isset(self::PRINTABLES[$printable]), 404);

        $validated = $request->validate([
            'class' => ['nullable', 'string', Rule::in(array_merge(['all'], Student::allClasses()))],
            'status' => ['nullable', 'string', Rule::in(array_merge(['all'], array_keys(Student::ENROLMENT_STATUSES)))],
        ]);

        $class = $validated['class'] ?? 'all';
        $status = $validated['status'] ?? 'active';

        $students = Student::query()
            ->byStatus($status)
            ->when($class !== 'all', fn ($q) => $q->byCurrentClass($class))
            ->orderBy('current_class')
            ->orderBy('student_name')
            ->get();

        $data = [
            'printable' => self::PRINTABLES[$printable],
            'key' => $printable,
            'class' => $class,
            'students' => $students,
            'academicYear' => Student::academicYearLabel(),
            'scope' => $class === 'all' ? 'All classes' : 'Class ' . $class,
        ];

        if ($printable === 'birthdays') {
            $data['months'] = $students
                ->filter(fn ($s) => $s->student_dob)
                ->groupBy(fn ($s) => (int) $s->student_dob->format('n'))
                ->sortKeys()
                ->map(fn ($group) => $group->sortBy(fn ($s) => (int) $s->student_dob->format('j')));
            $data['noDob'] = $students->filter(fn ($s) => !$s->student_dob);
        }

        if ($printable === 'outstanding') {
            $data['rows'] = $students
                ->map(fn ($s) => ['student' => $s, 'completeness' => $s->completeness()])
                ->filter(fn ($row) => $row['completeness']['missing'] !== [])
                ->values();
            $data['items'] = Student::ESSENTIAL_ITEMS;
        }

        return view('printables.' . $printable, $data);
    }
}
