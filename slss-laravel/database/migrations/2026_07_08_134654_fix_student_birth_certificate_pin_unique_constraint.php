<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing 'N/A' values to NULL to avoid unique constraint violations
        DB::table('students')
            ->where('student_birth_certificate_pin', 'N/A')
            ->update(['student_birth_certificate_pin' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert NULL values back to 'N/A'
        DB::table('students')
            ->whereNull('student_birth_certificate_pin')
            ->update(['student_birth_certificate_pin' => 'N/A']);
    }
};
