<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'bio' => $this->faker->paragraph,
            'profile_picture' => 'avatars/' . $this->faker->image('public/storage/avatars', 200, 200, 'people', false),
            'website' => $this->faker->url,
            'location' => $this->faker->city,
            'last_active' => $this->faker->dateTimeThisMonth,
        ];
    }
}
