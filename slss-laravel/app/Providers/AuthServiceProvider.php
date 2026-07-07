<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        Gate::define('edit-students', function (User $user) {
            return in_array($user->role, ['admin', 'staff']);
        });

        Gate::define('delete-students', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('import-students', function (User $user) {
            return in_array($user->role, ['admin', 'staff']);
        });
    }
}
