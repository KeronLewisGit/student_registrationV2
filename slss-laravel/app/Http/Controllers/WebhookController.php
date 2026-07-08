<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class WebhookController extends Controller
{
    /**
     * Handle incoming student registration data from Elementor form
     */
    public function handleStudentRegistration(Request $request)
    {
        try {
            // Log incoming data for debugging
            Log::info('Webhook received', [
                'fields' => $request->input('fields'),
                'ip' => $request->ip()
            ]);

            // Extract and process the data
            $studentData = $this->extractStudentData($request);

            // Create the student record
            $student = Student::create($studentData);

            Log::info('Student created successfully', ['student_id' => $student->id]);

            return response()->json([
                'success' => true,
                'message' => 'Student registered successfully',
                'student_id' => $student->id
            ], 200);

        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving registration'
            ], 500);
        }
    }

    /**
     * Extract and map Elementor form fields to Student model attributes
     */
    private function extractStudentData(Request $request): array
    {
        return [
            // Basic Information
            'student_passport_photo' => $this->field($request, 'student_passport'),
            'form_1_class' => $this->field($request, 'student_class'),
            'student_name' => $this->multiField($request, ['student_first_name', 'student_last_name']),
            'student_gender' => $this->field($request, 'student_gender'),
            'citizen_type' => $this->field($request, 'student_citizenship_type'),
            'student_current_address' => $this->multiField($request, [
                'student_house_no', 'student_address_line1',
                'student_community', 'student_village',
                'student_city', 'student_corporartion'
            ]),
            'student_dob' => $this->field($request, 'student_dob', '1900-01-01'),
            'student_birth_certificate' => $this->field($request, 'student_birth_certificate'),
            'student_birth_certificate_pin' => $this->field($request, 'student_birth_pin'),
            'student_religion' => $this->field($request, 'student_religion'),
            'student_country_of_birth' => $this->field($request, 'student_country_of_birth'),
            'student_nationality' => $this->conditionalField($request, 'student_nationality', 'Other', 'other_student_nationality'),
            'student_ethnicity' => $this->field($request, 'student_ethnicity'),
            'student_contact' => $this->field($request, 'student_contact_no'),
            'student_email' => $this->field($request, 'student_email'),

            // SEA Information
            'student_sea_date' => $this->field($request, 'student_sea_date', '1900-01-01'),
            'student_primary_school' => $this->field($request, 'student_primary_school'),
            'student_sea_slip' => $this->field($request, 'student_sea_slip'),
            'student_sea_number' => $this->field($request, 'student_sea_number'),

            // Transfer Information
            'student_transfer_status' => $this->field($request, 'transfer_status'),
            'student_transfer_slip' => $this->field($request, 'student_transfer_slip'),
            'student_transfer_reason' => $this->field($request, 'transferreason'),
            'student_transfer_date' => $this->field($request, 'student_transfer_year', '1900-01-01'),
            'student_previous_form_class' => $this->field($request, 'previous_form_class'),
            'student_previous_secondary_school' => $this->field($request, 'student_transfer_school'),
            'student_previous_school_location' => $this->multiField($request, [
                'transfer_address_line1', 'transfer_city', 'transfer_village'
            ]),

            // Medical Information
            'student_medical_condition' => $this->field($request, 'student_medical_condition'),
            'student_bloodtype' => $this->field($request, 'student_blood_type'),
            'student_allergies' => $this->field($request, 'student_allergies'),
            'student_immunization_status' => $this->field($request, 'student_immunisation_status'),

            // Special Needs & Intervention
            'student_family_crisis' => $this->conditionalField($request, 'student_family_crisis', 'Other', 'student_other_crisis'),
            'student_receiving_counselling' => $this->conditionalValue($request, 'recieved_counselling', 'Yes', 'counselling_explanation'),
            'student_physical_disabilities' => $this->conditionalValue($request, 'physical_disabilities', 'Yes', 'stated_physical_disabilities'),
            'student_learning_disabilities' => $this->conditionalValue($request, 'learning_disabilities', 'Yes', 'stated_learning_disabilities'),
            'student_educational_aid' => $this->field($request, 'educational_aid'),
            'student_special_sea_concessions' => $this->field($request, 'special_concessions'),
            'student_emotional_factors' => $this->conditionalValue($request, 'developmental_factors', 'Yes', 'stated_developmental_factors'),
            'student_other_intervention_information' => $this->field($request, 'other_intervention_information'),

            // Personal Preferences
            'student_school_feeding_option' => $this->fieldAny($request, [
                'school_feeding_programme', 'field_2d982f3', 'student_school_feeding_option'
            ]),
            'student_social_welfare_status' => $this->field($request, 'student_social_services'),
            'student_social_welfare_detail' => $this->conditionalValue($request, 'student_social_services', 'Yes', 'welfare_services_answer'),
            'student_mode_of_transport' => $this->field($request, 'student_transport_method'),
            'student_access_to_device' => $this->deviceAccess($request),
            'student_device_shared' => $this->field($request, 'is_used_others'),
            'student_reliable_internet' => $this->field($request, 'reliable_internet'),
            'student_internet_provider' => $this->conditionalField($request, 'internet_provider', 'Other', 'other_provider'),
            'student_online_tools' => $this->field($request, 'online_tools'),

            // Mother Information
            'is_mother_active_or_deceased' => $this->field($request, 'mother_living_status') === 'Deceased' ? 'Deceased' : 'Alive',
            'mother_death_certificate' => $this->field($request, 'mother_living_status') === 'Deceased'
                ? $this->fieldAny($request, ['mother_death_certificate', 'field_ed0fb29'])
                : 'N/A',
            'mother_name' => $this->multiField($request, ['mother_first_name', 'mother_last_name']),
            'mother_identification_type' => $this->field($request, 'mother_identification'),
            'mother_identification_number' => $this->field($request, 'mother_identification_number'),
            'mother_home_address' => $this->multiField($request, [
                'mother_house_no', 'mother_address_line1',
                'mother_community', 'mother_village',
                'mother_city', 'mother_corporartion'
            ]),
            'mother_contact' => $this->field($request, 'mother_contact'),
            'mother_profession' => $this->field($request, 'mother_profession'),
            'mother_work_address' => $this->multiField($request, [
                'mother_work_address_line1', 'mother_work_city', 'mother_work_village'
            ]),
            'mother_email' => $this->field($request, 'mother_email'),

            // Father Information
            'is_father_active_or_deceased' => $this->field($request, 'father_living_status') === 'Deceased' ? 'Deceased' : 'Alive',
            'father_death_certificate' => $this->field($request, 'father_living_status') === 'Deceased'
                ? $this->fieldAny($request, ['father_death_certificate', 'field_f9d5c85'])
                : 'N/A',
            'father_name' => $this->multiField($request, ['father_first_name', 'father_last_name']),
            'father_identification_type' => $this->field($request, 'father_identification_type'),
            'father_identification_number' => $this->field($request, 'father_identification_no'),
            'father_home_address' => $this->multiField($request, [
                'father_house_no', 'father_address_line1',
                'father_community', 'father_village',
                'father_city', 'father_corporartion'
            ]),
            'father_contact' => $this->field($request, 'father_contact'),
            'father_profession' => $this->field($request, 'father_profession'),
            'father_work_address' => $this->multiField($request, [
                'father_work_address_line1', 'father_work_city', 'father_work_village'
            ]),
            'father_email_address' => $this->field($request, 'father_email'),

            // Emergency Contact
            'emergency_contact_name' => $this->multiField($request, ['emergency_first_name', 'emergency_last_name']),
            'emergency_contact_address' => $this->multiField($request, [
                'emergency_address_line1', 'emergency_city', 'emergency_village'
            ]),
            'emergency_contact_relation_to_student' => $this->conditionalField($request, 'emergency_relation', 'Other', 'other_emergency_contact'),
            'emergency_contact_number' => $this->field($request, 'emergency_contact'),

            // Registrant Information
            'registration_date' => $this->field($request, 'registrant_date', now()->format('Y-m-d')),
            'registrant_relationship_to_student' => $this->conditionalField($request, 'registrant_relationsip_to_student', 'Other', 'registrant_other_relationship'),
            'registrant_name' => $this->multiField($request, ['registrant_first_name', 'registrant_last_name']),
            'registrant_identification_type' => $this->field($request, 'registrant_identification_type'),
            'registrant_identification_number' => $this->field($request, 'registrant_identification_number'),
            'registrant_nationality' => $this->conditionalField($request, 'registrant_nationality', 'Other', 'registrant_other_nationality'),
            'registrant_email' => $this->field($request, 'registrant_email'),
        ];
    }

    /**
     * Get field value or default
     */
    private function field(Request $request, string $key, string $default = 'N/A'): string
    {
        $value = $request->input("fields.{$key}.value");

        if (is_array($value)) {
            $value = implode(', ', array_filter(array_map('strval', $value), fn($x) => trim($x) !== ''));
        }

        return $this->hasValue($value) ? trim((string)$value) : $default;
    }

    /**
     * Get first non-blank value from multiple possible field keys
     */
    private function fieldAny(Request $request, array $keys, string $default = 'N/A'): string
    {
        foreach ($keys as $key) {
            $value = $request->input("fields.{$key}.value");

            if (is_array($value)) {
                $value = implode(', ', array_filter(array_map('strval', $value), fn($x) => trim($x) !== ''));
            }

            if ($this->hasValue($value)) {
                return trim((string)$value);
            }
        }

        return $default;
    }

    /**
     * Concatenate multiple fields, skipping blanks
     */
    private function multiField(Request $request, array $keys, string $default = 'N/A', string $separator = ' '): string
    {
        $values = [];

        foreach ($keys as $key) {
            $value = $request->input("fields.{$key}.value");

            if (is_array($value)) {
                $value = implode(' ', array_map('strval', $value));
            }

            if ($this->hasValue($value)) {
                $values[] = trim((string)$value);
            }
        }

        return $values ? implode($separator, $values) : $default;
    }

    /**
     * Return alternate field value when main field equals specific value
     */
    private function conditionalField(Request $request, string $mainKey, string $matchValue, string $alternateKey): string
    {
        $mainValue = $this->field($request, $mainKey);
        return $mainValue === $matchValue ? $this->field($request, $alternateKey) : $mainValue;
    }

    /**
     * Return alternate field value when condition field equals specific value, otherwise N/A
     */
    private function conditionalValue(Request $request, string $conditionKey, string $matchValue, string $valueKey): string
    {
        return $this->field($request, $conditionKey) === $matchValue
            ? $this->field($request, $valueKey)
            : 'N/A';
    }

    /**
     * Handle device access field logic
     */
    private function deviceAccess(Request $request): string
    {
        $device = $this->field($request, 'student_device');
        if ($device === 'Other') {
            return $this->field($request, 'student_device_other');
        }
        return $this->fieldAny($request, ['student_device', 'student_continuos_access']);
    }

    /**
     * Check if value is present and not blank
     */
    private function hasValue($value): bool
    {
        if (is_array($value)) {
            $value = implode('', array_map('strval', $value));
        }

        return $value !== null && trim((string)$value) !== '';
    }
}
