<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->jobTitle();

        return [
            'serial' => fake()->unique()->numberBetween(1, 99),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('##'),
            'color' => '#4C6FFF',
        ];
    }
}
