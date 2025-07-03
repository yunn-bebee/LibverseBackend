<?php

namespace Database\Factories;

// database/factories/UserBadgeFactory.php
use App\Models\Badge;
use App\Models\ReadingChallenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserBadgeFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'badge_id' => Badge::factory(),
            'earned_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'challenge_id' => ReadingChallenge::factory(),
        ];
    }
}