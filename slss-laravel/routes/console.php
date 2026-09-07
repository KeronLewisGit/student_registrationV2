<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Housekeeping: purge audit entries past retention and permanently delete
// students left in Recently Deleted for over a year. Hostinger cron must call
// `php artisan schedule:run` every minute for this to fire.
Schedule::command('model:prune', ['--model' => [\App\Models\StudentActivity::class, \App\Models\Student::class]])->daily();
