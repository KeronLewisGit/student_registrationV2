<?php

namespace Tests\Feature;

use App\Models\Student;
use Tests\TestCase;

class PrintAndCompletenessTest extends TestCase
{
    public function test_placeholders_and_blanks_print_as_not_recorded(): void
    {
        $s = $this->student([
            'student_bloodtype' => 'Select Blood Type', 'father_home_address' => 'N/a N/a N/a',
            'student_transfer_status' => 'No', 'student_transfer_date' => '1900-01-01', 'student_allergies' => '',
        ]);
        $admin = $this->admin();

        $html = $this->actingAs($admin)->get('/students/' . $s->id . '/print')->assertOk()->getContent();
        $this->assertStringNotContainsString('Select Blood Type', $html);
        $this->assertStringNotContainsString('N/a N/a', $html);
        $this->assertStringNotContainsString('1900', $html);
        $this->assertStringContainsString('Not a transfer student', $html);
        $this->assertStringContainsString('Class ' . $s->current_class, $html);

        preg_match_all('#<h5>[^<]*</h5>\s*<p>(.*?)</p>#s', $html, $m);
        $this->assertGreaterThan(50, count($m[1]));
        foreach ($m[1] as $cell) {
            $this->assertNotSame('', trim(strip_tags($cell)), 'No printed field may be blank');
        }
    }

    public function test_completeness_reports_missing_essentials(): void
    {
        $s = $this->student(['student_passport_photo' => null, 'emergency_contact_name' => null]);
        $c = $s->completeness();

        $this->assertArrayHasKey('photo', $c['missing']);
        $this->assertArrayHasKey('emergency', $c['missing']);
        $this->assertArrayNotHasKey('dob', $c['missing']);
        $this->assertGreaterThan(0, $c['percent']);
        $this->assertLessThan(100, $c['percent']);

        $this->actingAs($this->admin())->get('/students?incomplete=1')->assertOk()->assertSee($s->student_name);
    }

    public function test_completeness_colour_follows_the_percentage(): void
    {
        $this->assertSame('low', \App\Models\Student::completenessLevel(58));
        $this->assertSame('mid', \App\Models\Student::completenessLevel(60));
        $this->assertSame('mid', \App\Models\Student::completenessLevel(84));
        $this->assertSame('ok', \App\Models\Student::completenessLevel(85));
    }

    public function test_printables_render_for_a_class(): void
    {
        $s = $this->student(['registration_date' => '2024-07-01', 'student_allergies' => 'Peanuts']); // 3C in 2026
        $admin = $this->admin();

        foreach (['register', 'contacts', 'medical', 'birthdays', 'outstanding'] as $key) {
            $this->actingAs($admin)->get('/printables/' . $key . '?class=' . $s->current_class)->assertOk()->assertSee($s->student_name);
        }
        $this->actingAs($admin)->get('/printables/medical?class=all')->assertSee('Peanuts');
        $this->actingAs($admin)->get('/printables/nonsense')->assertNotFound();
    }

    public function test_pdf_generates(): void
    {
        $s = $this->student(['student_bloodtype' => 'Select Blood Type']);
        $this->actingAs($this->admin())->get('/students/' . $s->id . '/pdf')->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
