<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        if (Student::query()->exists()) {
            return;
        }

        Student::factory()->count(50)->create();
    }
}
