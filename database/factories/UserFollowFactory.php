<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFollowFactory extends Factory
{
    public function definition()
    {
        return [
            'follower_id' => User::factory(),
            'followee_id' => User::factory(),
        ];
    }
}