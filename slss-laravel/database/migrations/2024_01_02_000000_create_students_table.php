<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Student Basic Information (matching exact SQL schema)
            $table->text('student_passport_photo')->nullable(); // TEXT in SQL
            $table->text('form_1_class')->nullable(); // TEXT in SQL (values: A, B, C, D, E)
            $table->text('student_name')->nullable(); // TEXT in SQL
            $table->string('student_gender')->nullable(); // VARCHAR(255) in SQL
            $table->text('citizen_type')->nullable(); // TEXT in SQL (Birth/Naturalization)
            $table->text('student_current_address')->nullable();
            $table->date('student_dob')->nullable();
            $table->text('student_birth_certificate')->nullable(); // TEXT in SQL (URL/path)
            $table->string('student_birth_certificate_pin', 20)->nullable()->unique(); // VARCHAR(20) with UNIQUE constraint
            $table->text('student_religion')->nullable();
            $table->text('student_country_of_birth')->nullable();
            $table->text('student_nationality')->nullable();
            $table->text('student_ethnicity')->nullable();
            $table->text('student_contact')->nullable();
            $table->text('student_email')->nullable();

            // SEA Information
            $table->date('student_sea_date')->nullable();
            $table->text('student_primary_school')->nullable();
            $table->text('student_sea_slip')->nullable(); // URL/path
            $table->text('student_sea_number')->nullable();

            // Transfer Information
            $table->text('student_transfer_status')->nullable(); // Yes/No
            $table->text('student_transfer_slip')->nullable(); // URL/path
            $table->date('student_transfer_date')->nullable();
            $table->text('student_previous_secondary_school')->nullable();
            $table->text('student_previous_school_location')->nullable();

            // Medical Information
            $table->text('student_medical_condition')->nullable();
            $table->text('student_bloodtype')->nullable(); // "Blood Group A", "Blood Group O", etc.
            $table->text('student_allergies')->nullable();
            $table->text('student_immunization_status')->nullable(); // Yes/No

            // Special Needs & Intervention (NOT NULL in SQL, but can be empty string)
            $table->text('student_family_crisis')->default(''); // NOT NULL in SQL
            $table->text('student_receiving_counselling')->default(''); // NOT NULL in SQL (typo: recieving)
            $table->text('student_physical_disabilities')->default(''); // NOT NULL in SQL (typo: disibilities)
            $table->text('student_learning_disabilities')->default(''); // NOT NULL in SQL
            $table->text('student_educational_aid')->default(''); // NOT NULL in SQL
            $table->text('student_special_sea_concessions')->default(''); // NOT NULL in SQL
            $table->text('student_emotional_factors')->default(''); // NOT NULL in SQL
            $table->text('student_other_intervention_information')->default(''); // NOT NULL in SQL

            // Personal Preferences
            $table->text('student_school_feeding_option')->nullable(); // "Both Breakfast and Lunch", "Breakfast Only", "None"
            $table->text('student_social_welfare_status')->nullable(); // Yes/No
            $table->text('student_mode_of_transport')->nullable(); // Maxi-Taxi, Family Private Car, Walk, etc.
            $table->text('student_access_to_device')->nullable(); // Yes/No

            // Mother Information
            $table->text('is_mother_active_or_deceased')->nullable(); // Alive/Deceased
            $table->text('mother_name')->nullable();
            $table->text('mother_identification_type')->nullable(); // National Identification Card, Driver's Permit, etc.
            $table->text('mother_identification_number')->nullable();
            $table->text('mother_home_address')->nullable();
            $table->text('mother_contact')->nullable();
            $table->text('mother_profession')->nullable();
            $table->text('mother_work_address')->nullable();
            $table->text('mother_email')->nullable();

            // Father Information
            $table->text('is_father_active_or_deceased')->nullable(); // Alive/Deceased
            $table->text('father_name')->nullable();
            $table->text('father_identification_type')->nullable();
            $table->text('father_identification_number')->nullable();
            $table->text('father_home_address')->nullable();
            $table->text('father_contact')->nullable();
            $table->text('father_profession')->nullable();
            $table->text('father_work_address')->nullable();
            $table->text('father_email_address')->nullable();

            // Emergency Contact
            $table->text('emergency_contact_name')->nullable();
            $table->text('emergency_contact_address')->nullable();
            $table->text('emergency_contact_relation_to_student')->nullable(); // Uncle, Aunt, Grandmother, etc.
            $table->text('emergency_contact_number')->nullable();

            // Registrant Information
            $table->date('registration_date')->nullable();
            $table->text('registrant_relationship_to_student')->nullable(); // Mother, Father, Sister, etc.
            $table->text('registrant_name')->nullable();
            $table->text('registrant_identification_type')->nullable();
            $table->text('registrant_identification_number')->nullable();
            $table->text('registrant_nationality')->nullable();
            $table->text('registrant_email')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance (based on common queries)
            $table->index('form_1_class');
            $table->index('student_name');
            $table->index('registration_date');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
