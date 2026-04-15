<?php

namespace Database\Factories;

use App\Enums\StopType;
use App\Models\Stop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stop>
 */
class StopFactory extends Factory
{
    protected $model = Stop::class;

    public function definition(): array
    {
        $titles = [
            'Hoyt Lake Outlet',
            'Scajaquada Creek Bend',
            'Route 198 Underpass',
            'Delaware Park Wetlands',
            'Cazenovia Creek Confluence',
            'Forest Lawn Cemetery Edge',
            'Black Rock Canal Junction',
            'Niagara Street Bridge',
            'Buffalo State Shoreline',
            'Elmwood Avenue Crossing',
            'Bidwell Parkway Overlook',
            'McKinley Circle Marsh',
            'Humboldt Parkway Fragment',
            'Richmond Avenue Culvert',
            'Grant Street Bridge',
        ];

        return [
            'title' => $this->faker->randomElement($titles),
            'description' => $this->faker->optional(0.7)->paragraph(3),
            'latitude' => $this->faker->randomFloat(7, 42.89, 42.92),
            'longitude' => $this->faker->randomFloat(7, -78.87, -78.82),
            'trail_order' => $this->faker->numberBetween(0, 20),
            'type' => $this->faker->randomElement(StopType::cases()),
            'is_published' => $this->faker->boolean(60),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }
}
