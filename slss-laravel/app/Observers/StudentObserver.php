<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\StudentActivity;

/**
 * Writes the audit trail for every change to a student record.
 */
class StudentObserver
{
    /**
     * Attributes that change on their own and are not worth logging.
     */
    private const IGNORED = ['updated_at', 'created_at', 'deleted_at'];

    /**
     * Fields whose values are never copied into the audit log; only the fact
     * that they changed is recorded (medical, welfare and identity numbers).
     */
    private const REDACTED_FIELDS = [
        'student_medical_condition', 'student_bloodtype', 'student_allergies', 'student_immunization_status',
        'student_family_crisis', 'student_receiving_counselling', 'student_physical_disabilities',
        'student_learning_disabilities', 'student_educational_aid', 'student_special_sea_concessions',
        'student_emotional_factors', 'student_other_intervention_information',
        'student_social_welfare_status', 'student_social_welfare_detail',
        'student_birth_certificate_pin', 'mother_identification_number', 'father_identification_number',
        'registrant_identification_number',
    ];

    /**
     * Fields whose old value should not be echoed into the log in full.
     */
    private const FILE_FIELDS = [
        'student_passport_photo', 'student_birth_certificate', 'student_sea_slip',
        'student_transfer_slip', 'mother_death_certificate', 'father_death_certificate',
    ];

    /**
     * Columns declared NOT NULL DEFAULT '' in the students table. The form
     * middleware turns blank inputs into null, which the database rejects,
     * so blanks are written back as empty strings here.
     */
    public const NOT_NULL_COLUMNS = [
        'student_family_crisis', 'student_receiving_counselling', 'student_physical_disabilities',
        'student_learning_disabilities', 'student_educational_aid', 'student_special_sea_concessions',
        'student_emotional_factors', 'student_other_intervention_information',
    ];

    public function saving(Student $student): void
    {
        foreach (self::NOT_NULL_COLUMNS as $column) {
            if (array_key_exists($column, $student->getAttributes()) && $student->{$column} === null) {
                $student->{$column} = '';
            }
        }
    }

    /**
     * Fill in intake year, current class and status for any new record that
     * arrives without them (registration webhook, CSV import, legacy import).
     */
    public function creating(Student $student): void
    {
        if (!$student->intake_year) {
            $registered = $student->registration_date ? \Carbon\Carbon::parse($student->registration_date) : now();
            $student->intake_year = (int) $registered->format('Y');
        }

        if (!$student->current_class) {
            $stream = $student->stream();
            if ($stream) {
                $form = min(Student::MAX_FORM, max(1, Student::currentAcademicYear() - $student->intake_year + 1));
                $student->current_class = $form . $stream;
            }
        }

        $student->enrolment_status = $student->enrolment_status ?: 'active';
    }

    public function created(Student $student): void
    {
        StudentActivity::record($student, 'created', 'Record created');
    }

    public function updated(Student $student): void
    {
        $changes = [];

        foreach ($student->getChanges() as $field => $new) {
            if (in_array($field, self::IGNORED, true)) {
                continue;
            }

            $old = $student->getOriginal($field);

            if ($this->same($old, $new)) {
                continue;
            }

            $changes[$field] = in_array($field, self::REDACTED_FIELDS, true)
                ? ['from' => '[redacted]', 'to' => '[redacted]']
                : ['from' => $this->display($field, $old), 'to' => $this->display($field, $new)];
        }

        if (!$changes) {
            return;
        }

        [$action, $summary] = $this->describe($student, $changes);

        StudentActivity::record($student, $action, $summary, $changes);
    }

    public function deleted(Student $student): void
    {
        if (!$student->isForceDeleting()) {
            StudentActivity::record($student, 'deleted', 'Moved to Recently Deleted');
        }
    }

    public function restored(Student $student): void
    {
        StudentActivity::record($student, 'restored', 'Restored from Recently Deleted');
    }

    /**
     * Only a permanent delete removes the student's files from disk; a soft
     * delete must leave them so Restore brings the record back intact.
     */
    public function forceDeleted(Student $student): void
    {
        foreach (self::FILE_FIELDS as $field) {
            Student::deleteStoredFile($student->{$field});
        }
    }

    /**
     * Pick the action code and a one-line summary from the set of changes.
     *
     * @return array{0: string, 1: string}
     */
    private function describe(Student $student, array $changes): array
    {
        $fields = array_keys($changes);

        if ($fields === ['current_class'] || (count($fields) <= 2 && isset($changes['current_class']) && isset($changes['intake_year']))) {
            return ['promoted', sprintf('Class changed from %s to %s',
                $changes['current_class']['from'] ?: 'none', $changes['current_class']['to'] ?: 'none')];
        }

        if (isset($changes['enrolment_status'])) {
            return ['status', sprintf('Status changed from %s to %s',
                Student::ENROLMENT_STATUSES[$changes['enrolment_status']['from']] ?? $changes['enrolment_status']['from'],
                Student::ENROLMENT_STATUSES[$changes['enrolment_status']['to']] ?? $changes['enrolment_status']['to'])];
        }

        if ($fields === ['student_passport_photo']) {
            return ['photo', 'Passport photo ' . ($changes['student_passport_photo']['to'] ? 'uploaded' : 'removed')];
        }

        if (count($fields) === 1 && in_array($fields[0], self::FILE_FIELDS, true)) {
            return ['document', Student::fieldLabel($fields[0]) . ' ' . ($changes[$fields[0]]['to'] ? 'stored' : 'removed')];
        }

        $labels = array_map([Student::class, 'fieldLabel'], array_slice($fields, 0, 3));
        $more = count($fields) > 3 ? ' and ' . (count($fields) - 3) . ' more' : '';

        return ['updated', 'Updated ' . implode(', ', $labels) . $more];
    }

    private function same($old, $new): bool
    {
        return $this->normalise($old) === $this->normalise($new);
    }

    /**
     * Comparable scalar for a value: dates as Y-m-d (the raw stored string
     * carries a time suffix, the cast value is Carbon), strings trimmed,
     * null and '' treated alike.
     */
    private function normalise($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }
        if (preg_match('/^(\d{4}-\d{2}-\d{2})(?: 00:00:00)?$/', $text, $m)) {
            return $m[1];
        }

        return $text;
    }

    private function display(string $field, $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $value = (string) $value;
        if (preg_match('/^(\d{4}-\d{2}-\d{2}) 00:00:00$/', $value, $m)) {
            $value = $m[1];
        }

        if (in_array($field, self::FILE_FIELDS, true)) {
            return basename(parse_url($value, PHP_URL_PATH) ?: $value);
        }

        return mb_strlen($value) > 120 ? mb_substr($value, 0, 117) . '...' : $value;
    }
}
