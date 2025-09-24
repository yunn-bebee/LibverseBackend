<?php

namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
       public function definition()
    {
        $types = ['like', 'comment', 'mention', 'event', 'challenge', 'follow', 'badge'];
        $channels = ['database', 'email', 'sms', 'push'];

        return [
            'id' => (string) Str::uuid(), // since incrementing = false, keyType = string
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement($types),
            'data' => [
                'message' => $this->faker->sentence,
                'extra' => $this->faker->optional()->word,
            ],
            'channel' => $this->faker->randomElement($channels),
            'read_at' => $this->faker->optional()->dateTime,
        ];
    }
}
