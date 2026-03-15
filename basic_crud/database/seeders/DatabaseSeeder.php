<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            StudentSeeder::class,
        ]);

        User::query()->updateOrCreate(
            ['email' => 'superadmin@studentms.test'],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'role' => User::ROLE_SUPER_ADMIN,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@studentms.test'],
            [
                'name' => 'Admin Kampus',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
            ],
        );

        $studentUser = User::query()->updateOrCreate(
            ['email' => 'student@studentms.test'],
            [
                'name' => 'Student Demo',
                'password' => 'password',
                'role' => User::ROLE_STUDENT,
            ],
        );

        Student::query()->updateOrCreate(
            ['email' => 'student@studentms.test'],
            [
                'user_id' => $studentUser->id,
                'name' => 'Student Demo',
                'major' => 'Computer Science',
                'status' => 'active',
                'enrollment_year' => 2024,
            ],
        );
    }
}
