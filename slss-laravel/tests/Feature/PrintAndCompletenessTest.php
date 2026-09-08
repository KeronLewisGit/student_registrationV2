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
        $this->assertSame(100 - (int) round(count($c['missing']) / $c['essentials_total'] * 100), $c['percent'],
            'The percentage is the share of essential items, so it always agrees with the missing count');
        $this->assertGreaterThan(0, $c['fields_percent']);
        $this->assertLessThan(100, $c['fields_percent']);

        $this->actingAs($this->admin())->get('/students?incomplete=1')->assertOk()->assertSee($s->student_name);
    }

    public function test_photo_counts_only_when_it_is_a_displayable_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::disk('local')->put('private/passports/real.png', 'png');

        $cases = [
            'https://slss.edu.tt/wp-content/uploads/forms/scan.pdf' => false, // PDF is not a photo
            'https://slss.edu.tt/wp-content/uploads/forms/face.jpg' => true,
            'https://evil.example/face.jpg' => false,                        // unknown host
            'private/passports/real.png' => true,
            'private/passports/gone.png' => false,                           // file missing on disk
            'Yes' => false,
            null => false,
        ];
        foreach ($cases as $value => $expected) {
            $s = $this->student(['student_passport_photo' => $value === '' ? null : $value]);
            $this->assertSame($expected, $s->hasUsablePhoto(), "photo value: " . var_export($value, true));
            $this->assertSame(!$expected, array_key_exists('photo', $s->completeness()['missing']));
        }
    }

    public function test_completeness_colour_follows_the_percentage(): void
    {
        $this->assertSame('low', \App\Models\Student::completenessLevel(58));
        $this->assertSame('mid', \App\Models\Student::completenessLevel(60));
        $this->assertSame('mid', \App\Models\Student::completenessLevel(84));
        $this->assertSame('ok', \App\Models\Student::completenessLevel(85));
    }

    public function test_print_batch_and_export_follow_the_list_sort(): void
    {
        $this->student(['student_name' => 'Anna Zephyr', 'current_class' => '2A', 'student_dob' => '2012-01-01']);
        $this->student(['student_name' => 'Ben Adams', 'current_class' => '1B', 'student_dob' => '2014-01-01']);
        $this->student(['student_name' => 'Cara Miller', 'current_class' => '3C', 'student_dob' => '2013-01-01']);
        $admin = $this->admin();

        $order = function (string $html): array {
            preg_match_all('/record-subject">\s*([A-Za-z ]+?)\s*(?:&middot;|<)/', $html, $m);
            return array_map('trim', $m[1]);
        };

        $this->assertSame(['Anna Zephyr', 'Ben Adams', 'Cara Miller'], $order($this->actingAs($admin)->get('/students-print')->getContent()));
        $this->assertSame(['Ben Adams', 'Cara Miller', 'Anna Zephyr'], $order($this->actingAs($admin)->get('/students-print?names=last')->getContent()));
        $this->assertSame(['Cara Miller', 'Ben Adams', 'Anna Zephyr'], $order($this->actingAs($admin)->get('/students-print?sort=name&dir=desc')->getContent()));
        $this->assertSame(['Ben Adams', 'Anna Zephyr', 'Cara Miller'], $order($this->actingAs($admin)->get('/students-print?sort=class&dir=asc')->getContent()));
        $this->assertSame(['Ben Adams', 'Cara Miller', 'Anna Zephyr'], $order($this->actingAs($admin)->get('/students-print?sort=dob&dir=desc')->getContent()));
        $this->actingAs($admin)->get('/students-print?sort=dob&dir=desc')->assertSee('sorted by date of birth, youngest first');

        $response = $this->actingAs($admin)->get('/reports/all-students/export?format=csv&columns[]=student_name&sort=class&dir=desc');
        $csv = file_get_contents($response->baseResponse->getFile()->getPathname());
        $this->assertMatchesRegularExpression('/Cara Miller.*Anna Zephyr.*Ben Adams/s', $csv);
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
