<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle incoming student registration data from Elementor form
     */
    public function handleStudentRegistration(Request $request)
    {
        if (!$this->hasValidToken($request)) {
            Log::warning('Webhook rejected: invalid or missing token', ['ip' => $request->ip()]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $fields = $request->input('fields');
        if (!is_array($fields)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payload: "fields" must be an object.',
            ], 422);
        }

        try {
            Log::info('Webhook received', [
                'field_count' => count($fields),
                'ip' => $request->ip()
            ]);

            // Extract, then sanitise (invalid emails/dates become null, PIN normalised,
            // class canonicalised) so a stray value never blocks the whole registration.
            $studentData = $this->sanitise($this->extractStudentData($request));

            // A family re-submitting the form must not create a second record.
            $existing = $this->findExisting($studentData);

            if ($existing) {
                $filled = $this->fillBlanks($existing, $studentData);
                StudentActivity::record(
                    $existing,
                    'updated',
                    'Registration form submitted again' . ($filled ? '; filled in ' . implode(', ', array_map([Student::class, 'fieldLabel'], $filled)) : '; no new information'),
                    null
                );

                Log::info('Webhook matched an existing student', ['student_id' => $existing->id, 'filled' => $filled]);

                return response()->json([
                    'success' => true,
                    'message' => 'Registration already on file; any new details were added.',
                    'student_id' => $existing->id,
                    'duplicate' => true,
                ], 200);
            }

            $student = Student::create($studentData);

            Log::info('Student created from webhook', ['student_id' => $student->id]);

            return response()->json([
                'success' => true,
                'message' => 'Student registered successfully',
                'student_id' => $student->id
            ], 200);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in webhook', [
                'error' => $e->getMessage(),
                'payload' => $request->input('fields'), // kept so a failed registration can be re-keyed by hand
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The registration could not be saved. Please contact the school.',
                'error_type' => 'database'
            ], 500);

        } catch (\Throwable $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'payload' => $request->input('fields'), // kept so a failed registration can be re-keyed by hand
            ]);

            return response()->json([
                'success' => false,
                'message' => 'The registration could not be processed. Please contact the school.',
                'error_type' => 'general'
            ], 500);
        }
    }

    /**
     * Validate the shared webhook secret sent by the registration form.
     *
     * The token may arrive as an X-Webhook-Token header or a "token"
     * query/body parameter (Elementor webhooks can only append it to the URL).
     */
    private function hasValidToken(Request $request): bool
    {
        $secret = config('services.webhook.secret');

        // Fail closed: refuse all requests until a secret is configured.
        if (empty($secret)) {
            Log::error('WEBHOOK_SECRET is not configured; rejecting webhook request.');
            return false;
        }

        $provided = $request->header('X-Webhook-Token', $request->input('token', ''));

        return is_string($provided) && hash_equals($secret, $provided);
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
            'student_dob' => $this->dateField($request, 'student_dob'),
            'student_birth_certificate' => $this->field($request, 'student_birth_certificate'),
            'student_birth_certificate_pin' => $this->fieldOrNull($request, 'student_birth_pin'),
            'student_religion' => $this->field($request, 'student_religion'),
            'student_country_of_birth' => $this->field($request, 'student_country_of_birth'),
            'student_nationality' => $this->conditionalField($request, 'student_nationality', 'Other', 'other_student_nationality'),
            'student_ethnicity' => $this->field($request, 'student_ethnicity'),
            'student_contact' => $this->field($request, 'student_contact_no'),
            'student_email' => $this->field($request, 'student_email'),

            // SEA Information
            'student_sea_date' => $this->dateField($request, 'student_sea_date'),
            'student_primary_school' => $this->field($request, 'student_primary_school'),
            'student_sea_slip' => $this->field($request, 'student_sea_slip'),
            'student_sea_number' => $this->field($request, 'student_sea_number'),

            // Transfer Information
            'student_transfer_status' => $this->field($request, 'transfer_status'),
            'student_transfer_slip' => $this->field($request, 'student_transfer_slip'),
            'student_transfer_reason' => $this->field($request, 'transferreason'),
            'student_transfer_date' => $this->dateField($request, 'student_transfer_year'),
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
            // Note: Form doesn't capture deceased status, defaulting to Alive
            'is_mother_active_or_deceased' => 'Alive',
            'mother_death_certificate' => null,
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
            // Note: Form doesn't capture deceased status, defaulting to Alive
            'is_father_active_or_deceased' => 'Alive',
            'father_death_certificate' => null,
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
            'registration_date' => $this->dateField($request, 'registrant_date', now()->format('Y-m-d')),
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
    private function field(Request $request, string $key, ?string $default = null): ?string
    {
        $value = $request->input("fields.{$key}.value");

        if (is_array($value)) {
            $value = implode(', ', array_filter(array_map('strval', $value), fn($x) => trim($x) !== ''));
        }

        return $this->hasValue($value) ? trim((string)$value) : $default;
    }

    /**
     * Get field value or NULL (for fields with unique constraints)
     */
    private function fieldOrNull(Request $request, string $key): ?string
    {
        $value = $request->input("fields.{$key}.value");

        if (is_array($value)) {
            $value = implode(', ', array_filter(array_map('strval', $value), fn($x) => trim($x) !== ''));
        }

        return $this->hasValue($value) ? trim((string)$value) : null;
    }

    /**
     * Get date field and convert from DD/MM/YYYY to YYYY-MM-DD format
     */
    private function dateField(Request $request, string $key, ?string $default = null): ?string
    {
        $value = $this->field($request, $key);

        if ($value === null) {
            return $default;
        }

        return self::parseDate($value) ?? $default;
    }

    /**
     * Parse a date the way the school writes it (day first), with ISO as a
     * fallback. Anything else returns null rather than throwing or being
     * silently read month-first.
     */
    public static function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        foreach (['d/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d', 'Y/m/d', 'j/n/Y', 'j-n-Y', 'd/m/y', 'F j, Y', 'j F Y', 'Y-m-d H:i:s'] as $format) {
            $date = \DateTime::createFromFormat('!' . $format, $value);
            if ($date && (int) $date->format('Y') >= 1950 && (int) $date->format('Y') <= 2100) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Get first non-blank value from multiple possible field keys
     */
    private function fieldAny(Request $request, array $keys, ?string $default = null): ?string
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
    private function multiField(Request $request, array $keys, ?string $default = null, string $separator = ' '): ?string
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
    private function conditionalField(Request $request, string $mainKey, string $matchValue, string $alternateKey): ?string
    {
        $mainValue = $this->field($request, $mainKey);
        return $mainValue === $matchValue ? $this->field($request, $alternateKey) : $mainValue;
    }

    /**
     * Return alternate field value when condition field equals specific value, otherwise N/A
     */
    private function conditionalValue(Request $request, string $conditionKey, string $matchValue, string $valueKey): ?string
    {
        $condition = $this->field($request, $conditionKey);

        if ($condition === $matchValue) {
            return $this->field($request, $valueKey) ?? $condition;
        }

        return $condition; // "No" is worth keeping; blank stays blank
    }

    /**
     * Handle device access field logic
     */
    private function deviceAccess(Request $request): ?string
    {
        $device = $this->field($request, 'student_device');
        if ($device === 'Other') {
            return $this->field($request, 'student_device_other');
        }
        return $this->fieldAny($request, ['student_device', 'student_continuos_access']);
    }


    /**
     * Legacy WordPress hosts whose file links may be stored (see Student::documentUrl).
     */
    private const FILE_FIELDS = [
        'student_passport_photo', 'student_birth_certificate', 'student_sea_slip', 'student_transfer_slip',
    ];

    /**
     * Make the extracted data safe to store: normalise the PIN and class,
     * drop invalid emails/dates/links, and cap lengths, so the record saves
     * and can be edited afterwards.
     */
    private function sanitise(array $data): array
    {
        if (!empty($data['student_birth_certificate_pin'])) {
            $pin = preg_replace('/[^0-9A-Z]/', '', strtoupper($data['student_birth_certificate_pin']));
            $data['student_birth_certificate_pin'] = preg_match('/[0-9]/', $pin) ? substr($pin, 0, 20) : null;
        }

        if (!empty($data['form_1_class'])) {
            $data['form_1_class'] = Student::canonicalClass($data['form_1_class']) ?? $data['form_1_class'];
        }

        foreach (['student_email', 'mother_email', 'father_email_address', 'registrant_email'] as $email) {
            if (!empty($data[$email]) && !filter_var($data[$email], FILTER_VALIDATE_EMAIL)) {
                $data[$email] = null;
            }
        }

        foreach (['student_dob', 'student_sea_date', 'student_transfer_date', 'registration_date'] as $date) {
            if (!empty($data[$date]) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data[$date])) {
                $data[$date] = self::parseDate($data[$date]);
            }
        }
        $data['registration_date'] = $data['registration_date'] ?? now()->format('Y-m-d');

        // Only links on the school's own site are kept for uploaded files
        foreach (self::FILE_FIELDS as $file) {
            if (!empty($data[$file]) && Student::documentUrl($data[$file]) === null && preg_match('#^https?://#i', $data[$file])) {
                $data[$file] = null;
            }
        }

        foreach ($data as $key => $value) {
            if (is_string($value) && mb_strlen($value) > 2000) {
                $data[$key] = mb_substr($value, 0, 2000);
            }
        }

        return $data;
    }

    /**
     * An existing record for the same child: same PIN, or same name and
     * date of birth. Soft-deleted records count too.
     */
    private function findExisting(array $data): ?Student
    {
        if (!empty($data['student_birth_certificate_pin'])) {
            $byPin = Student::withTrashed()->where('student_birth_certificate_pin', $data['student_birth_certificate_pin'])->first();
            if ($byPin) {
                return $byPin;
            }
        }

        if (!empty($data['student_name']) && !empty($data['student_dob'])) {
            $name = preg_replace('/\s+/', ' ', strtolower(trim($data['student_name'])));

            return Student::withTrashed()
                ->whereDate('student_dob', $data['student_dob'])
                ->get()
                ->first(fn ($s) => preg_replace('/\s+/', ' ', strtolower(trim((string) $s->student_name))) === $name);
        }

        return null;
    }

    /**
     * Copy values from a repeat submission into fields the existing record
     * has blank. Returns the names of the fields that were filled.
     *
     * @return array<int, string>
     */
    private function fillBlanks(Student $student, array $data): array
    {
        $filled = [];

        foreach ($data as $field => $value) {
            if (!Student::hasValue($value) || Student::hasValue($student->{$field})) {
                continue;
            }
            $student->{$field} = $value;
            $filled[] = $field;
        }

        if ($filled) {
            $student->save();
        }

        return $filled;
    }

    /**
     * Check if value is present and not blank
     */
    private function hasValue($value): bool
    {
        if (is_array($value)) {
            $value = implode('', array_map('strval', $value));
        }

        $trimmed = trim((string)$value);

        // Reject null, empty, or placeholder values
        if ($trimmed === '' || $value === null) {
            return false;
        }

        // Reject common Elementor select placeholders
        $placeholders = [
            'Select Gender', 'Select Religion', 'Select Country', 'Select Ethnicity',
            'Select Nationality', 'Select Community', 'Select Constituency', 'Select City',
            'Select Corporation', 'Select Identification Type', 'Select Blood Type',
            'Select Option', 'Select Transport Option', 'Select Relation',
            'Citizenship Type', 'Citizenship  Type'
        ];

        return !in_array($trimmed, $placeholders, true);
    }
}
