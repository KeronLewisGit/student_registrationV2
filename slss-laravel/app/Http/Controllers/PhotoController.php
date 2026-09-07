<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * Bulk passport-photo upload: each file is matched to a student by the file
 * name (birth certificate PIN, student ID, or the student's full name).
 */
class PhotoController extends Controller
{
    public function __construct(protected StudentService $studentService)
    {
        $this->middleware('can:edit-students');
    }

    public function index()
    {
        $withoutPhoto = Student::query()
            ->where('enrolment_status', 'active')
            ->where(fn ($q) => $q->whereNull('student_passport_photo')
                ->orWhere('student_passport_photo', '')
                ->orWhereIn('student_passport_photo', ['N/A', 'n/a', 'None', 'Yes', 'No']))
            ->count();

        return view('students.photos', [
            'withoutPhoto' => $withoutPhoto,
            'results' => session('photo_results'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'photos' => 'required|array|min:1|max:200',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'replace' => 'nullable|boolean',
        ]);

        $replace = (bool) $request->boolean('replace');
        $results = ['matched' => [], 'skipped' => [], 'unmatched' => []];

        foreach ($request->file('photos') as $file) {
            /** @var UploadedFile $file */
            $name = $file->getClientOriginalName();
            $student = $this->matchStudent(pathinfo($name, PATHINFO_FILENAME));

            if (!$student) {
                $results['unmatched'][] = $name;
                continue;
            }

            if (!$replace && Student::hasValue($student->student_passport_photo)) {
                $results['skipped'][] = "{$name} → {$student->student_name} (already has a photo)";
                continue;
            }

            $this->studentService->attachPhoto($student, $file);
            $results['matched'][] = "{$name} → {$student->student_name}" . ($student->current_class ? " ({$student->current_class})" : '');
        }

        $summary = sprintf('%d photo(s) attached, %d skipped, %d not matched.',
            count($results['matched']), count($results['skipped']), count($results['unmatched']));

        return redirect()
            ->route('students.photos')
            ->with(count($results['unmatched']) || count($results['skipped']) ? 'warning' : 'success', $summary)
            ->with('photo_results', $results);
    }

    /**
     * Resolve a file name (without extension) to one student.
     * Tries, in order: birth certificate PIN, numeric student ID, full name.
     */
    private function matchStudent(string $stem): ?Student
    {
        $stem = trim($stem);

        // "7235365011", "PIN-7235365011", "7235365011 (1)"
        if (preg_match('/[A-Z]{0,3}\d{6,}/i', $stem, $m)) {
            $pin = strtoupper(preg_replace('/[^0-9A-Z]/', '', strtoupper($m[0])));
            $student = Student::where('student_birth_certificate_pin', $pin)->first();
            if ($student) {
                return $student;
            }
        }

        // "id-123" or just "123"
        if (preg_match('/^(?:id[-_ ]?)?(\d{1,6})$/i', $stem, $m)) {
            $student = Student::find((int) $m[1]);
            if ($student) {
                return $student;
            }
        }

        // "Jah-Marley Lewis", "lewis_jah-marley", "jah marley lewis"
        $words = preg_split('/[\s_.,]+/', strtolower(preg_replace('/\(\d+\)$/', '', $stem)), -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) >= 2) {
            $candidates = Student::query()
                ->where(function ($q) use ($words) {
                    foreach ($words as $w) {
                        $q->where('student_name', 'like', "%{$w}%");
                    }
                })
                ->limit(2)
                ->get();

            if ($candidates->count() === 1) {
                return $candidates->first();
            }
        }

        return null;
    }
}
