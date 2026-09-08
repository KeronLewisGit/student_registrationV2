<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentService
{
    /**
     * Document upload fields and the storage directory each is saved to.
     *
     * @var array<string, string>
     */
    protected const DOCUMENT_FIELDS = [
        'student_birth_certificate' => 'birth_certificates',
        'student_sea_slip' => 'sea_slips',
        'student_transfer_slip' => 'transfer_slips',
        'mother_death_certificate' => 'death_certificates',
        'father_death_certificate' => 'death_certificates',
    ];

    /**
     * Create a new student record.
     *
     * @param  array  $data
     * @param  \Illuminate\Http\UploadedFile|null  $photo
     * @return \App\Models\Student
     */
    public function createStudent(array $data, ?UploadedFile $photo = null): Student
    {
        // Handle first name + last name concatenation
        if (isset($data['student_first_name']) || isset($data['student_last_name'])) {
            $firstName = trim($data['student_first_name'] ?? '');
            $lastName = trim($data['student_last_name'] ?? '');
            $data['student_name'] = trim("$firstName $lastName");
            unset($data['student_first_name'], $data['student_last_name']);
        }

        if ($photo) {
            $data['student_passport_photo'] = $this->handlePhotoUpload($photo);
        }

        $data = $this->handleDocumentUploads($data);

        // Intake year / current class / status defaults are applied by StudentObserver::creating
        return Student::create($data);
    }

    /**
     * Update an existing student record.
     *
     * @param  \App\Models\Student  $student
     * @param  array  $data
     * @param  \Illuminate\Http\UploadedFile|null  $photo
     * @return \App\Models\Student
     */
    public function updateStudent(Student $student, array $data, ?UploadedFile $photo = null): Student
    {
        // Handle first name + last name concatenation
        if (isset($data['student_first_name']) || isset($data['student_last_name'])) {
            $firstName = trim($data['student_first_name'] ?? '');
            $lastName = trim($data['student_last_name'] ?? '');
            $data['student_name'] = trim("$firstName $lastName");
            unset($data['student_first_name'], $data['student_last_name']);
        }

        if ($photo) {
            // Store the new photo first; only delete the old one once the
            // upload has succeeded, so a failed store can't lose both.
            $oldPhoto = $student->student_passport_photo;
            $data['student_passport_photo'] = $this->handlePhotoUpload($photo, $student->id);

            if ($oldPhoto && $oldPhoto !== $data['student_passport_photo']) {
                $this->deletePhoto($oldPhoto);
            }
        }

        $data = $this->handleDocumentUploads($data, $student);

        $student->update($data);
        return $student->fresh();
    }

    /**
     * Store a passport photo for an existing student (used by the bulk photo
     * upload as well as the edit form) and record it on the student.
     */
    public function attachPhoto(Student $student, UploadedFile $photo): Student
    {
        $oldPhoto = $student->student_passport_photo;
        $path = $this->handlePhotoUpload($photo, $student->id);

        $student->update(['student_passport_photo' => $path]);

        if ($oldPhoto && $oldPhoto !== $path) {
            $this->deletePhoto($oldPhoto);
        }

        return $student;
    }

    /**
     * Delete a student record and associated photo.
     *
     * @param  \App\Models\Student  $student
     * @return bool
     */
    public function deleteStudent(Student $student): bool
    {
        // Soft delete only: files stay on disk so Restore brings the record back
        // intact. StudentObserver::forceDeleted removes them on permanent deletion.
        return $student->delete();
    }

    /**
     * Handle student passport photo upload.
     *
     * @param  \Illuminate\Http\UploadedFile  $photo
     * @param  int|null  $studentId
     * @return string
     */
    protected function handlePhotoUpload(UploadedFile $photo, ?int $studentId = null): string
    {
        return $this->storePrivately($photo, 'passports', 'student_' . ($studentId ?? 'new'));
    }

    /**
     * Save an upload into private storage (storage/app/private/<dir>) with a
     * random, unguessable name and an extension derived from the file's real
     * MIME type, never from the client-supplied name.
     */
    protected function storePrivately(UploadedFile $file, string $directory, string $stem): string
    {
        $extension = strtolower($file->extension() ?: 'bin');
        $filename = $stem . '_' . Str::random(12) . '.' . $extension;

        $file->storeAs('private/' . $directory, $filename, 'local');

        return 'private/' . $directory . '/' . $filename;
    }

    /**
     * Store any uploaded supporting documents and replace the uploaded
     * files in $data with their storage paths.
     *
     * Fields with no new upload are removed so an existing path is not
     * overwritten with null on update.
     *
     * @param  array  $data
     * @param  \App\Models\Student|null  $student
     * @return array
     */
    protected function handleDocumentUploads(array $data, ?Student $student = null): array
    {
        foreach (self::DOCUMENT_FIELDS as $field => $directory) {
            if (!isset($data[$field])) {
                continue;
            }

            if (!$data[$field] instanceof UploadedFile) {
                unset($data[$field]);
                continue;
            }

            $file = $data[$field];
            $oldDocument = $student?->{$field};

            $data[$field] = $this->storePrivately($file, $directory, $field . '_' . ($student?->id ?? 'new'));

            // Delete the replaced document only after the new one is stored
            if ($oldDocument && $oldDocument !== $data[$field]) {
                $this->deletePhoto($oldDocument);
            }
        }

        return $data;
    }

    /**
     * Delete a student photo from storage.
     *
     * @param  string  $photoPath
     * @return void
     */
    protected function deletePhoto(string $photoPath): void
    {
        Student::deleteStoredFile($photoPath);
    }

    /**
     * Get filtered student records based on search criteria.
     *
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    /**
     * Sort keys the list, print batch and export all understand. They mirror
     * the sortable columns of the student table.
     */
    public const SORTS = ['name', 'class', 'complete', 'gender', 'dob', 'registered'];

    public function getFilteredStudents(array $filters): \Illuminate\Database\Eloquent\Collection
    {
        $students = $this->buildFilteredQuery($filters)->orderBy('student_name')->get();

        // Completeness is computed in PHP (it spans many columns), so the
        // "incomplete only" filter is applied after the query.
        if (!empty($filters['incomplete'])) {
            $students = $students->filter(fn (Student $s) => !$s->isComplete())->values();
        }

        return $this->sortStudents($students, $filters);
    }

    /**
     * Order a set of students the way the on-screen list is ordered:
     * sort = name|class|complete|gender|dob|registered, dir = asc|desc,
     * names = first|last (which part of the name to sort by).
     */
    public function sortStudents(\Illuminate\Database\Eloquent\Collection $students, array $filters): \Illuminate\Database\Eloquent\Collection
    {
        $sort = in_array($filters['sort'] ?? null, self::SORTS, true) ? $filters['sort'] : 'name';
        $desc = ($filters['dir'] ?? 'asc') === 'desc';
        $names = ($filters['names'] ?? 'first') === 'last' ? 'last' : 'first';

        $nameKey = fn (Student $s) => $s->nameSortKeys()[$names];

        $key = match ($sort) {
            'class' => fn (Student $s) => [$s->current_class ?? 'zz', $nameKey($s)],
            'complete' => fn (Student $s) => [$s->completeness()['percent'], $nameKey($s)],
            'gender' => fn (Student $s) => [strtolower((string) $s->student_gender) ?: 'zz', $nameKey($s)],
            'dob' => fn (Student $s) => [$s->student_dob?->format('Y-m-d') ?? '9999', $nameKey($s)],
            'registered' => fn (Student $s) => [$s->registration_date?->format('Y-m-d') ?? '0000', $nameKey($s)],
            default => fn (Student $s) => [$nameKey($s)],
        };

        $sorted = $students->sortBy($key, SORT_REGULAR, $desc);

        return new \Illuminate\Database\Eloquent\Collection($sorted->values()->all());
    }

    /**
     * Human description of a sort, for print headers ("by last name, Z to A").
     */
    public static function describeSort(array $filters): ?string
    {
        $sort = in_array($filters['sort'] ?? null, self::SORTS, true) ? $filters['sort'] : 'name';
        $desc = ($filters['dir'] ?? 'asc') === 'desc';
        $names = ($filters['names'] ?? 'first') === 'last' ? 'last name' : 'first name';

        if ($sort === 'name' && !$desc && $names === 'first name') {
            return null; // the default
        }

        $label = match ($sort) {
            'class' => 'class', 'complete' => 'completeness', 'gender' => 'gender',
            'dob' => 'date of birth', 'registered' => 'registration date', default => $names,
        };
        $direction = in_array($sort, ['dob', 'registered', 'complete'], true)
            ? ($desc ? 'highest first' : 'lowest first')
            : ($desc ? 'Z to A' : 'A to Z');
        if ($sort === 'dob') { $direction = $desc ? 'youngest first' : 'oldest first'; }
        if ($sort === 'registered') { $direction = $desc ? 'newest first' : 'oldest first'; }

        return "sorted by {$label}, {$direction}";
    }

    /**
     * Build the base query for the given filters without executing it.
     *
     * Shared by the student listing and the reports/export layer so both
     * apply identical filtering rules.
     *
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function buildFilteredQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = Student::query();

        if (!empty($filters['year'])) {
            $query->byYear($filters['year']);
        }

        if (!empty($filters['student_class']) && $filters['student_class'] !== '0') {
            $query->byClass($filters['student_class']);
        }

        if (!empty($filters['current_class']) && $filters['current_class'] !== '0') {
            $query->byCurrentClass($filters['current_class']);
        }

        // Active students are shown unless another status (or "all") is asked for.
        $query->byStatus($filters['status'] ?? 'active');

        // Advanced filters (gender, religion, ...): f[column]=value
        if (!empty($filters['f']) && is_array($filters['f'])) {
            $query->advanced($filters['f']);
        }

        if (!empty($filters['student_name']) && $filters['student_name'] !== '0') {
            $query->byName($filters['student_name']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query;
    }
}
