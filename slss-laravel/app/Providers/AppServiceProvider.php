<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Observers\StudentObserver;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        // Audit trail for every create / update / delete / restore of a student
        Student::observe(StudentObserver::class);

        // Sign-in activity
        Event::listen(Login::class, function (Login $event) {
            ActivityLog::log('auth', 'login', 'Signed in', ['user' => $event->user, 'subject' => $event->user]);
        });
        Event::listen(Failed::class, function (Failed $event) {
            $email = (string) ($event->credentials['email'] ?? '');
            ActivityLog::log('auth', 'login-failed', 'Failed sign-in attempt for ' . ($email !== '' ? $email : 'an unknown account'), [
                'user' => $event->user,
                'user_name' => $event->user?->name ?? ($email !== '' ? $email : 'Unknown'),
                'subject' => $event->user,
            ]);
        });
        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                ActivityLog::log('auth', 'logout', 'Signed out', ['user' => $event->user, 'subject' => $event->user]);
            }
        });
    }
}
