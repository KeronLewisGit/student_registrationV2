<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password {email?} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset a user\'s password. Usage: php artisan user:reset-password [email] [--password=new_password]';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 SLSS User Password Reset');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        // Get email from argument or ask
        $email = $this->argument('email');

        if (!$email) {
            // Show available users
            $users = User::all(['id', 'name', 'email', 'role']);

            if ($users->isEmpty()) {
                $this->error('❌ No users found in the database.');
                return 1;
            }

            $this->table(
                ['ID', 'Name', 'Email', 'Role'],
                $users->map(fn($u) => [$u->id, $u->name, $u->email, ucfirst($u->role)])->toArray()
            );
            $this->newLine();

            $email = $this->ask('Enter the email address of the user');
        }

        // Validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            $this->error('❌ Invalid email address format.');
            return 1;
        }

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User with email '{$email}' not found.");
            return 1;
        }

        // Display user info
        $this->info("Found user:");
        $this->line("  Name: {$user->name}");
        $this->line("  Email: {$user->email}");
        $this->line("  Role: " . ucfirst($user->role));
        $this->newLine();

        // Get password from option or ask
        $password = $this->option('password');

        if (!$password) {
            $password = $this->secret('Enter new password (min 8 characters)');
            $passwordConfirm = $this->secret('Confirm new password');

            if ($password !== $passwordConfirm) {
                $this->error('❌ Passwords do not match.');
                return 1;
            }
        }

        // Validate password strength
        if (strlen($password) < 8) {
            $this->error('❌ Password must be at least 8 characters long.');
            return 1;
        }

        // Confirm action
        if (!$this->option('password')) {
            if (!$this->confirm("Are you sure you want to reset the password for {$user->email}?", true)) {
                $this->warn('⚠️  Password reset cancelled.');
                return 0;
            }
        }

        // Update password
        $user->password = Hash::make($password);
        $user->save();

        $this->newLine();
        $this->info('✅ Password reset successfully!');
        $this->newLine();
        $this->line("User: {$user->email}");
        $this->line("You can now log in with the new password.");
        $this->newLine();

        return 0;
    }
}
