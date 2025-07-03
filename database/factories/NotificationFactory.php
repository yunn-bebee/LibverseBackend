<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    public function definition()
    {
        $types = ['like', 'comment', 'mention', 'event', 'challenge', 'follow', 'badge'];
        $sourceTypes = ['post', 'comment', 'event', 'user'];
        
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement($types),
            'source_type' => $this->faker->randomElement($sourceTypes),
            'source_id' => $this->faker->randomNumber(),
            'message' => $this->faker->sentence,
            'is_read' => $this->faker->boolean,
        ];
    }
}