<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CsvImportService
{
    /**
     * Columns declared NOT NULL with an empty-string default.
     *
     * A blank CSV cell must be omitted rather than sent as null, or the
     * insert violates the constraint and aborts the whole import.
     *
     * @var array<int, string>
     */
    protected const NOT_NULL_COLUMNS = [
        'student_family_crisis',
        'student_receiving_counselling',
        'student_physical_disabilities',
        'student_learning_disabilities',
        'student_educational_aid',
        'student_special_sea_concessions',
        'student_emotional_factors',
        'student_other_intervention_information',
    ];

    /**
     * Mapping of CSV column names to database column names.
     *
     * @var array
     */
    protected array $mapping = [
        'student_passport_photo' => 'student_passport_photo',
        'form_1_class' => 'form_1_class',
        'student_name' => 'student_name',
        'student_gender' => 'student_gender',
        'citizen_type' => 'citizen_type',
        'student_current_address' => 'student_current_address',
        'student_dob' => 'student_dob',
        'student_birth_certificate' => 'student_birth_certificate',
        'student_birth_certficate_pin' => 'student_birth_certificate_pin', // Note: old typo mapped correctly
        'student_religion' => 'student_religion',
        'student_country_of_birth' => 'student_country_of_birth',
        'student_nationality' => 'student_nationality',
        'student_ethnicity' => 'student_ethnicity',
        'student_contact' => 'student_contact',
        'student_email' => 'student_email',
        'student_sea_date' => 'student_sea_date',
        'student_primary_school' => 'student_primary_school',
        'student_sea_slip' => 'student_sea_slip',
        'student_sea_number' => 'student_sea_number',
        'student_transfer_status' => 'student_transfer_status',
        'student_transfer_slip' => 'student_transfer_slip',
        'student_transfer_date' => 'student_transfer_date',
        'student_previous_secondary_school' => 'student_previous_secondary_school',
        'student_previous_school_location' => 'student_previous_school_location',
        'student_medical_condition' => 'student_medical_condition',
        'student_bloodtype' => 'student_bloodtype',
        'student_allergies' => 'student_allergies',
        'student_immunization_status' => 'student_immunization_status',
        'student_family_crisis' => 'student_family_crisis',
        'student_recieving_counselling' => 'student_receiving_counselling',
        'student_physical_disibilities' => 'student_physical_disabilities',
        'student_learning_disabilities' => 'student_learning_disabilities',
        'student_educational_aid' => 'student_educational_aid',
        'student_special_sea_concessions' => 'student_special_sea_concessions',
        'student_emotional_factors' => 'student_emotional_factors',
        'student_other_intervention_information' => 'student_other_intervention_information',
        'student_school_feeding_option' => 'student_school_feeding_option',
        'student_social_welfare_status' => 'student_social_welfare_status',
        'student_mode_of_transport' => 'student_mode_of_transport',
        'student_access_to_device' => 'student_access_to_device',
        'mother_name' => 'mother_name',
        'is_mother_active_or_deceased' => 'is_mother_active_or_deceased',
        'mother_identification_type' => 'mother_identification_type',
        'mother_identification_number' => 'mother_identification_number',
        'mother_home_address' => 'mother_home_address',
        'mother_contact' => 'mother_contact',
        'mother_profession' => 'mother_profession',
        'mother_work_address' => 'mother_work_address',
        'mother_email' => 'mother_email',
        'is_father_active_or_deceased' => 'is_father_active_or_deceased',
        'father_name' => 'father_name',
        'father_identification_type' => 'father_identification_type',
        'father_identification_number' => 'father_identification_number',
        'father_home_address' => 'father_home_address',
        'father_contact' => 'father_contact',
        'father_profession' => 'father_profession',
        'father_work_address' => 'father_work_address',
        'father_email_address' => 'father_email_address',
        'emergency_contact_name' => 'emergency_contact_name',
        'emergency_contact_address' => 'emergency_contact_address',
        'emergency_contact_relation_to_student' => 'emergency_contact_relation_to_student',
        'emergency_contact_number' => 'emergency_contact_number',
        'registration_date' => 'registration_date',
        'registrant_relationship_to_student' => 'registrant_relationship_to_student',
        'registrant_name' => 'registrant_name',
        'registrant_identification_type' => 'registrant_identification_type',
        'registrant_identification_number' => 'registrant_identification_number',
        'registrant_nationality' => 'registrant_nationality',
        'registrant_email' => 'registrant_email',
    ];

    /**
     * Get the CSV column headers this importer accepts, in order.
     *
     * These are the exact header names the import expects. Some retain
     * historical misspellings (e.g. student_birth_certficate_pin) which
     * must be preserved or those columns are ignored on import.
     *
     * @return array<int, string>
     */
    public function getExpectedHeaders(): array
    {
        return array_keys($this->mapping);
    }

    /**
     * Import student records from a CSV file.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return array
     * @throws \RuntimeException
     */
    public function import(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            throw new \RuntimeException('Could not open CSV file');
        }

        // Read and process header
        $header = fgetcsv($handle);

        if ($header === false || $header === [null]) {
            fclose($handle);
            throw new \RuntimeException('The CSV file is empty.');
        }

        // Remove BOM if present
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        }

        $header = array_map(fn ($h) => trim((string) $h), $header);

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1; // header row

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Skip completely blank lines without counting them
                if ($row === [null] || implode('', array_map('strval', $row)) === '') {
                    continue;
                }

                if (count($row) < count($header)) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: fewer columns than the header — skipped.";
                    continue;
                }

                // Extra trailing columns (stray commas) would make array_combine throw
                $row = array_slice($row, 0, count($header));

                $data = array_combine($header, $row);
                $data = array_map(function($value) {
                    $value = trim((string) $value);
                    $value = str_replace('\/', '/', $value);
                    return $value === '' ? null : $value;
                }, $data);

                // Map CSV columns to database columns
                $studentData = $this->mapCsvToStudentData($data);

                // Validate required fields
                $validator = Validator::make($studentData, [
                    'student_name' => 'required',
                ]);

                if ($validator->fails()) {
                    $skipped++;
                    continue;
                }

                // Check for duplicate by PIN
                $pin = $studentData['student_birth_certificate_pin'] ?? null;
                if ($pin) {
                    $existing = Student::where('student_birth_certificate_pin', $pin)->first();
                    if ($existing) {
                        $skipped++;
                        continue;
                    }
                }

                Student::create($studentData);
                $imported++;
            }

            DB::commit();
            fclose($handle);

            return [
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);

            throw $e;
        }
    }

    /**
     * Map CSV data to student model attributes.
     *
     * @param  array  $data
     * @return array
     */
    protected function mapCsvToStudentData(array $data): array
    {
        $result = [];

        foreach ($this->mapping as $csvKey => $dbKey) {
            $result[$dbKey] = $data[$csvKey] ?? null;
        }

        // Normalize PIN (uppercase FIRST, or lowercase letters get stripped)
        if (!empty($result['student_birth_certificate_pin'])) {
            $pin = preg_replace('/[^0-9A-Z]/', '', strtoupper($result['student_birth_certificate_pin']));
            $result['student_birth_certificate_pin'] = $pin ?: null;
        }

        // Convert dates
        $dateFields = ['student_dob', 'student_sea_date', 'student_transfer_date', 'registration_date'];

        foreach ($dateFields as $dateField) {
            if (!empty($result[$dateField])) {
                $result[$dateField] = $this->parseDate($result[$dateField]);
            }
        }

        // These columns are NOT NULL with an empty-string default. Drop the key
        // when blank so the database default applies instead of inserting null.
        foreach (self::NOT_NULL_COLUMNS as $column) {
            if (!isset($result[$column])) {
                unset($result[$column]);
            }
        }

        return $result;
    }

    /**
     * Parse a CSV date as day-first (d/m/Y — the format this app displays and
     * exports everywhere) with ISO Y-m-d as fallback. strtotime() must not be
     * used here: it reads 05/06/2012 as US month-first and silently swaps
     * day and month.
     */
    protected function parseDate(string $value): ?string
    {
        $value = trim($value);

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            $parseErrors = \DateTime::getLastErrors();
            $clean = $parseErrors === false
                || ($parseErrors['warning_count'] === 0 && $parseErrors['error_count'] === 0);

            if ($date !== false && $clean) {
                return $date->format('Y-m-d');
            }
        }

        // Last resort for verbose formats like "5 June 2012"
        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}
