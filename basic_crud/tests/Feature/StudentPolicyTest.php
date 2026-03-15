<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Policies\StudentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPolicyTest extends TestCase
{
    use RefreshDatabase;

    private StudentPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new StudentPolicy();
    }

    // ── viewAny ─────────────────────────────────────────────

    public function test_student_cannot_view_any(): void
    {
        $user = User::factory()->student()->make();
        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_admin_can_view_any(): void
    {
        $admin = User::factory()->admin()->make();
        $this->assertTrue($this->policy->viewAny($admin));
    }

    public function test_super_admin_can_view_any(): void
    {
        $superAdmin = User::factory()->superAdmin()->make();
        $this->assertTrue($this->policy->viewAny($superAdmin));
    }

    // ── view ────────────────────────────────────────────────

    public function test_student_can_view_own_record(): void
    {
        $user    = User::factory()->student()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($this->policy->view($user, $student));
    }

    public function test_student_cannot_view_other_students_record(): void
    {
        $user    = User::factory()->student()->create();
        $other   = Student::factory()->create();
        $this->assertFalse($this->policy->view($user, $other));
    }

    public function test_admin_can_view_any_student_record(): void
    {
        $admin   = User::factory()->admin()->make();
        $student = Student::factory()->create();
        $this->assertTrue($this->policy->view($admin, $student));
    }

    public function test_super_admin_can_view_any_student_record(): void
    {
        $superAdmin = User::factory()->superAdmin()->make();
        $student    = Student::factory()->create();
        $this->assertTrue($this->policy->view($superAdmin, $student));
    }

    // ── create ──────────────────────────────────────────────

    public function test_student_cannot_create(): void
    {
        $user = User::factory()->student()->make();
        $this->assertFalse($this->policy->create($user));
    }

    public function test_admin_can_create(): void
    {
        $admin = User::factory()->admin()->make();
        $this->assertTrue($this->policy->create($admin));
    }

    public function test_super_admin_can_create(): void
    {
        $superAdmin = User::factory()->superAdmin()->make();
        $this->assertTrue($this->policy->create($superAdmin));
    }

    // ── update ──────────────────────────────────────────────

    public function test_admin_can_update_any_student(): void
    {
        $admin   = User::factory()->admin()->make();
        $student = Student::factory()->create();
        $this->assertTrue($this->policy->update($admin, $student));
    }

    public function test_student_can_update_own_record(): void
    {
        $user    = User::factory()->student()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        $this->assertTrue($this->policy->update($user, $student));
    }

    public function test_student_cannot_update_another_students_record(): void
    {
        $user    = User::factory()->student()->create();
        $student = Student::factory()->create();
        $this->assertFalse($this->policy->update($user, $student));
    }

    // ── delete ──────────────────────────────────────────────

    public function test_student_cannot_delete(): void
    {
        $user    = User::factory()->student()->make();
        $student = Student::factory()->create();
        $this->assertFalse($this->policy->delete($user, $student));
    }

    public function test_admin_cannot_delete(): void
    {
        $admin   = User::factory()->admin()->make();
        $student = Student::factory()->create();
        $this->assertFalse($this->policy->delete($admin, $student));
    }

    public function test_super_admin_can_delete(): void
    {
        $superAdmin = User::factory()->superAdmin()->make();
        $student    = Student::factory()->create();
        $this->assertTrue($this->policy->delete($superAdmin, $student));
    }
}
