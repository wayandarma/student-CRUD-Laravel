<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_student_user(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Student Baru',
            'email' => 'student-baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'student-baru@example.com',
            'role' => User::ROLE_STUDENT,
        ]);
    }
}
