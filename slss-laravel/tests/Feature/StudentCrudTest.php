<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentActivity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentCrudTest extends TestCase
{
    public function test_create_with_blank_special_needs_fields_does_not_crash(): void
    {
        $this->actingAs($this->admin())
            ->post('/students', [
                'student_first_name' => 'Blank', 'student_last_name' => 'Needs',
                'form_1_class' => '1A', 'student_dob' => '2013-01-02',
                'student_family_crisis' => '', 'student_educational_aid' => '',
            ])->assertRedirect();

        $student = Student::where('student_name', 'Blank Needs')->firstOrFail();
        $this->assertSame('', $student->student_family_crisis);
        $this->assertSame('active', $student->enrolment_status);
        $this->assertSame((int) date('Y'), $student->intake_year);
        $this->assertNotNull($student->current_class);
    }

    public function test_updating_with_blank_fields_saves_and_unchanged_resave_logs_nothing(): void
    {
        $student = $this->student(['student_family_crisis' => '']);
        $admin = $this->admin();

        $this->actingAs($admin)->put('/students/' . $student->id, [
            'student_first_name' => 'Test', 'student_last_name' => 'Student',
            'form_1_class' => '1C', 'student_dob' => '2013-04-05', 'registration_date' => '2024-07-01',
            'student_birth_certificate_pin' => $student->student_birth_certificate_pin,
            'student_family_crisis' => '', 'student_receiving_counselling' => '',
        ])->assertRedirect('/students/' . $student->id);

        $this->assertSame(0, StudentActivity::where('student_id', $student->id)->where('action', 'updated')->count(),
            'An unchanged re-save must not produce an audit entry');
    }

    public function test_duplicate_pin_and_future_dob_are_rejected(): void
    {
        $existing = $this->student();

        $this->actingAs($this->admin())->post('/students', [
            'student_first_name' => 'Dup', 'student_last_name' => 'Pin',
            'student_birth_certificate_pin' => $existing->student_birth_certificate_pin,
            'student_dob' => now()->addYear()->format('Y-m-d'),
        ])->assertSessionHasErrors(['student_birth_certificate_pin', 'student_dob']);
    }

    public function test_soft_delete_keeps_files_and_restore_is_logged(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/students', [
            'student_first_name' => 'Photo', 'student_last_name' => 'Kid', 'form_1_class' => '1B',
            'student_passport_photo' => UploadedFile::fake()->image('anything.png', 40, 40),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $student = Student::where('student_name', 'Photo Kid')->firstOrFail();
        $this->assertMatchesRegularExpression('#^private/passports/student_new_[A-Za-z0-9]{12}\.(png|jpg|gif)$#', $student->student_passport_photo,
            'Extension must come from the detected MIME type, not the client file name');
        Storage::disk('local')->assertExists($student->student_passport_photo);

        $this->actingAs($admin)->delete('/students/' . $student->id)->assertRedirect('/students');
        Storage::disk('local')->assertExists($student->student_passport_photo);
        $this->actingAs($admin)->get('/students/' . $student->id)->assertNotFound();

        $this->actingAs($admin)->post('/students/' . $student->id . '/restore')->assertRedirect();
        $this->assertNull($student->fresh()->deleted_at);
        $this->assertEqualsCanonicalizing(['created', 'deleted', 'restored'],
            StudentActivity::where('student_id', $student->id)->pluck('action')->unique()->values()->all());
    }

    public function test_activity_log_redacts_sensitive_fields_and_records_user(): void
    {
        $student = $this->student();
        $admin = $this->admin();

        $this->actingAs($admin);
        $student->update(['student_bloodtype' => 'Blood Group A', 'student_contact' => '868-555-0100']);

        $entry = StudentActivity::where('student_id', $student->id)->where('action', 'updated')->firstOrFail();
        $this->assertSame('Admin', $entry->user_name);
        $this->assertSame('[redacted]', $entry->changes['student_bloodtype']['to']);
        $this->assertSame('868-555-0100', $entry->changes['student_contact']['to']);
    }

    public function test_search_finds_by_parent_phone_and_registrant(): void
    {
        $this->student(['student_name' => 'Alpha One', 'mother_contact' => '868-555-0199', 'registrant_name' => 'Jevanni Clairmont']);
        $this->student(['student_name' => 'Beta Two']);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/students?search=8685550199')->assertSee('Alpha One')->assertDontSee('Beta Two');
        $this->actingAs($admin)->get('/students?search=jevanni')->assertSee('Alpha One')->assertDontSee('Beta Two');
        $this->actingAs($admin)->get('/students?search=alpha%20868')->assertSee('Alpha One')->assertDontSee('Beta Two');
    }

    public function test_advanced_filters_narrow_the_list_and_the_export(): void
    {
        $this->student(['student_name' => 'Girl Hindu', 'student_gender' => 'Female', 'student_religion' => 'Hindu']);
        $this->student(['student_name' => 'Girl Catholic', 'student_gender' => 'Female', 'student_religion' => 'Roman Catholic']);
        $this->student(['student_name' => 'Boy Hindu', 'student_gender' => 'Male', 'student_religion' => 'hindu']);
        $admin = $this->admin();

        $this->actingAs($admin)->get('/students?f[student_gender]=Female')
            ->assertOk()->assertSee('Girl Hindu')->assertSee('Girl Catholic')->assertDontSee('Boy Hindu')
            ->assertSee('Gender: Female');

        $this->actingAs($admin)->get('/students?f[student_gender]=Female&f[student_religion]=Hindu')
            ->assertOk()->assertSee('Girl Hindu')->assertDontSee('Girl Catholic')->assertDontSee('Boy Hindu');

        // Case-insensitive match and unknown columns ignored
        $this->actingAs($admin)->get('/students?f[student_religion]=HINDU&f[password]=x')
            ->assertOk()->assertSee('Girl Hindu')->assertSee('Boy Hindu')->assertDontSee('Girl Catholic');

        $response = $this->actingAs($admin)->get('/reports/all-students/export?format=csv&f[student_gender]=Male&columns[]=student_name');
        $response->assertOk();
        $csv = file_get_contents($response->baseResponse->getFile()->getPathname());
        $this->assertStringContainsString('Boy Hindu', $csv);
        $this->assertStringNotContainsString('Girl Hindu', $csv);
    }
}
