<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Add missing columns for Elementor webhook integration
            $table->text('student_transfer_reason')->nullable()->after('student_transfer_slip');
            $table->text('student_previous_form_class')->nullable()->after('student_transfer_date');
            $table->text('student_social_welfare_detail')->nullable()->after('student_social_welfare_status');
            $table->text('student_device_shared')->nullable()->after('student_access_to_device');
            $table->text('student_reliable_internet')->nullable()->after('student_device_shared');
            $table->text('student_internet_provider')->nullable()->after('student_reliable_internet');
            $table->text('student_online_tools')->nullable()->after('student_internet_provider');
            $table->text('mother_death_certificate')->nullable()->after('is_mother_active_or_deceased');
            $table->text('father_death_certificate')->nullable()->after('is_father_active_or_deceased');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'student_transfer_reason',
                'student_previous_form_class',
                'student_social_welfare_detail',
                'student_device_shared',
                'student_reliable_internet',
                'student_internet_provider',
                'student_online_tools',
                'mother_death_certificate',
                'father_death_certificate',
            ]);
        });
    }
};
