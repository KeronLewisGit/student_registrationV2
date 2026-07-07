<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@slss.edu.tt',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create staff user
        User::create([
            'name' => 'Staff User',
            'email' => 'staff@slss.edu.tt',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
        ]);

        // Create viewer user
        User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@slss.edu.tt',
            'password' => Hash::make('viewer123'),
            'role' => 'viewer',
        ]);
    }
}
