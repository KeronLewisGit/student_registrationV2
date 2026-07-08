<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Student;

class ImportOldStudentData extends Command
{
    protected $signature = 'students:import-old-data';
    protected $description = 'Import student data from old student_registration_data table to new students table';

    public function handle()
    {
        $this->info('Starting data migration from old system...');

        // Check if old table exists
        if (!DB::getSchemaBuilder()->hasTable('student_registration_data')) {
            $this->error('Old table student_registration_data not found!');
            $this->info('Please import the SQL file first using:');
            $this->line('mysql -u username -p database_name < backup.sql');
            return 1;
        }

        // Get count of old records
        $oldCount = DB::table('student_registration_data')->count();
        $this->info("Found {$oldCount} students in old table.");

        if ($oldCount === 0) {
            $this->warn('No students to import.');
            return 0;
        }

        // Confirm before proceeding
        if (!$this->confirm('Do you want to proceed with the import?', true)) {
            $this->warn('Import cancelled.');
            return 0;
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        // Fetch all old records
        $oldStudents = DB::table('student_registration_data')->get();

        $this->info('Importing students...');
        $bar = $this->output->createProgressBar($oldCount);
        $bar->start();

        foreach ($oldStudents as $oldStudent) {
            try {
                // Check if student already exists (by name and DOB)
                $exists = Student::where('student_name', $oldStudent->student_name)
                    ->where('student_dob', $oldStudent->student_dob)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Map old column names to new ones
                Student::create([
                    'form_1_class' => $oldStudent->form_1_class,
                    'student_name' => $oldStudent->student_name,
                    'student_gender' => $oldStudent->student_gender,
                    'citizen_type' => $oldStudent->citizen_type,
                    'student_current_address' => $oldStudent->student_current_address,
                    'student_dob' => $oldStudent->student_dob,
                    'student_birth_certificate' => $oldStudent->student_birth_certificate,
                    // Fix typo in old column name
                    'student_birth_certificate_pin' => $oldStudent->student_birth_certficate_pin ?? null,
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

                    // Special Needs - Fix typos in old column names
                    'student_family_crisis' => $oldStudent->student_family_crisis,
                    'student_receiving_counselling' => $oldStudent->student_recieving_counselling ?? null,
                    'student_physical_disabilities' => $oldStudent->student_physical_disibilities ?? null,
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

                    // Registration Information
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
                $errors++;
                $this->newLine();
                $this->error("Error importing student {$oldStudent->student_name}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('Import Summary:');
        $this->info("  ✓ Imported: {$imported} students");
        $this->warn("  ⊘ Skipped (duplicates): {$skipped}");
        if ($errors > 0) {
            $this->error("  ✗ Errors: {$errors}");
        }
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if ($this->confirm('Do you want to drop the old student_registration_data table?', false)) {
            DB::statement('DROP TABLE student_registration_data');
            $this->info('Old table dropped successfully.');
        }

        return 0;
    }
}
