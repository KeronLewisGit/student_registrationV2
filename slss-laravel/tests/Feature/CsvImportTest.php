<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CsvImportTest extends TestCase
{
    private function csv(string $content): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('import.csv', $content);
    }

    public function test_template_round_trips_and_duplicates_are_skipped_not_fatal(): void
    {
        $admin = $this->admin();
        $template = $this->actingAs($admin)->get('/import/template')->assertOk()->streamedContent();

        $this->actingAs($admin)->post('/import', ['csv_file' => $this->csv($template)])->assertRedirect();
        $this->assertSame(1, Student::count());

        $deleted = $this->student(['student_name' => 'Gone Kid', 'student_birth_certificate_pin' => 'GONE123']);
        $deleted->delete();

        $csv = "student_name,student_birth_certficate_pin,student_dob,form_1_class\n"
             . "Gone Kid,GONE123,2013-05-04,1B\n"
             . "Jane Doe,,2012-05-04,1A\n"
             . "Fresh Kid,,2011-01-01,1A\n";

        $response = $this->actingAs($admin)->post('/import', ['csv_file' => $this->csv($csv)])->assertRedirect();
        $errors = session('import_errors');
        $this->assertCount(2, $errors, 'Deleted-PIN row and same-name-and-DOB row are both skipped');
        $this->assertSame(1, Student::where('student_name', 'Fresh Kid')->count());
        $this->assertSame(1, Student::where('student_name', 'Jane Doe')->count());
    }
}
