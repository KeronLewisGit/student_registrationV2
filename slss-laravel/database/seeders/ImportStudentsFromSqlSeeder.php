<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Student;

class ImportStudentsFromSqlSeeder extends Seeder
{
    /**
     * Import student data from slss.sql dump file
     *
     * Run this seeder with: php artisan db:seed --class=ImportStudentsFromSqlSeeder
     */
    public function run(): void
    {
        $sqlFile = base_path('../slss.sql');

        if (!file_exists($sqlFile)) {
            $this->command->error("SQL file not found at: $sqlFile");
            $this->command->info("Please ensure slss.sql is located at: " . dirname(base_path()));
            return;
        }

        $this->command->info('Reading SQL dump file...');

        // Read and execute the SQL file
        DB::unprepared(file_get_contents($sqlFile));

        $this->command->info('SQL dump imported successfully!');

        // Migrate data from student_registration_data to students table
        $this->command->info('Migrating data to Laravel students table...');

        $oldStudents = DB::table('student_registration_data')->get();

        $this->command->info("Found {$oldStudents->count()} students to import");

        $bar = $this->command->getOutput()->createProgressBar($oldStudents->count());
        $bar->start();

        $imported = 0;
        $skipped = 0;

        foreach ($oldStudents as $oldStudent) {
            // Check if student already exists by PIN
            if ($oldStudent->student_birth_certficate_pin) {
                $exists = Student::where('student_birth_certificate_pin', $oldStudent->student_birth_certficate_pin)->exists();
                if ($exists) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }
            }

            try {
                Student::create([
                    // Basic Information
                    'form_1_class' => $oldStudent->form_1_class,
                    'student_name' => $oldStudent->student_name,
                    'student_gender' => $oldStudent->student_gender,
                    'citizen_type' => $oldStudent->citizen_type,
                    'student_current_address' => $oldStudent->student_current_address,
                    'student_dob' => $oldStudent->student_dob,
                    'student_birth_certificate' => $oldStudent->student_birth_certificate,
                    'student_birth_certificate_pin' => $oldStudent->student_birth_certficate_pin, // Note: fixing typo
                    'student_religion' => $oldStudent->student_religion,
                    'student_country_of_birth' => $oldStudent->student_country_of_birth,
                    'student_nationality' => $oldStudent->student_nationality,
                    'student_ethnicity' => $oldStudent->student_ethnicity,
                    'student_contact' => $oldStudent->student_contact,
                    'student_email' => $oldStudent->student_email,
                    'student_passport_photo' => $oldStudent->student_passport_photo,

                    // SEA Information
                    'student_sea_date' => $oldStudent->student_sea_date,
                    'student_primary_school' => $oldStudent->student_primary_school,
                    'student_sea_slip' => $oldStudent->student_sea_slip,
                    'student_sea_number' => $oldStudent->student_sea_number,

                    // Transfer Information
                    'student_transfer_status' => $oldStudent->student_transfer_status,
                    'student_transfer_slip' => $oldStudent->student_transfer_slip,
                    'student_transfer_date' => $oldStudent->student_transfer_date,
                    'student_previous_secondary_school' => $oldStudent->student_previous_secondary_school,
                    'student_previous_school_location' => $oldStudent->student_previous_school_location,

                    // Medical Information
                    'student_medical_condition' => $oldStudent->student_medical_condition,
                    'student_bloodtype' => $oldStudent->student_bloodtype,
                    'student_allergies' => $oldStudent->student_allergies,
                    'student_immunization_status' => $oldStudent->student_immunization_status,

                    // Special Needs & Intervention
                    'student_family_crisis' => $oldStudent->student_family_crisis,
                    'student_receiving_counselling' => $oldStudent->student_recieving_counselling, // Note: fixing typo
                    'student_physical_disabilities' => $oldStudent->student_physical_disibilities, // Note: fixing typo
                    'student_learning_disabilities' => $oldStudent->student_learning_disabilities,
                    'student_educational_aid' => $oldStudent->student_educational_aid,
                    'student_special_sea_concessions' => $oldStudent->student_special_sea_concessions,
                    'student_emotional_factors' => $oldStudent->student_emotional_factors,
                    'student_other_intervention_information' => $oldStudent->student_other_intervention_information,

                    // Personal Preferences
                    'student_school_feeding_option' => $oldStudent->student_school_feeding_option,
                    'student_social_welfare_status' => $oldStudent->student_social_welfare_status,
                    'student_mode_of_transport' => $oldStudent->student_mode_of_transport,
                    'student_access_to_device' => $oldStudent->student_access_to_device,

                    // Mother Information
                    'mother_name' => $oldStudent->mother_name,
                    'is_mother_active_or_deceased' => $oldStudent->is_mother_active_or_deceased,
                    'mother_identification_type' => $oldStudent->mother_identification_type,
                    'mother_identification_number' => $oldStudent->mother_identification_number,
                    'mother_home_address' => $oldStudent->mother_home_address,
                    'mother_contact' => $oldStudent->mother_contact,
                    'mother_profession' => $oldStudent->mother_profession,
                    'mother_work_address' => $oldStudent->mother_work_address,
                    'mother_email' => $oldStudent->mother_email,

                    // Father Information
                    'father_name' => $oldStudent->father_name,
                    'is_father_active_or_deceased' => $oldStudent->is_father_active_or_deceased,
                    'father_identification_type' => $oldStudent->father_identification_type,
                    'father_identification_number' => $oldStudent->father_identification_number,
                    'father_home_address' => $oldStudent->father_home_address,
                    'father_contact' => $oldStudent->father_contact,
                    'father_profession' => $oldStudent->father_profession,
                    'father_work_address' => $oldStudent->father_work_address,
                    'father_email_address' => $oldStudent->father_email_address,

                    // Emergency Contact
                    'emergency_contact_name' => $oldStudent->emergency_contact_name,
                    'emergency_contact_address' => $oldStudent->emergency_contact_address,
                    'emergency_contact_relation_to_student' => $oldStudent->emergency_contact_relation_to_student,
                    'emergency_contact_number' => $oldStudent->emergency_contact_number,

                    // Registrant Information
                    'registration_date' => $oldStudent->registration_date,
                    'registrant_relationship_to_student' => $oldStudent->registrant_relationship_to_student,
                    'registrant_name' => $oldStudent->registrant_name,
                    'registrant_identification_type' => $oldStudent->registrant_identification_type,
                    'registrant_identification_number' => $oldStudent->registrant_identification_number,
                    'registrant_nationality' => $oldStudent->registrant_nationality,
                    'registrant_email' => $oldStudent->registrant_email,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $this->command->error("\nError importing student ID {$oldStudent->id}: " . $e->getMessage());
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);

        $this->command->info("✅ Import completed!");
        $this->command->info("   - Imported: $imported students");
        $this->command->info("   - Skipped: $skipped students (duplicates or errors)");

        // Drop the old table
        $this->command->newLine();
        if ($this->command->confirm('Do you want to drop the old student_registration_data table?', false)) {
            DB::statement('DROP TABLE IF EXISTS student_registration_data');
            $this->command->info('✅ Old table dropped successfully');
        }
    }
}
