<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Year-end promotion: move every active student up one form, and mark the
 * top form as graduated. Admin only.
 */
class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin');
    }

    public function index()
    {
        $target = Student::promotionTargetYear();

        return view('students.promotion', [
            'academicYear' => Student::academicYearLabel($target),
            'targetYear' => $target,
            'preview' => $this->preview($target),
            'newIntake' => Student::where('enrolment_status', 'active')->where('intake_year', '>=', $target)->count(),
            'lastRun' => $this->lastRun(),
            'alreadyRan' => $this->alreadyRan($target),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'confirm' => 'accepted',
        ], ['confirm.accepted' => 'Tick the confirmation box to run the promotion.']);

        $target = Student::promotionTargetYear();
        $label = 'Year-end promotion to ' . Student::academicYearLabel($target);

        // Server-side guard: never move the same students up twice for one year.
        if ($this->alreadyRan($target)) {
            return redirect()
                ->route('students.promotion')
                ->with('error', 'The promotion into ' . Student::academicYearLabel($target) . ' has already been run. Nothing was changed.');
        }

        $lock = Cache::lock('year-end-promotion', 300);
        if (!$lock->get()) {
            return redirect()->route('students.promotion')->with('error', 'A promotion is already running. Please wait.');
        }

        $promoted = 0;
        $graduated = 0;

        try {
            DB::transaction(function () use (&$promoted, &$graduated, $label, $target) {
                // Updates run without the observer so each student gets exactly one audit row.
                Student::withoutEvents(function () use (&$promoted, &$graduated, $label, $target) {
                    Student::query()
                        ->where('enrolment_status', 'active')
                        ->whereNotNull('current_class')
                        // The incoming intake registers in June-August; they have not started yet.
                        ->where(fn ($q) => $q->whereNull('intake_year')->orWhere('intake_year', '<', $target))
                        ->orderBy('id')
                        ->chunkById(200, function ($students) use (&$promoted, &$graduated, $label) {
                            foreach ($students as $student) {
                                $next = $student->nextClass();

                                if ($next !== null) {
                                    $from = $student->current_class;
                                    $student->update(['current_class' => $next]);
                                    StudentActivity::record($student, 'promoted', "{$label}: {$from} to {$next}",
                                        ['current_class' => ['from' => $from, 'to' => $next]]);
                                    $promoted++;
                                } elseif ($student->currentForm() === Student::MAX_FORM) {
                                    $student->update([
                                        'enrolment_status' => 'graduated',
                                        'status_changed_at' => now(),
                                        'status_note' => 'Completed Form ' . Student::MAX_FORM,
                                    ]);
                                    StudentActivity::record($student, 'promoted', "{$label}: graduated from {$student->current_class}",
                                        ['enrolment_status' => ['from' => 'active', 'to' => 'graduated']]);
                                    $graduated++;
                                }
                            }
                        });
                });
            });
        } finally {
            $lock->release();
        }

        return redirect()
            ->route('students.promotion')
            ->with('success', "Promotion into " . Student::academicYearLabel($target) . " complete. {$promoted} students moved up a form, {$graduated} marked as graduated.");
    }

    /**
     * What the promotion would do, grouped by current class.
     *
     * @return array<int, array{from:string, to:string, count:int}>
     */
    private function preview(int $target): array
    {
        $rows = Student::query()
            ->where('enrolment_status', 'active')
            ->whereNotNull('current_class')
            ->where(fn ($q) => $q->whereNull('intake_year')->orWhere('intake_year', '<', $target))
            ->select('current_class', DB::raw('COUNT(*) as total'))
            ->groupBy('current_class')
            ->orderBy('current_class')
            ->get();

        return $rows->map(function ($row) {
            $stub = new Student(['current_class' => $row->current_class]);
            $next = $stub->nextClass();

            return [
                'from' => $row->current_class,
                'to' => $next ?? ($stub->currentForm() === Student::MAX_FORM ? 'Graduated' : 'Unchanged'),
                'count' => (int) $row->total,
            ];
        })->all();
    }

    private function lastRun(): ?StudentActivity
    {
        return StudentActivity::where('action', 'promoted')
            ->where('summary', 'like', 'Year-end promotion%')
            ->latest('created_at')
            ->first();
    }

    private function alreadyRan(int $target): bool
    {
        return StudentActivity::where('action', 'promoted')
            ->where('summary', 'like', 'Year-end promotion to ' . Student::academicYearLabel($target) . '%')
            ->exists();
    }
}
