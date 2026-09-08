<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Self-service profile fields. `name` stays as the display name and is kept
 * in step with first_name + last_name by the User model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 80)->nullable()->after('name');
            $table->string('last_name', 80)->nullable()->after('first_name');
            $table->string('job_title', 100)->nullable()->after('last_name');
            $table->string('phone', 40)->nullable()->after('job_title');
        });

        // Backfill first/last from the existing display name
        foreach (DB::table('users')->select('id', 'name')->get() as $user) {
            $words = preg_split('/\s+/', trim((string) $user->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $words ? array_shift($words) : null,
                'last_name' => $words ? implode(' ', $words) : null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'job_title', 'phone']);
        });
    }
};
