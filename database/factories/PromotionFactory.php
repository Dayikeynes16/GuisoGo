<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 20, 200),
            'production_cost' => fake()->randomFloat(2, 5, 50),
            'is_active' => true,
            'active_days' => [1, 2, 3, 4, 5],
            'starts_at' => '09:00',
            'ends_at' => '21:00',
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function allWeek(): static
    {
        return $this->state(['active_days' => [0, 1, 2, 3, 4, 5, 6]]);
    }

    public function allDay(): static
    {
        return $this->state(['starts_at' => null, 'ends_at' => null]);
    }
}
