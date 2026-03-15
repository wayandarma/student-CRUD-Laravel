<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'major' => fake()->randomElement([
                'Computer Science',
                'Information Systems',
                'Software Engineering',
                'Data Science',
            ]),
            'status' => fake()->randomElement(['active', 'inactive']),
            'enrollment_year' => fake()->numberBetween(2020, 2026),
        ];
    }
}
