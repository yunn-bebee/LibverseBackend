<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MentionFactory extends Factory
{
    public function definition()
    {
        $sourceTypes = ['post', 'comment', 'event'];
        
        return [
            'user_id' => User::factory(),
            'source_id' => $this->faker->randomNumber(),
            'source_type' => $this->faker->randomElement($sourceTypes),
        ];
    }
}