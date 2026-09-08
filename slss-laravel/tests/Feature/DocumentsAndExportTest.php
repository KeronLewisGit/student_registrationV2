<?php

namespace Tests\Feature;

use App\Exports\StudentsExport;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentsAndExportTest extends TestCase
{
    public function test_private_documents_are_served_only_to_the_right_roles(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('private/passports/p.png', 'png-bytes');
        Storage::disk('local')->put('private/birth_certificates/b.pdf', 'pdf-bytes');

        $admin = $this->admin();
        $viewer = $this->viewer();

        $this->actingAs($admin)->get('/documents/private/passports/p.png')->assertOk();
        $this->actingAs($admin)->get('/documents/private/birth_certificates/b.pdf')->assertOk();

        // AuthenticateSession ties the session to the signed-in user; start a fresh one for the viewer
        $this->flushSession();
        $this->actingAs($viewer)->get('/documents/private/passports/p.png')->assertOk();
        $this->actingAs($viewer)->get('/documents/private/birth_certificates/b.pdf')->assertForbidden();

        $this->flushSession();
        $this->actingAs($admin)->get('/documents/private/passports/../../../.env')->assertNotFound();
        $this->actingAs($admin)->get('/documents/private/passports/missing.png')->assertNotFound();
    }

    public function test_document_url_only_trusts_the_school_host(): void
    {
        $this->assertNotNull(Student::documentUrl('https://slss.edu.tt/wp-content/uploads/x.jpg'));
        $this->assertNull(Student::documentUrl('https://evil.example/x.jpg'));
        $this->assertNull(Student::documentUrl('N/A'));
        $this->assertStringContainsString('/documents/private/passports/a.png', Student::documentUrl('private/passports/a.png'));
        $this->assertNull(Student::documentUrl('private/passports/../x.png'));
    }

    public function test_export_accepts_the_values_the_list_form_sends(): void
    {
        $this->student(['student_name' => 'Export Me']);
        $admin = $this->admin();

        $page = $this->actingAs($admin)->get('/students?year=&student_class=0&current_class=0&status=active&search=export')->assertOk();
        preg_match('#href="([^"]*all-students/export[^"]*)"#', $page->getContent(), $m);
        $link = html_entity_decode($m[1]);
        $this->assertStringNotContainsString('student_class=0', $link);

        $this->actingAs($admin)->get($link)->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_export_respects_chosen_columns(): void
    {
        $this->student(['student_name' => 'Column Kid', 'mother_contact' => '868-555-0123', 'student_bloodtype' => 'Select Blood Type']);
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/reports/all-students/export?format=csv&columns[]=student_name&columns[]=mother_contact&columns[]=student_bloodtype&columns[]=enrolment_status');
        $response->assertOk();
        $csv = file_get_contents($response->baseResponse->getFile()->getPathname()); // Excel::download returns a file response
        $lines = array_values(array_filter(explode("\n", trim($csv))));

        $this->assertStringContainsString('Student Name', $lines[0]);
        $this->assertStringContainsString("Mother's Contact", $lines[0]);
        $this->assertStringNotContainsString('Date of Birth', $lines[0]);
        $this->assertStringContainsString('Column Kid', $lines[1]);
        $this->assertStringContainsString('Active', $lines[1], 'Status is exported as its label');
        $this->assertStringNotContainsString('Select Blood Type', $lines[1], 'Placeholders are cleaned in exports');

        $this->actingAs($admin)->get('/reports/all-students/export?format=csv&columns[]=not_a_column')->assertSessionHasErrors('columns.0');
        $this->actingAs($admin)->get('/reports/all-students')->assertOk()->assertSee('Columns to include');
    }

    public function test_spreadsheet_formulas_are_neutralised(): void
    {
        $this->assertSame("'=HYPERLINK(\"x\")", StudentsExport::safeCell('=HYPERLINK("x")'));
        $this->assertSame("'+1", StudentsExport::safeCell('+1'));
        $this->assertSame('Jane Doe', StudentsExport::safeCell('Jane Doe'));
        $this->assertNull(StudentsExport::safeCell(null));
    }

    public function test_bulk_pdf_is_capped(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->student(['student_name' => "Bulk {$i}"]);
        }
        \App\Http\Controllers\StudentController::BULK_PDF_LIMIT; // constant exists
        $response = $this->actingAs($this->admin())->get('/students-bulk-pdf?progress_id=t1&search=nomatchxyz');
        $response->assertStatus(400); // no students
    }
}
