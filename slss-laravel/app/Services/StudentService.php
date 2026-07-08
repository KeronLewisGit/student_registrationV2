<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StudentService
{
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

        return Student::create($data);
    }

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

        $student->update($data);
        return $student->fresh();
    }

    public function deleteStudent(Student $student): bool
    {
        // Delete photo if exists
        if ($student->student_passport_photo) {
            $this->deletePhoto($student->student_passport_photo);
        }

        return $student->delete();
    }

    protected function handlePhotoUpload(UploadedFile $photo, ?int $studentId = null): string
    {
        $filename = 'student_' . ($studentId ?? time()) . '_' . time() . '.' . $photo->getClientOriginalExtension();
        $path = $photo->storeAs('passports', $filename, 'public');

        return 'storage/' . $path;
    }

    protected function deletePhoto(string $photoPath): void
    {
        $path = str_replace('storage/', '', $photoPath);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function getFilteredStudents(array $filters): \Illuminate\Database\Eloquent\Collection
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

        return $query->orderBy('student_name')->get();
    }
}
