<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turn the student-only audit table into a general activity log that also
 * records sign-ins, exports, prints, document views, imports, user
 * management and system events.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('student_activities', 'activity_logs');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->change();
            $table->string('category', 20)->default('student')->index()->after('user_name');
            $table->string('subject_type', 40)->nullable()->after('action');
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            $table->string('subject_label', 160)->nullable()->after('subject_id');
            $table->string('user_agent', 255)->nullable()->after('ip');
            $table->index(['subject_type', 'subject_id']);
        });

        DB::table('activity_logs')->whereNotNull('student_id')->update([
            'category' => 'student',
            'subject_type' => 'student',
            'subject_id' => DB::raw('student_id'),
        ]);
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['subject_type', 'subject_id']);
            $table->dropColumn(['category', 'subject_type', 'subject_id', 'subject_label', 'user_agent']);
        });
        Schema::rename('activity_logs', 'student_activities');
    }
};
