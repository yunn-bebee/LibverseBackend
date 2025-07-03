<?php
namespace Database\Factories;

// database/factories/ReadingChallengeFactory.php

use App\Models\Badge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingChallengeFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true) . ' Challenge',
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph,
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
            'target_count' => $this->faker->numberBetween(5, 20),
            'badge_id' => Badge::factory(),
            'created_by' => User::factory(),
            'is_active' => true,
        ];
    }

    public function inactive()
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}