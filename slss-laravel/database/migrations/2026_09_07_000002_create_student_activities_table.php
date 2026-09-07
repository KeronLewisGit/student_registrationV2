<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail: who did what to a student record, and when.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_name', 120);            // snapshot, survives user deletion
            $table->string('action', 30)->index();       // created, updated, deleted, restored, promoted, photo, import, ...
            $table->string('summary', 255)->nullable();  // one-line human description
            $table->json('changes')->nullable();         // {field: {from, to}}
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_activities');
    }
};
