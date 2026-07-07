<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ImportFromSqlDumpSeeder extends Seeder
{
    /**
     * Master seeder to import all data from slss.sql dump file
     *
     * This seeder imports:
     * - Student data from student_registration_data table
     * - User accounts from users table
     *
     * Usage:
     *   php artisan db:seed --class=ImportFromSqlDumpSeeder
     *
     * Prerequisites:
     *   - Place slss.sql file in /Users/keronlewis/Documents/SLSS-App/ directory
     *   - Ensure fresh Laravel database is set up (migrations run)
     */
    public function run(): void
    {
        $this->command->info('========================================');
        $this->command->info('  SLSS SQL Dump Import Seeder');
        $this->command->info('========================================');
        $this->command->newLine();

        // Import students
        $this->command->info('Step 1: Importing student data...');
        $this->call(ImportStudentsFromSqlSeeder::class);

        $this->command->newLine(2);

        // Import users (optional - depends on if you want old users)
        if ($this->command->confirm('Do you want to import users from the old system?', false)) {
            $this->command->info('Step 2: Importing user accounts...');
            $this->call(ImportUsersFromSqlSeeder::class);
        } else {
            $this->command->info('Step 2: Skipped user import. Using default seeded users.');
        }

        $this->command->newLine(2);
        $this->command->info('========================================');
        $this->command->info('  ✅ Import Process Complete!');
        $this->command->info('========================================');
        $this->command->newLine();
        $this->command->info('Next steps:');
        $this->command->info('  1. Verify data: php artisan tinker');
        $this->command->info('     >>> App\Models\Student::count()');
        $this->command->info('  2. Start server: php artisan serve');
        $this->command->info('  3. Login with default credentials (if not imported users)');
        $this->command->newLine();
    }
}
