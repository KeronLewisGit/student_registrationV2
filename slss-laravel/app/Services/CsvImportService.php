<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CsvImportService
{
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

    public function import(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            throw new \RuntimeException('Could not open CSV file');
        }

        // Read and process header
        $header = fgetcsv($handle);

        // Remove BOM if present
        if ($header && isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        $header = array_map('trim', $header);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < count($header)) {
                    continue;
                }

                $data = array_combine($header, $row);
                $data = array_map(function($value) {
                    $value = trim($value);
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

    protected function mapCsvToStudentData(array $data): array
    {
        $result = [];

        foreach ($this->mapping as $csvKey => $dbKey) {
            $result[$dbKey] = $data[$csvKey] ?? null;
        }

        // Normalize PIN
        if (!empty($result['student_birth_certificate_pin'])) {
            $pin = strtoupper(preg_replace('/[^0-9A-Z]/', '', $result['student_birth_certificate_pin']));
            $result['student_birth_certificate_pin'] = $pin ?: null;
        }

        // Convert dates
        $dateFields = ['student_dob', 'student_sea_date', 'student_transfer_date', 'registration_date'];

        foreach ($dateFields as $dateField) {
            if (!empty($result[$dateField])) {
                $timestamp = strtotime($result[$dateField]);
                $result[$dateField] = $timestamp ? date('Y-m-d', $timestamp) : null;
            }
        }

        return $result;
    }
}
