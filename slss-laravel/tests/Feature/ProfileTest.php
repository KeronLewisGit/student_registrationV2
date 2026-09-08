<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_every_role_can_open_and_update_their_own_profile(): void
    {
        $viewer = $this->viewer();

        $this->actingAs($viewer)->get('/profile')->assertOk()->assertSee('My Profile')->assertSee('Recent sign-in activity');

        $this->actingAs($viewer)->put('/profile', [
            'first_name' => 'Vera', 'last_name' => 'Viewer-Jones', 'email' => 'viewer@test.local',
            'job_title' => 'Office Clerk', 'phone' => '868-555-0100',
        ])->assertRedirect('/profile')->assertSessionHas('success');

        $viewer->refresh();
        $this->assertSame('Vera Viewer-Jones', $viewer->name, 'Display name follows first + last');
        $this->assertSame('VV', $viewer->initials);
        $this->assertSame('Office Clerk', $viewer->job_title);
        $this->assertSame('viewer', $viewer->role, 'Role cannot be changed from the profile page');

        $log = ActivityLog::where('action', 'profile-updated')->firstOrFail();
        $this->assertSame(['from' => 'Viewer', 'to' => 'Vera'], $log->changes['first_name']);
    }

    public function test_changing_email_requires_the_current_password(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->put('/profile', ['first_name' => 'S', 'last_name' => 'T', 'email' => 'new@test.local'])
            ->assertSessionHasErrors('current_password');
        $this->assertSame('staff@test.local', $staff->fresh()->email);

        $this->actingAs($staff)->put('/profile', ['first_name' => 'S', 'last_name' => 'T', 'email' => 'new@test.local', 'current_password' => 'wrong'])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($staff)->put('/profile', ['first_name' => 'S', 'last_name' => 'T', 'email' => 'new@test.local', 'current_password' => 'Password-123456'])
            ->assertSessionHasNoErrors();
        $this->assertSame('new@test.local', $staff->fresh()->email);
    }

    public function test_password_change_needs_current_password_and_policy(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/profile/password', ['current_password' => 'wrong', 'password' => 'NewPassword-1234', 'password_confirmation' => 'NewPassword-1234'])
            ->assertSessionHasErrorsIn('password', ['current_password']);

        $this->actingAs($admin)->put('/profile/password', ['current_password' => 'Password-123456', 'password' => 'short', 'password_confirmation' => 'short'])
            ->assertSessionHasErrorsIn('password', ['password']);

        $this->actingAs($admin)->put('/profile/password', ['current_password' => 'Password-123456', 'password' => 'NewPassword-1234', 'password_confirmation' => 'NewPassword-1234'])
            ->assertRedirect('/profile')->assertSessionHas('success');

        $this->assertTrue(Hash::check('NewPassword-1234', $admin->fresh()->password));
        $this->assertSame(1, ActivityLog::where('action', 'password-changed')->where('user_id', $admin->id)->count());
    }

    public function test_admin_edits_of_name_keep_first_and_last_in_step(): void
    {
        $admin = $this->admin();
        $staff = $this->staff();

        $this->actingAs($admin)->put('/users/' . $staff->id, ['name' => 'Maria De La Cruz', 'email' => 'staff@test.local', 'role' => 'staff'])->assertRedirect();
        $staff->refresh();
        $this->assertSame('Maria', $staff->first_name);
        $this->assertSame('De La Cruz', $staff->last_name);
    }

    public function test_guest_cannot_reach_the_profile(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }
}
