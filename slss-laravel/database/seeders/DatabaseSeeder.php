<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Check if users already exist
        if (User::count() > 0) {
            $this->command->warn('⚠️  Users already exist. Skipping seeder to prevent duplicates.');
            return;
        }

        // Get passwords from environment or use defaults
        $adminPassword = env('DEFAULT_ADMIN_PASSWORD', 'admin123');
        $staffPassword = env('DEFAULT_STAFF_PASSWORD', 'staff123');
        $viewerPassword = env('DEFAULT_VIEWER_PASSWORD', 'viewer123');

        // Security warning for default passwords
        if ($adminPassword === 'admin123' || $staffPassword === 'staff123' || $viewerPassword === 'viewer123') {
            $this->command->warn('');
            $this->command->warn('┌─────────────────────────────────────────────────────────────┐');
            $this->command->warn('│  ⚠️  SECURITY WARNING: Using default passwords!            │');
            $this->command->warn('│                                                             │');
            $this->command->warn('│  CHANGE THESE PASSWORDS IMMEDIATELY after deployment!       │');
            $this->command->warn('│                                                             │');
            $this->command->warn('│  You can set custom passwords in .env:                      │');
            $this->command->warn('│  - DEFAULT_ADMIN_PASSWORD=your_secure_password              │');
            $this->command->warn('│  - DEFAULT_STAFF_PASSWORD=your_secure_password              │');
            $this->command->warn('│  - DEFAULT_VIEWER_PASSWORD=your_secure_password             │');
            $this->command->warn('└─────────────────────────────────────────────────────────────┘');
            $this->command->warn('');
        }

        // Create default admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@slss.edu.tt',
            'password' => Hash::make($adminPassword),
            'role' => 'admin',
        ]);
        $this->command->info('✓ Created admin user: admin@slss.edu.tt');

        // Create staff user
        User::create([
            'name' => 'Staff User',
            'email' => 'staff@slss.edu.tt',
            'password' => Hash::make($staffPassword),
            'role' => 'staff',
        ]);
        $this->command->info('✓ Created staff user: staff@slss.edu.tt');

        // Create viewer user
        User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@slss.edu.tt',
            'password' => Hash::make($viewerPassword),
            'role' => 'viewer',
        ]);
        $this->command->info('✓ Created viewer user: viewer@slss.edu.tt');

        $this->command->info('');
        $this->command->info('✅ Database seeding completed successfully!');
    }
}
