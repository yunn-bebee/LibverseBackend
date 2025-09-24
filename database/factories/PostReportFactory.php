<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostReport>
 */class PostReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PostReport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'reason' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'dismissed']),
            'reviewed_at' => $this->faker->optional()->dateTime(),
            'reviewed_by' => $this->faker->optional()->randomElement(User::pluck('id')->toArray()),
        ];
    }
}
