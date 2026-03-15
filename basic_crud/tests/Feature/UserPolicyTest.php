<?php

namespace Tests\Feature;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy();
    }

    // ── viewAny ─────────────────────────────────────────────

    public function test_student_cannot_view_any_users(): void
    {
        $student = User::factory()->student()->make();
        $this->assertFalse($this->policy->viewAny($student));
    }

    public function test_admin_cannot_view_any_users(): void
    {
        $admin = User::factory()->admin()->make();
        $this->assertFalse($this->policy->viewAny($admin));
    }

    public function test_super_admin_can_view_any_users(): void
    {
        $superAdmin = User::factory()->superAdmin()->make();
        $this->assertTrue($this->policy->viewAny($superAdmin));
    }

    // ── updateRole ──────────────────────────────────────────

    public function test_super_admin_can_change_student_to_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target     = User::factory()->student()->create();
        $this->assertTrue($this->policy->updateRole($superAdmin, $target));
    }

    public function test_super_admin_can_change_admin_to_student(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target     = User::factory()->admin()->create();
        $this->assertTrue($this->policy->updateRole($superAdmin, $target));
    }

    public function test_super_admin_cannot_change_another_super_admins_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $target     = User::factory()->superAdmin()->create();
        $this->assertFalse($this->policy->updateRole($superAdmin, $target));
    }

    public function test_super_admin_cannot_change_own_role(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $this->assertFalse($this->policy->updateRole($superAdmin, $superAdmin));
    }

    public function test_admin_cannot_update_any_role(): void
    {
        $admin  = User::factory()->admin()->create();
        $target = User::factory()->student()->create();
        $this->assertFalse($this->policy->updateRole($admin, $target));
    }

    public function test_student_cannot_update_any_role(): void
    {
        $student = User::factory()->student()->create();
        $target  = User::factory()->admin()->create();
        $this->assertFalse($this->policy->updateRole($student, $target));
    }
}
