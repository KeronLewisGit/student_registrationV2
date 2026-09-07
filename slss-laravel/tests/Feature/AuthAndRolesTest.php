<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthAndRolesTest extends TestCase
{
    public function test_login_works_and_wrong_password_is_rejected(): void
    {
        $this->admin();

        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'nope'])
            ->assertSessionHasErrors('email');

        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'Password-123456'])
            ->assertRedirect('/students');
        $this->assertAuthenticated();
    }

    public function test_login_is_rate_limited_per_account_not_per_office(): void
    {
        $this->admin();
        $this->staff();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'admin@test.local', 'password' => 'nope'])->assertStatus(302);
        }
        $this->post('/login', ['email' => 'admin@test.local', 'password' => 'nope'])->assertStatus(429);

        // A different account from the same address is still allowed
        $this->post('/login', ['email' => 'staff@test.local', 'password' => 'Password-123456'])->assertRedirect('/students');
    }

    public function test_viewer_can_see_basics_but_nothing_sensitive(): void
    {
        $student = $this->student(['student_bloodtype' => 'Blood Group O', 'mother_identification_number' => '19800101001']);
        $viewer = $this->viewer();

        $this->actingAs($viewer)->get('/students')->assertOk();
        $page = $this->actingAs($viewer)->get('/students/' . $student->id)->assertOk();
        $page->assertDontSee('Blood Group O');
        $page->assertDontSee('19800101001');
        $page->assertDontSee('Download PDF');

        foreach (['/students/' . $student->id . '/pdf', '/students/' . $student->id . '/print', '/students/create',
                  '/students/' . $student->id . '/edit', '/students-trash', '/students-photos', '/students-promotion',
                  '/students-print', '/import', '/reports', '/printables', '/activity', '/users', '/deploy'] as $url) {
            $this->actingAs($viewer)->get($url)->assertForbidden();
        }

        $this->actingAs($viewer)->delete('/students/' . $student->id)->assertForbidden();
        $this->actingAs($viewer)->put('/students/' . $student->id, ['student_first_name' => 'X', 'student_last_name' => 'Y'])->assertForbidden();
    }

    public function test_staff_cannot_reach_admin_only_pages(): void
    {
        $staff = $this->staff();

        foreach (['/users', '/students-promotion', '/activity', '/students-trash', '/deploy', '/storage-diagnostics'] as $url) {
            $this->actingAs($staff)->get($url)->assertForbidden();
        }
        foreach (['/students/create', '/printables', '/students-photos', '/import', '/reports'] as $url) {
            $this->actingAs($staff)->get($url)->assertOk();
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/students')->assertRedirect('/login');
        $this->get('/documents/private/passports/x.png')->assertRedirect('/login');
    }
}
