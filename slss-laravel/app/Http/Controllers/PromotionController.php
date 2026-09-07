<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentActivity;
use Illuminate\Http\Request;
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
        $preview = $this->preview();

        $lastRun = StudentActivity::where('action', 'promoted')
            ->where('summary', 'like', 'Year-end promotion%')
            ->latest('created_at')
            ->first();

        return view('students.promotion', [
            'academicYear' => Student::academicYearLabel(),
            'preview' => $preview,
            'lastRun' => $lastRun,
            'ranThisYear' => $lastRun && Student::currentAcademicYear($lastRun->created_at) === Student::currentAcademicYear(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'confirm' => 'accepted',
        ], ['confirm.accepted' => 'Tick the confirmation box to run the promotion.']);

        $promoted = 0;
        $graduated = 0;
        $label = 'Year-end promotion to ' . Student::academicYearLabel();

        DB::transaction(function () use (&$promoted, &$graduated, $label) {
            Student::query()
                ->where('enrolment_status', 'active')
                ->whereNotNull('current_class')
                ->orderBy('id')
                ->chunkById(200, function ($students) use (&$promoted, &$graduated, $label) {
                    foreach ($students as $student) {
                        $next = $student->nextClass();

                        if ($next !== null) {
                            $from = $student->current_class;
                            $student->update(['current_class' => $next]);
                            StudentActivity::record($student, 'promoted', "{$label}: {$from} to {$next}");
                            $promoted++;
                        } elseif ($student->currentForm() === Student::MAX_FORM) {
                            $student->update([
                                'enrolment_status' => 'graduated',
                                'status_changed_at' => now(),
                                'status_note' => 'Completed Form ' . Student::MAX_FORM,
                            ]);
                            StudentActivity::record($student, 'promoted', "{$label}: graduated from {$student->current_class}");
                            $graduated++;
                        }
                    }
                });
        });

        return redirect()
            ->route('students.promotion')
            ->with('success', "Promotion complete. {$promoted} students moved up a form, {$graduated} marked as graduated.");
    }

    /**
     * What the promotion would do, grouped by current class.
     *
     * @return array<int, array{from:string, to:string, count:int}>
     */
    private function preview(): array
    {
        $rows = Student::query()
            ->where('enrolment_status', 'active')
            ->whereNotNull('current_class')
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
}
