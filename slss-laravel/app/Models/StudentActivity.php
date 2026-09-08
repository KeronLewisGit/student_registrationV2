<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * The student-record slice of the activity log. Kept as its own model so
 * Student::activities() and the profile page's History panel only see
 * entries about student records.
 */
class StudentActivity extends ActivityLog
{
    protected static function booted(): void
    {
        static::addGlobalScope('student', fn (Builder $q) => $q->where('category', 'student'));
    }

    /**
     * Record an activity against a student.
     */
    public static function record(Student $student, string $action, ?string $summary = null, ?array $changes = null): ActivityLog
    {
        return ActivityLog::log('student', $action, $summary, [
            'subject' => $student,
            'changes' => $changes,
        ]);
    }
}
