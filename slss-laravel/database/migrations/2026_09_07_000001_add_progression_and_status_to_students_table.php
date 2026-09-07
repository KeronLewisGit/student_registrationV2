<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Academic-year progression and enrolment status.
 *
 * form_1_class stays as the historical intake class. current_class is the
 * class the student is in now and is what the year-end promotion advances.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedSmallInteger('intake_year')->nullable()->after('form_1_class');
            $table->string('current_class', 10)->nullable()->after('intake_year');
            $table->string('enrolment_status', 20)->default('active')->after('current_class');
            $table->date('status_changed_at')->nullable()->after('enrolment_status');
            $table->string('status_note')->nullable()->after('status_changed_at');

            $table->index('current_class');
            $table->index('enrolment_status');
        });

        // Backfill: intake year from the registration date, current class from
        // the intake class advanced by the number of academic years since.
        $academicYearStart = (int) now()->format('Y') - (now()->month < 9 ? 1 : 0);

        DB::table('students')
            ->select('id', 'form_1_class', 'registration_date', 'created_at')
            ->orderBy('id')
            ->chunk(200, function ($rows) use ($academicYearStart) {
                foreach ($rows as $row) {
                    $registered = $row->registration_date ?: $row->created_at;
                    $intakeYear = $registered ? (int) substr((string) $registered, 0, 4) : null;

                    // Registration in Jan–Aug precedes the September intake of the same year.
                    $currentClass = null;
                    $stream = preg_replace('/[^A-Z]/', '', strtoupper((string) $row->form_1_class));
                    $stream = $stream !== '' ? substr($stream, -1) : '';

                    if ($intakeYear && $stream !== '') {
                        $form = min(6, max(1, $academicYearStart - $intakeYear + 1));
                        $currentClass = $form . $stream;
                    }

                    DB::table('students')->where('id', $row->id)->update([
                        'intake_year' => $intakeYear,
                        'current_class' => $currentClass,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['current_class']);
            $table->dropIndex(['enrolment_status']);
            $table->dropColumn(['intake_year', 'current_class', 'enrolment_status', 'status_changed_at', 'status_note']);
        });
    }
};
