<?php

use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The progression backfill derived the stream letter from whatever letters
 * the legacy form_1_class held, which could turn placeholder values such as
 * "Select Class" or "N/A" into classes like "3S" or "3A". Blank those out so
 * they can be assigned properly; valid 1A-6F values are untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('students')
            ->whereNotNull('current_class')
            ->whereNotIn('current_class', Student::allClasses())
            ->update(['current_class' => null]);
    }

    public function down(): void
    {
        // Nothing to restore: the cleared values were invalid.
    }
};
