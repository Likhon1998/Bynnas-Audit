<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'position_id' => Position::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
