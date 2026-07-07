<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canEdit();
    }

    public function rules(): array
    {
        return [
            // Student Basic Information (optimized based on actual data)
            'student_name' => 'required|string',
            'form_1_class' => 'nullable|string|in:A,B,C,D,E', // Only specific classes
            'student_gender' => 'nullable|string|in:Male,Female,Other',
            'citizen_type' => 'nullable|string|in:Birth,Naturalization,Other',
            'student_current_address' => 'nullable|string',
            'student_dob' => 'nullable|date|before:today|after:1990-01-01',
            'student_birth_certificate' => 'nullable|string', // URL or path
            'student_birth_certificate_pin' => 'nullable|string|max:20|unique:students,student_birth_certificate_pin', // VARCHAR(20) UNIQUE
            'student_religion' => 'nullable|string',
            'student_country_of_birth' => 'nullable|string',
            'student_nationality' => 'nullable|string',
            'student_ethnicity' => 'nullable|string',
            'student_contact' => 'nullable|string', // Phone numbers (868)xxx-xxxx format
            'student_email' => 'nullable|email',
            'student_passport_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,pdf|max:5120', // Allow PDF, 5MB max

            // SEA Information
            'student_sea_date' => 'nullable|date|after:2000-01-01',
            'student_primary_school' => 'nullable|string',
            'student_sea_slip' => 'nullable|string', // URL or path
            'student_sea_number' => 'nullable|string',

            // Transfer Information
            'student_transfer_status' => 'nullable|string|in:Yes,No',
            'student_transfer_slip' => 'nullable|string', // URL or path
            'student_transfer_date' => 'nullable|date',
            'student_previous_secondary_school' => 'nullable|string',
            'student_previous_school_location' => 'nullable|string',

            // Medical Information
            'student_medical_condition' => 'nullable|string',
            'student_bloodtype' => 'nullable|string', // "Blood Group A", "Blood Group O", etc.
            'student_allergies' => 'nullable|string',
            'student_immunization_status' => 'nullable|string|in:Yes,No,Unknown',

            // Special Needs & Intervention (can be empty string, not required)
            'student_family_crisis' => 'nullable|string',
            'student_receiving_counselling' => 'nullable|string',
            'student_physical_disabilities' => 'nullable|string',
            'student_learning_disabilities' => 'nullable|string',
            'student_educational_aid' => 'nullable|string',
            'student_special_sea_concessions' => 'nullable|string',
            'student_emotional_factors' => 'nullable|string',
            'student_other_intervention_information' => 'nullable|string',

            // Personal Preferences
            'student_school_feeding_option' => 'nullable|string|in:Both Breakfast and Lunch,Breakfast Only,Lunch Only,None',
            'student_social_welfare_status' => 'nullable|string|in:Yes,No',
            'student_mode_of_transport' => 'nullable|string',
            'student_access_to_device' => 'nullable|string|in:Yes,No',

            // Mother Information
            'is_mother_active_or_deceased' => 'nullable|string|in:Alive,Deceased',
            'mother_name' => 'nullable|string',
            'mother_identification_type' => 'nullable|string',
            'mother_identification_number' => 'nullable|string',
            'mother_home_address' => 'nullable|string',
            'mother_contact' => 'nullable|string',
            'mother_profession' => 'nullable|string',
            'mother_work_address' => 'nullable|string',
            'mother_email' => 'nullable|email',

            // Father Information
            'is_father_active_or_deceased' => 'nullable|string|in:Alive,Deceased',
            'father_name' => 'nullable|string',
            'father_identification_type' => 'nullable|string',
            'father_identification_number' => 'nullable|string',
            'father_home_address' => 'nullable|string',
            'father_contact' => 'nullable|string',
            'father_profession' => 'nullable|string',
            'father_work_address' => 'nullable|string',
            'father_email_address' => 'nullable|email',

            // Emergency Contact
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_address' => 'nullable|string',
            'emergency_contact_relation_to_student' => 'nullable|string',
            'emergency_contact_number' => 'nullable|string',

            // Registrant Information
            'registration_date' => 'nullable|date|before_or_equal:today',
            'registrant_relationship_to_student' => 'nullable|string',
            'registrant_name' => 'nullable|string',
            'registrant_identification_type' => 'nullable|string',
            'registrant_identification_number' => 'nullable|string',
            'registrant_nationality' => 'nullable|string',
            'registrant_email' => 'nullable|email',
        ];
    }

    public function messages(): array
    {
        return [
            'student_name.required' => 'Student name is required.',
            'student_dob.before' => 'Date of birth must be in the past.',
            'student_birth_certificate_pin.unique' => 'This birth certificate PIN is already registered.',
            'student_passport_photo.image' => 'Passport photo must be an image file.',
            'student_passport_photo.max' => 'Passport photo must not exceed 2MB.',
        ];
    }
}
