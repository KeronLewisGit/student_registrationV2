<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentActivity;
use Carbon\Carbon;
use Tests\TestCase;

class ProgressionTest extends TestCase
{
    public function test_academic_year_maths(): void
    {
        Carbon::setTestNow('2026-07-15');
        $this->assertSame(2025, Student::currentAcademicYear());
        $this->assertSame(2026, Student::promotionTargetYear());

        Carbon::setTestNow('2026-10-01');
        $this->assertSame(2026, Student::currentAcademicYear());
        $this->assertSame(2026, Student::promotionTargetYear());
        Carbon::setTestNow();
    }

    public function test_new_records_get_intake_year_and_current_class(): void
    {
        Carbon::setTestNow('2026-09-07');
        $s = $this->student(['form_1_class' => 'Form 1e', 'registration_date' => '2024-07-01']);
        $this->assertSame(2024, $s->intake_year);
        $this->assertSame('3E', $s->current_class);
        $this->assertSame('4E', $s->nextClass());
        Carbon::setTestNow();
    }

    public function test_promotion_skips_new_intake_graduates_form_six_and_refuses_rerun(): void
    {
        // July 2026: the 2025/2026 year is ending; the promotion targets 2026/2027
        Carbon::setTestNow('2026-07-20');
        $oldTimer = $this->student(['student_name' => 'Old Timer', 'registration_date' => '2024-07-01']);   // 2C this year
        $leaver   = $this->student(['student_name' => 'Top Form', 'registration_date' => '2020-07-01']);    // 6C this year
        $newbie   = $this->student(['student_name' => 'New Kid', 'registration_date' => '2026-06-30']);     // intake 2026 -> 1C
        $left     = $this->student(['student_name' => 'Gone', 'registration_date' => '2024-07-01', 'enrolment_status' => 'left']);
        $this->assertSame('2C', $oldTimer->current_class);
        $this->assertSame('6C', $leaver->current_class);
        $this->assertSame('1C', $newbie->current_class);

        $admin = $this->admin();
        $this->actingAs($admin)->get('/students-promotion')->assertOk()->assertSee('2026/2027');
        $this->actingAs($admin)->post('/students-promotion', ['confirm' => '1'])->assertRedirect('/students-promotion');

        $this->assertSame('3C', $oldTimer->fresh()->current_class);
        $this->assertSame('graduated', $leaver->fresh()->enrolment_status);
        $this->assertSame('1C', $newbie->fresh()->current_class, 'Incoming intake must not be promoted');
        $this->assertSame('2C', $left->fresh()->current_class, 'Students who left are untouched');
        $this->assertSame(1, StudentActivity::where('student_id', $oldTimer->id)->where('action', 'promoted')->count(), 'Exactly one audit row per student');

        $this->actingAs($admin)->post('/students-promotion', ['confirm' => '1'])->assertSessionHas('error');
        $this->assertSame('3C', $oldTimer->fresh()->current_class, 'Second run must change nothing');
        Carbon::setTestNow();
    }

    public function test_promotion_is_refused_when_classes_already_match_the_target_year(): void
    {
        // October: the 2026/2027 year is running and the backfill already placed everyone in it
        Carbon::setTestNow('2026-10-01');
        $s = $this->student(['registration_date' => '2024-07-01']); // intake 2024 -> 3C for 2026/2027
        $this->assertSame('3C', $s->current_class);

        $admin = $this->admin();
        $this->actingAs($admin)->get('/students-promotion')->assertOk()->assertSee('already match 2026/2027');
        $this->actingAs($admin)->post('/students-promotion', ['confirm' => '1'])->assertSessionHas('error');
        $this->assertSame('3C', $s->fresh()->current_class);
        Carbon::setTestNow();
    }

    public function test_promotion_requires_confirmation(): void
    {
        $s = $this->student(['registration_date' => '2024-07-01']);
        $this->actingAs($this->admin())->post('/students-promotion', [])->assertSessionHasErrors('confirm');
        $this->assertSame($s->current_class, $s->fresh()->current_class);
    }
}
