<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Badge>
 */
class BadgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $types = ['challenge', 'participation', 'moderation', 'achievement'];
        
        return [
            'name' => $this->faker->word . ' Badge',
            'icon_url' => 'badges/' . $this->faker->image('public/storage/badges', 100, 100, 'badge', false),
            'description' => $this->faker->sentence,
            'type' => $this->faker->randomElement($types),
        ];
    }
}
