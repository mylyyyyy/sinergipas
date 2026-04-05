<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nip' => fake()->unique()->numerify('##################'),
            'full_name' => fake()->name(),
            'position' => fake()->jobTitle(),
            'rank' => null,
            'photo' => null,
            'position_id' => null,
            'work_unit_id' => null,
        ];
    }
}
