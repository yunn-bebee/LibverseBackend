<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\ReadingChallenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserChallengeBookFactory extends Factory
{
    public function definition()
    {
        $statuses = ['planned', 'reading', 'completed'];
        $status = $this->faker->randomElement($statuses);
        
        $startedAt = null;
        $completedAt = null;
        $rating = null;
        $review = null;
        
        if ($status !== 'planned') {
            $startedAt = $this->faker->dateTimeBetween('-1 month');
        }
        
        if ($status === 'completed') {
            $completedAt = $this->faker->dateTimeBetween($startedAt, 'now');
            $rating = $this->faker->numberBetween(1, 5);
            $review = $this->faker->paragraph;
        }
        
        return [
            'user_id' => User::factory(),
            'challenge_id' => ReadingChallenge::factory(),
            'book_id' => Book::factory(),
            'status' => $status,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'user_rating' => $rating,
            'review' => $review,
        ];
    }
}