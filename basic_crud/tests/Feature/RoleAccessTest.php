<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_student_management_index(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('students.index'));

        $response->assertOk();
    }

    public function test_admin_dashboard_redirects_to_student_management_index(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertRedirect(route('students.index'));
    }

    public function test_student_is_redirected_away_from_student_management_routes(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('students.index'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_student_can_access_the_student_dashboard(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Portal Mahasiswa');
    }

    public function test_admin_cannot_delete_a_student(): void
    {
        $admin   = User::factory()->admin()->create();
        $student = \App\Models\Student::factory()->create();

        $response = $this->actingAs($admin)->delete(route('students.destroy', $student));

        $response->assertForbidden();
    }

    // -- Profile routes ---------------------------------------------------

    public function test_student_can_access_profile_show_redirects_to_edit_when_no_profile(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('profile.show'));

        // No profile linked → StudentProfileController@show redirects to profile.edit
        $response->assertRedirect(route('profile.edit'));
    }

    public function test_student_can_access_profile_edit(): void
    {
        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)->get(route('profile.edit'));

        $response->assertOk();
    }

    public function test_admin_cannot_access_profile_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('profile.show'));

        // role:student middleware → redirects to dashboard
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    // -- User management --------------------------------------------------

    public function test_super_admin_can_access_user_management(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)->get(route('users.index'));

        $response->assertOk();
    }

    public function test_admin_cannot_access_user_management(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('users.index'));

        // role:super_admin middleware → redirects to dashboard
        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_super_admin_can_change_student_role_to_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target     = User::factory()->student()->create();

        $response = $this->actingAs($superAdmin)
            ->put(route('users.update-role', $target), ['role' => 'admin']);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'admin']);
    }

    public function test_super_admin_cannot_change_another_super_admins_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target     = User::factory()->superAdmin()->create();

        $response = $this->actingAs($superAdmin)
            ->put(route('users.update-role', $target), ['role' => 'admin']);

        $response->assertForbidden();
    }

    // -- students.show ----------------------------------------------------

    public function test_admin_can_access_students_show(): void
    {
        $admin   = User::factory()->admin()->create();
        $student = \App\Models\Student::factory()->create();

        $response = $this->actingAs($admin)->get(route('students.show', $student));

        $response->assertOk();
    }
}
