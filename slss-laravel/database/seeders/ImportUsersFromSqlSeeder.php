<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ImportUsersFromSqlSeeder extends Seeder
{
    /**
     * Import user data from slss.sql dump file
     *
     * This seeder handles the old user authentication table structure
     * and migrates it to Laravel's users table with role field.
     *
     * Run this seeder with: php artisan db:seed --class=ImportUsersFromSqlSeeder
     */
    public function run(): void
    {
        $sqlFile = base_path('../slss.sql');

        if (!file_exists($sqlFile)) {
            $this->command->error("SQL file not found at: $sqlFile");
            $this->command->info("Please ensure slss.sql is located at: " . dirname(base_path()));
            return;
        }

        $this->command->info('Checking for old users data...');

        // Check if old users table exists
        $oldUsersExist = DB::select("SHOW TABLES LIKE 'users'");

        if (empty($oldUsersExist)) {
            $this->command->warn('Old users table not found. Importing SQL dump first...');
            DB::unprepared(file_get_contents($sqlFile));
        }

        // Get users from old structure
        $oldUsers = DB::select("
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN user_role ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
        ");

        if (empty($oldUsers)) {
            $this->command->warn('No users found in old table.');
            return;
        }

        $this->command->info("Found " . count($oldUsers) . " users to import");

        $bar = $this->command->getOutput()->createProgressBar(count($oldUsers));
        $bar->start();

        $imported = 0;
        $skipped = 0;

        foreach ($oldUsers as $oldUser) {
            // Check if user already exists
            $exists = User::where('email', $oldUser->email)->exists();
            if ($exists) {
                $skipped++;
                $bar->advance();
                continue;
            }

            try {
                // Map old role to new role
                $role = $this->mapRole($oldUser->role_name);

                // Create user with existing password hash
                User::create([
                    'name' => $oldUser->username ?? explode('@', $oldUser->email)[0],
                    'email' => $oldUser->email,
                    'password' => $oldUser->password, // Keep existing hash
                    'role' => $role,
                    'email_verified_at' => $oldUser->verified ? now() : null,
                    'created_at' => date('Y-m-d H:i:s', $oldUser->registered),
                    'updated_at' => $oldUser->last_login ? date('Y-m-d H:i:s', $oldUser->last_login) : now(),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $this->command->error("\nError importing user {$oldUser->email}: " . $e->getMessage());
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine(2);

        $this->command->info("✅ Import completed!");
        $this->command->info("   - Imported: $imported users");
        $this->command->info("   - Skipped: $skipped users (duplicates or errors)");

        // Warn about password compatibility
        $this->command->newLine();
        $this->command->warn('⚠️  NOTE: The old system may use a different password hashing algorithm.');
        $this->command->warn('   If users cannot login, they will need to reset their passwords.');
    }

    /**
     * Map old role names to new role enum
     */
    private function mapRole(?string $roleName): string
    {
        return match($roleName) {
            'admin' => 'admin',
            'staff', 'teacher', 'editor' => 'staff',
            default => 'viewer',
        };
    }
}
