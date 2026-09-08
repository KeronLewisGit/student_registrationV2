<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Student;
use Illuminate\Http\Request;

/**
 * Serves uploaded student photos and documents from private storage.
 *
 * Photos need a login; birth/death certificates and slips also need the
 * view-sensitive permission (admin or staff).
 */
class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request, string $path)
    {
        if (!preg_match(Student::STORED_FILE_PATTERN, $path, $m)) {
            abort(404);
        }

        if ($m['dir'] !== 'passports') {
            $this->authorize('view-sensitive');
        }

        $absolute = Student::documentPath($path);

        if ($absolute === null || !is_file($absolute)) {
            abort(404);
        }

        // Which student owns this file (photos are viewed constantly; only log the documents)
        if ($m['dir'] !== 'passports') {
            $owner = Student::withTrashed()
                ->where(fn ($q) => $q->where('student_birth_certificate', $path)->orWhere('student_sea_slip', $path)
                    ->orWhere('student_transfer_slip', $path)->orWhere('mother_death_certificate', $path)->orWhere('father_death_certificate', $path))
                ->first();
            ActivityLog::log('document', 'viewed', 'Viewed ' . str_replace('_', ' ', $m['dir']) . ' file ' . $m['file'], ['subject' => $owner]);
        }

        $mime = mime_content_type($absolute) ?: 'application/octet-stream';
        $inline = str_starts_with($mime, 'image/') || $mime === 'application/pdf';

        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . basename($absolute) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
