<?php

namespace App\Http\Controllers;

use App\Services\CsvImportService;
use Illuminate\Http\Request;

class ImportController extends Controller
{
    public function __construct(
        protected CsvImportService $importService
    ) {
        $this->middleware('can:import-students');
    }

    public function index()
    {
        return view('students.import', [
            'expectedHeaders' => $this->importService->getExpectedHeaders(),
        ]);
    }

    /**
     * Download a blank CSV template containing the exact headers the
     * importer expects, plus one example row.
     */
    public function template()
    {
        $headers = $this->importService->getExpectedHeaders();

        $callback = function () use ($headers) {
            $handle = fopen('php://output', 'w');

            // BOM so Excel opens the file as UTF-8.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, $headers);
            fputcsv($handle, $this->exampleRow($headers));

            fclose($handle);
        };

        return response()->streamDownload($callback, 'student_import_template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Build a single example row demonstrating the expected value formats.
     *
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    protected function exampleRow(array $headers): array
    {
        $examples = [
            'form_1_class' => '1A',
            'student_name' => 'Jane Doe',
            'student_gender' => 'Female',
            'citizen_type' => 'Birth',
            'student_current_address' => '12 Main Street, Port of Spain',
            'student_dob' => '2012-05-04',
            'student_birth_certficate_pin' => '20120504123',
            'student_religion' => 'Roman Catholic',
            'student_country_of_birth' => 'Trinidad and Tobago',
            'student_nationality' => 'Trinidadian',
            'student_ethnicity' => 'Mixed',
            'student_contact' => '(868)123-4567',
            'student_email' => 'jane.doe@example.com',
            'student_sea_date' => '2024-04-01',
            'student_primary_school' => 'Example Primary School',
            'student_sea_number' => 'SEA123456',
            'student_transfer_status' => 'No',
            'student_bloodtype' => 'O+',
            'student_immunization_status' => 'Up to date',
            'student_school_feeding_option' => 'No',
            'student_social_welfare_status' => 'No',
            'student_mode_of_transport' => 'Maxi-Taxi',
            'student_access_to_device' => 'Laptop',
            'mother_name' => 'Mary Doe',
            'is_mother_active_or_deceased' => 'Alive',
            'mother_contact' => '(868)765-4321',
            'mother_email' => 'mary.doe@example.com',
            'is_father_active_or_deceased' => 'Alive',
            'father_name' => 'John Doe',
            'father_contact' => '(868)765-1234',
            'father_email_address' => 'john.doe@example.com',
            'emergency_contact_name' => 'Anne Smith',
            'emergency_contact_relation_to_student' => 'Aunt',
            'emergency_contact_number' => '(868)555-0000',
            'registration_date' => now()->format('Y-m-d'),
            'registrant_relationship_to_student' => 'Mother',
            'registrant_name' => 'Mary Doe',
            'registrant_nationality' => 'Trinidadian',
            'registrant_email' => 'mary.doe@example.com',
        ];

        return array_map(fn ($header) => $examples[$header] ?? '', $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240'
        ]);

        try {
            $result = $this->importService->import($request->file('csv_file'));

            return back()->with('success',
                "Import completed successfully. Imported: {$result['imported']}, Skipped: {$result['skipped']}"
            );

        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
