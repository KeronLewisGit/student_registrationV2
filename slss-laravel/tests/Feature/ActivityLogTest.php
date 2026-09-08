<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    public function test_sign_in_activity_is_recorded_with_ip(): void
    {
        $admin = $this->admin();

        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'wrong']);
        $this->post('/login', ['email' => 'nobody@test.local', 'password' => 'wrong']);
        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'Password-123456']);
        $this->post('/logout');

        $this->assertSame(['login-failed', 'login-failed', 'login', 'logout'],
            ActivityLog::where('category', 'auth')->orderBy('id')->pluck('action')->all());

        $failed = ActivityLog::where('action', 'login-failed')->orderBy('id')->get();
        $this->assertSame($admin->id, $failed[0]->user_id, 'A failed attempt on a real account is tied to that account');
        $this->assertNull($failed[1]->user_id);
        $this->assertStringContainsString('nobody@test.local', $failed[1]->summary);
        $this->assertNotNull($failed[0]->ip);
    }

    public function test_user_management_exports_prints_and_documents_are_recorded(): void
    {
        $admin = $this->admin();
        $student = $this->student();

        $this->actingAs($admin)->post('/users', ['name' => 'New Staff', 'email' => 'new@test.local', 'role' => 'staff',
            'password' => 'Password-123456', 'password_confirmation' => 'Password-123456'])->assertRedirect();
        $staff = User::where('email', 'new@test.local')->firstOrFail();
        $this->actingAs($admin)->put('/users/' . $staff->id, ['name' => 'New Staff', 'email' => 'new@test.local', 'role' => 'viewer'])->assertRedirect();
        $this->actingAs($admin)->delete('/users/' . $staff->id)->assertRedirect();

        $this->actingAs($admin)->get('/reports/all-students/export?format=csv')->assertOk();
        $this->actingAs($admin)->get('/students/' . $student->id . '/pdf')->assertOk();
        $this->actingAs($admin)->get('/students/' . $student->id . '/print')->assertOk();
        $this->actingAs($admin)->get('/printables/register?class=all')->assertOk();

        $actions = ActivityLog::whereIn('category', ['user', 'export', 'print'])->orderBy('id')->pluck('action')->all();
        $this->assertSame(['user-created', 'user-updated', 'user-deleted', 'spreadsheet', 'pdf', 'print', 'printable'], $actions);

        $update = ActivityLog::where('action', 'user-updated')->firstOrFail();
        $this->assertSame(['from' => 'staff', 'to' => 'viewer'], $update->changes['role']);
        $this->assertSame('New Staff', $update->subject_label);

        $pdf = ActivityLog::where('action', 'pdf')->firstOrFail();
        $this->assertSame($student->id, $pdf->student_id, 'Prints are linked to the student');
    }

    public function test_activity_page_presets_and_filters(): void
    {
        $admin = $this->admin();
        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'wrong']);
        $student = $this->student(['student_name' => 'Logged Kid']);
        $this->actingAs($admin);
        $student->update(['student_contact' => '868-555-0000']);

        $this->actingAs($admin)->get('/activity')->assertOk()->assertSee('Failed sign-in attempt')->assertSee('Logged Kid');
        $this->actingAs($admin)->get('/activity?preset=failed')->assertOk()->assertSee('Failed sign-in attempt')->assertDontSee('Updated Contact');
        $this->actingAs($admin)->get('/activity?preset=students')->assertOk()->assertSee('Updated Contact')->assertDontSee('Failed sign-in attempt');
        $this->actingAs($admin)->get('/activity?category=student&action=updated&q=Logged')->assertOk()->assertSee('Logged Kid');
        $this->actingAs($admin)->get('/activity?student=' . $student->id)->assertOk()->assertSee('Record created');
        $this->actingAs($admin)->get('/activity?ip=127.')->assertOk()->assertSee('Failed sign-in attempt');
        $this->actingAs($admin)->get('/activity?ip=10.99.')->assertOk()->assertSee('No activity matches');

        // The profile history panel only shows student-record entries
        $this->actingAs($admin)->get('/students/' . $student->id)->assertOk()->assertSee('Updated Contact')->assertDontSee('Failed sign-in');

        $this->flushSession();
        $this->actingAs($this->staff())->get('/activity')->assertForbidden();
    }
}
