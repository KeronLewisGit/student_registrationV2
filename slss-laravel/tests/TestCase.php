<?php

namespace Tests;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'admin@test.local', 'password' => bcrypt('Password-123456'), 'role' => 'admin']);
    }

    protected function staff(): User
    {
        return User::create(['name' => 'Staff', 'email' => 'staff@test.local', 'password' => bcrypt('Password-123456'), 'role' => 'staff']);
    }

    protected function viewer(): User
    {
        return User::create(['name' => 'Viewer', 'email' => 'viewer@test.local', 'password' => bcrypt('Password-123456'), 'role' => 'viewer']);
    }

    /**
     * A student with sensible defaults; override anything via $attributes.
     */
    protected function student(array $attributes = []): Student
    {
        return Student::create(array_merge([
            'student_name' => 'Test Student',
            'form_1_class' => '1C',
            'student_gender' => 'Female',
            'student_dob' => '2013-04-05',
            'registration_date' => '2024-07-01',
            'student_birth_certificate_pin' => 'TP' . str_pad((string) random_int(1, 999999999), 9, '0', STR_PAD_LEFT),
        ], $attributes));
    }
}
