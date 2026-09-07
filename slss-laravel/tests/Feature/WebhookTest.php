<?php

namespace Tests\Feature;

use App\Models\Student;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    private function payload(array $fields): array
    {
        return ['fields' => array_map(fn ($v) => ['value' => $v], $fields)];
    }

    public function test_rejects_missing_or_wrong_token(): void
    {
        $this->postJson('/webhook/student-registration', $this->payload(['student_first_name' => 'X']))->assertStatus(401);
        $this->postJson('/webhook/student-registration?token=wrong', $this->payload(['student_first_name' => 'X']))->assertStatus(401);
        $this->assertSame(0, Student::count());
    }

    public function test_creates_a_clean_record_without_placeholders(): void
    {
        $this->postJson('/webhook/student-registration', $this->payload([
            'student_first_name' => 'Hook', 'student_last_name' => 'Tester',
            'student_class' => 'Form 1c', 'student_dob' => '03/02/2013',
            'student_birth_pin' => 'hk-2013 0203 01', 'student_email' => 'not-an-email',
            'student_sea_date' => 'Select Date', 'student_passport' => 'https://evil.example/x.jpg',
            'student_birth_certificate' => 'https://slss.edu.tt/wp-content/uploads/a.pdf',
        ]), ['X-Webhook-Token' => 'test-secret'])->assertOk()->assertJson(['success' => true]);

        $s = Student::where('student_name', 'Hook Tester')->firstOrFail();
        $this->assertSame('1C', $s->form_1_class);
        $this->assertSame('2013-02-03', $s->student_dob->format('Y-m-d'));
        $this->assertSame('HK2013020301', $s->student_birth_certificate_pin);
        $this->assertNull($s->student_email);
        $this->assertNull($s->student_sea_date);
        $this->assertNull($s->student_passport_photo, 'Links on unknown hosts are dropped');
        $this->assertSame('https://slss.edu.tt/wp-content/uploads/a.pdf', $s->student_birth_certificate);
        $this->assertNull($s->student_religion, 'Blank fields are null, not N/A');
        $this->assertSame('', $s->student_family_crisis);
        $this->assertSame('active', $s->enrolment_status);
        $this->assertNotNull($s->registration_date);
    }

    public function test_repeat_submission_merges_instead_of_duplicating(): void
    {
        $headers = ['X-Webhook-Token' => 'test-secret'];
        $this->postJson('/webhook/student-registration', $this->payload([
            'student_first_name' => 'Hook', 'student_last_name' => 'Tester', 'student_dob' => '2013-02-03', 'student_birth_pin' => 'HK1',
        ]), $headers)->assertOk();

        $this->postJson('/webhook/student-registration', $this->payload([
            'student_first_name' => 'Hook', 'student_last_name' => 'Tester', 'student_birth_pin' => 'HK1',
            'student_religion' => 'Hindu',
        ]), $headers)->assertOk()->assertJson(['duplicate' => true]);

        $this->assertSame(1, Student::where('student_name', 'Hook Tester')->count());
        $this->assertSame('Hindu', Student::first()->student_religion);
    }

    public function test_unparseable_date_does_not_lose_the_registration(): void
    {
        $this->postJson('/webhook/student-registration', $this->payload([
            'student_first_name' => 'Bad', 'student_last_name' => 'Date', 'student_dob' => 'whenever', 'registrant_date' => 'June 5 2026',
        ]), ['X-Webhook-Token' => 'test-secret'])->assertOk();

        $s = Student::where('student_name', 'Bad Date')->firstOrFail();
        $this->assertNull($s->student_dob);
        $this->assertSame('2026-06-05', $s->registration_date->format('Y-m-d'));
    }
}
