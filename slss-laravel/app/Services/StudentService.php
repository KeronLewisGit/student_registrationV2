<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
            // Delete old photo
            if ($student->student_passport_photo) {
                $this->deletePhoto($student->student_passport_photo);
            }

            $data['student_passport_photo'] = $this->handlePhotoUpload($photo, $student->id);
        }

        $data = $this->handleDocumentUploads($data, $student);

        $student->update($data);
        return $student->fresh();
    }

    /**
     * Delete a student record and associated photo.
     *
     * @param  \App\Models\Student  $student
     * @return bool
     */
    public function deleteStudent(Student $student): bool
    {
        // Delete photo if exists
        if ($student->student_passport_photo) {
            $this->deletePhoto($student->student_passport_photo);
        }

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
        $timestamp = time();
        $filename = 'student_' . ($studentId ?? $timestamp) . '_' . $timestamp . '.' . $photo->getClientOriginalExtension();
        $path = $photo->storeAs('passports', $filename, 'public');

        return 'storage/' . $path;
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

            if ($student && $student->{$field}) {
                $this->deletePhoto($student->{$field});
            }

            $filename = $field . '_' . ($student?->id ?? 'new') . '_' . time() . '.'
                . $file->getClientOriginalExtension();

            $data[$field] = 'storage/' . $file->storeAs($directory, $filename, 'public');
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
        $path = str_replace('storage/', '', $photoPath);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Get filtered student records based on search criteria.
     *
     * @param  array  $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFilteredStudents(array $filters): \Illuminate\Database\Eloquent\Collection
    {
        return $this->buildFilteredQuery($filters)->orderBy('student_name')->get();
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

        if (!empty($filters['student_name']) && $filters['student_name'] !== '0') {
            $query->byName($filters['student_name']);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query;
    }
}
