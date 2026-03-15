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
}
