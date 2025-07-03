<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ReadingChallenge;
use App\Models\Book;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChallengeBook>
 */
class ChallengeBookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
              return [
            'challenge_id' => ReadingChallenge::factory(),
            'book_id' => Book::factory(),
            'added_by' => User::factory(),
        ];

    }
}
